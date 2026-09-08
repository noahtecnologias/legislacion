<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\UsuarioService;
use App\Service\ModuloService;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Codes;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Security\ModuloPermission;

class UsuarioController extends BaseController
{
    private UsuarioService $usuarioService;

    // El servicio se inyecta automáticamente
    public function __construct(UsuarioService $usuarioService, ModuloService $moduloService)
    {
        $this->usuarioService = $usuarioService;
        $this->moduloService = $moduloService;
    }

    #[Route('/admin/usuarios', name: 'admin_usuarios_listado')]
    public function usuariosListado() {
        $this->denyAccessUnlessGranted(ModuloPermission::USUARIOS);
        $usuario = $this->getUser();
        $this->setTitle("Jujuy Hidrocarburos | Usuarios");
        $this->data['usuario'] = $usuario;
        $this->data['data'] = null;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        return $this->render(
                        'admin/usuarios/listado.html.twig', $this->data
        );
    }

    /**
     * consulta de usuarios
     */
    #[Route('/admin/ajax/usuarios/search', name: 'admin_ajax_usuario_listado', methods: ['GET'])]
    public function listado(Request $request, \JMS\Serializer\SerializerInterface $serializer) {
        $this->denyAccessUnlessGranted(ModuloPermission::USUARIOS);
        try {
            $usuarios = $this->usuarioService->getUsuarios();
            // Crear el contexto de serialización
            $context = SerializationContext::create()->setGroups(['userdata']);
            $usuariosJson = $serializer->serialize($usuarios, 'json', $context);
            return new JsonResponse(
                [
                    'data' => $usuariosJson,
                    'message' => 'Listado de usuarios éxitoso.',
                    'status' => 'success',
                    'code' => Codes::OK
                ], 
                JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage(),
                'code' => Codes::ERROR
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin/usuario', name: 'admin_usuario_nuevo')]
    public function usuarioNuevo(SerializerInterface $serializer) {
        $this->denyAccessUnlessGranted(ModuloPermission::USUARIOS);
        $usuario = $this->getUser();
        $this->setTitle("Jujuy Hidrocarburos | Nuevo Usuario");
        $roles = $this->usuarioService->getRoles($serializer);
        $modulos = $this->moduloService->getModulos($serializer);
        $this->data['roles'] = $roles;
        $this->data['modulos'] = $modulos;
        $this->data['data'] = null;
        return $this->render(
                        'admin/usuarios/nuevo.html.twig', $this->data
        );
    }

    #[Route('/admin/ajax/usuario/nuevo', name: 'admin_ajax_usuario_nuevo', methods: ['POST'])]
    public function ajaxUsuarioNuevo(Request $request, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::USUARIOS);
        $data = $request->request->all();
        try {
            $this->usuarioService->save($data);
            return new JsonResponse(
                [
                    'message' => 'Usuario creado éxitosamente.',
                    'status' => 'success',
                    'code' => Codes::OK
                ], 
                JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage(),
                'code' => Codes::ERROR
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin/usuario/{id}', name: 'admin_usuario_update')]
    public function updateNuevo($id, SerializerInterface $serializer) {;
        $this->denyAccessUnlessGranted(ModuloPermission::USUARIOS);
        $this->setTitle("Jujuy Hidrocarburos | Modificar Usuario");
        $usuario = $this->usuarioService->getById($id, $serializer);
        $roles = $this->usuarioService->getRoles($serializer);
        $modulos = $this->moduloService->getModulos($serializer);
        $this->data['usuario'] = $usuario;
        $this->data['roles'] = $roles;
        $this->data['data'] = $id;
        $this->data['rolSelected'] = $usuario['roles'][0]['id'];
        $this->data['modulos'] = $modulos;
        return $this->render(
                        'admin/usuarios/editar.html.twig', $this->data
        );
    }

    #[Route('/admin/ajax/usuario/update', name: 'admin_ajax_usuario_update', methods: ['PUT'])]
    public function ajaxUsuarioUpdate(Request $request, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::USUARIOS);
        $data = $request->request->all();
        try {
            $this->usuarioService->update((int)$data['_id'], $data);
            return new JsonResponse(
                [
                    'message' => 'Usuario actualizado éxitosamente.',
                    'status' => 'success',
                    'code' => Codes::OK
                ], 
                JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage(),
                'code' => Codes::ERROR
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin/ajax/usuario/{id}', name: 'admin_ajax_usuario_delete', methods: ['DELETE'])]
    public function ajaxUsuarioDelete($id, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::USUARIOS);
        try {
            $this->usuarioService->delete((int)$id);
            return new JsonResponse(
                [
                    'message' => 'Usuario eliminado éxitosamente.',
                    'status' => 'success',
                    'code' => Codes::OK
                ], 
                JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage(),
                'code' => Codes::ERROR
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
