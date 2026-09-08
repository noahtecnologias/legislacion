<?php

namespace App\Controller\Administracion;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\UsuarioService;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Utils\Codes;

class HomeController extends BaseController
{
    private UsuarioService $usuarioService;

    // El servicio se inyecta automáticamente
    public function __construct(
        UsuarioService $usuarioService
    )
    {
        $this->usuarioService = $usuarioService;
    }

    private function validarAdministracion () {
        $usuario = $this->getUser();
        $administracion = false;
        foreach ($usuario->getRoles() as $rol) {
            if ($rol == 'ROLE_ADMINISTRACION'){
                $administracion = true;
            }
        }
        if (!$administracion){
            throw new AccessDeniedException("No tiene permiso para acceder a esta pagina.");
        }
    }

    #[Route('/administracion/inicio', name: 'administracion_home')]
    public function home(SerializerInterface $serializer)
    {
        $this->validarAdministracion();
        $this->addBreadCrumb("Inicio", true);
        $this->setTitle("Jujuy Hidrocarburos | Inicio");
        return $this->render(
            'administracion/home.html.twig', $this->data
        );
    }

    #[Route('/administracion/perfil', name: 'administracion_perfil')]
    public function perfil(SerializerInterface $serializer) {
        $this->validarAdministracion();
        $this->setTitle("Jujuy Hidrocarburos | Perfil");
        $this->addBreadCrumb("Inicio", false, "administracion_home");
        $this->addBreadCrumb("Perfil", true);
        
        $usuario = $this->getUserLoggedData();
        $roles = $this->usuarioService->getRoles($serializer);
        $this->data['data'] = $usuario;
        $this->data['roles'] = $roles;
        return $this->render(
                        'administracion/perfil.html.twig', $this->data
        );
    }

    #[Route('/administracion/perfil/ajax/update', name: 'administracion_ajax_perfil_update', methods: ['PUT'])]
    public function perfilAjaxUpdate(Request $request)
    {
        $this->validarAdministracion();
        $data = $request->request->all();
        $usuario = $this->getUser();
        try {
            $this->usuarioService->update($usuario->getId(), $data);
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

}
