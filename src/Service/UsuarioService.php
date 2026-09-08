<?php

namespace App\Service;

use App\Service\BaseService;
use App\Entity\Usuario;
use App\Entity\Roles;
use App\Entity\Modulo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\Serializer\SerializationContext;
use DateTime;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UsuarioService extends BaseService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function createUsuario(string $title, string $content): Usuario
    {
        $usuario = new Usuario($title, $content);
        $this->em->persist($usuario);
        $this->em->flush();

        return $usuario;
    }

    public function getUsuarioById(int $id): ?Usuario
    {
        return $this->em->getRepository(Usuario::class)->find($id);
    }

    public function getUsuarios(): array
    {
        return $this->em->getRepository(Usuario::class)->findAll();
    }

    public function getRoles($serializer)
    {
        $roles = $this->em->getRepository(Roles::class)->findAll();
       // Crear el contexto de serialización
       $context = SerializationContext::create()->setGroups(['user']);
       return $serializer->toArray($roles, $context);
    }

    public function getById($id, $serializer)
    {
        $usuario = $this->em->getRepository(Usuario::class)->find($id);
       // Crear el contexto de serialización
       $context = SerializationContext::create()->setGroups(['userdata']);
       return $serializer->toArray($usuario, $context);
    }

    public function save($datos)
    {
        $usuario = new Usuario();

        if (!isset($datos['modulos']) || !is_array($datos['modulos']) || count($datos['modulos']) === 0) {
            throw new HttpException(
                400,
                "Debe seleccionar al menos un módulo para el usuario."
            );
        }

        $usuario->setFechaCreacion(new DateTime());
        $usuario->setNombre($datos['nombre']);
        $usuario->setApellido($datos['apellido']);
        $usuario->setEmail($datos['email']);
        $usuario->setCelular($datos['celular']);
        $usuario->setActivo((isset($datos['activo'])) ? 1 : 0);

        // Validar que el correo electrónico no exista
        $usuarioExistente = $this->em
            ->getRepository(Usuario::class)
            ->getUsarioByCorreoElectronico(null, $datos['email']);

        if (COUNT($usuarioExistente) > 0) {
            throw new HttpException(
                409,
                "El Correo Electrónico ya existe en el sistema."
            );
        }

        // Password
        $hashedPassword = $this->passwordHasher
            ->hashPassword($usuario, $datos["password"]);

        $usuario->setPassword($hashedPassword);

        // Rol
        if (isset($datos['rol'])) {

            $rol = $this->em
                ->getRepository(Roles::class)
                ->find((int) $datos['rol']);

            if ($rol) {
                $usuario->addRol($rol);
            }
        }

        // Módulos
        $modulos = [];

        foreach ($datos['modulos'] as $moduloId) {

            $modulo = $this->em
                ->getRepository(Modulo::class)
                ->find((int) $moduloId);

            if (!$modulo || !$modulo->isActivo()) {
                throw new HttpException(
                    400,
                    "Uno de los módulos seleccionados no es válido."
                );
            }

            $modulos[] = $modulo;
        }

        if (count($modulos) === 0) {
            throw new HttpException(
                400,
                "Debe seleccionar al menos un módulo para el usuario."
            );
        }

        $usuario->getModulos()->clear();

        foreach ($modulos as $modulo) {
            $usuario->addModulo($modulo);
        }

        $this->em->persist($usuario);
        $this->em->flush();

        return $usuario;
    }

    public function update($id, $datos)
    {
        // Buscar al usuario por su ID
        $usuario = $this->em->getRepository(Usuario::class)->find($id);

        // Verificar si el usuario existe
        if (!$usuario) {
            throw new HttpException(409, "Usuario no encontrado");
        }

        // Verificar que tenga al menos un módulo
        if (
            !isset($datos['modulos']) ||
            !is_array($datos['modulos']) ||
            count($datos['modulos']) === 0
        ) {
            throw new HttpException(
                400,
                "Debe seleccionar al menos un módulo para el usuario."
            );
        }

        // Validar que el correo electrónico no exista para otro usuario
        $usuarioExistente = $this->em->getRepository(Usuario::class)->getUsarioByCorreoElectronico($id, $datos['email']);
        if(COUNT($usuarioExistente)>0) {
            throw new HttpException(409, "El Correo Electrónico ya existe en el sistema.");
        }

        $usuario->setNombre($datos['nombre']);
        $usuario->setApellido($datos['apellido']);
        $usuario->setEmail($datos['email']);
        $usuario->setCelular($datos['celular']);
        $usuario->setFechaActualizacion(new DateTime());
        $usuario->setActivo((isset($datos['activo'])) ? 1: 0);
        // Password
        if (trim($datos["password"]) != "" && $datos["password"] != null) {
            $hashedPassword = $this->passwordHasher->hashPassword($usuario, $datos["password"]);
            $usuario->setPassword($hashedPassword);
        }
        // Se editan los roles si viene de la edicion de usuario
        if (isset($datos['rol'])) {
            $rol = $this->em->getRepository(Roles::class)->find((int)$datos['rol']);
            /** ***roles anteriores**** */
            $roles = $usuario->getRoles();
            foreach ($roles as $r) {
                $aux = $this->em->getRepository(Roles::class)->findOneBy(['nombre' => $r]);
                $usuario->removeRol($aux);
            }
            /* * *********************** */
            $usuario->addRol($rol);
        }

        // Módulos
        $modulos = [];

        foreach ($datos['modulos'] as $moduloId) {

            $modulo = $this->em
                ->getRepository(Modulo::class)
                ->find((int) $moduloId);

            if (!$modulo || !$modulo->isActivo()) {
                throw new HttpException(
                    400,
                    "Uno de los módulos seleccionados no es válido."
                );
            }

            $modulos[] = $modulo;
        }

        if (count($modulos) === 0) {
            throw new HttpException(
                400,
                "Debe seleccionar al menos un módulo para el usuario."
            );
        }

        $usuario->getModulos()->clear();

        foreach ($modulos as $modulo) {
            $usuario->addModulo($modulo);
        }

        $this->em->flush();
        return $usuario;
    }

    public function delete($id) 
    {
        // Buscar al usuario por su ID
        $usuario = $this->em->getRepository(Usuario::class)->find($id);

        // Verificar si el usuario existe
        if (!$usuario) {
            throw new HttpException(409, "Usuario no encontrado");
        }

        $this->em->remove($usuario);
        $this->em->flush();
        return 'Usuario Eliminado con éxito';
    }

    private function enviarMailRecuperarClave($mailer, $twig, $usuario, $request)
    {
        // Renderizar el contenido del correo desde la plantilla Twig
        $htmlContent = $twig->render('email/restablecer_password.html.twig', [
            'name' => 'Juan',
        ]);
        // Crear el objeto de correo
        $email = (new Email())
            ->from('josuej.salazar@gmail.com')
            ->to('salazarj@jefatura.gob.ar')
            ->subject('Cabañas | Restablecer Contraseña')
            ->text('Este es el cuerpo del mensaje en texto plano.')
            ->html('<p>Este es el cuerpo del mensaje en formato <strong>HTML</strong>.</p>');

        // Enviar el correo
        try {
            $mailer->send($email);
            return new Response('Correo enviado con éxito');
        } catch (TransportExceptionInterface $e) {
            return new Response('Error al enviar el correo: ' . $e->getMessage());
        }
    }

    public function recuperarClaveEmail($mailer, $twig, $request, $email)
    {      
        $usuario = $this->em->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        if (!$usuario) {
            throw new HttpException(409, "Usuario no encontrado");
        }

        $this->enviarMailRecuperarClave($mailer, $twig, $usuario, $request);
        return $usuario;
    }
}