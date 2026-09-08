<?php

namespace App\Controller\Admin;

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
use App\Security\ModuloPermission;

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

    private function validarAdmin () {
        $usuario = $this->getUser();
        $admin = false;
        foreach ($usuario->getRoles() as $rol) {
            if ($rol == 'ROLE_ADMIN'){
                $admin = true;
            }
        }
        if (!$admin){
            throw new AccessDeniedException("No tiene permiso para acceder a esta pagina.");
        }
    }

    #[Route('/admin/inicio', name: 'admin_home')]
    public function home(SerializerInterface $serializer)
    {
        $this->validarAdmin();
        $this->addBreadCrumb("Inicio", true);
        $this->setTitle("Jujuy Hidrocarburos | Inicio");
        return $this->render(
            'admin/home.html.twig', $this->data
        );
    }

    #[Route('/admin/perfil', name: 'admin_perfil')]
    public function perfil(SerializerInterface $serializer) {
        $this->validarAdmin();
        $this->setTitle("Jujuy Hidrocarburos | Perfil");
        $this->addBreadCrumb("Inicio", false, "admin_home");
        $this->addBreadCrumb("Perfil", true);
        
        $usuario = $this->getUserLoggedData();
        $roles = $this->usuarioService->getRoles($serializer);
        $this->data['data'] = $usuario;
        $this->data['roles'] = $roles;
        return $this->render(
                        'admin/perfil.html.twig', $this->data
        );
    }

    #[Route('/admin/perfil/ajax/update', name: 'admin_ajax_perfil_update', methods: ['PUT'])]
    public function perfilAjaxUpdate(Request $request)
    {
        $this->validarAdmin();
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


    #[Route('/admin/pozos', name: 'admin_mapa')]
    public function mapaPozos() {
        $usuario = $this->getUser();
        $this->denyAccessUnlessGranted(ModuloPermission::POZO);
        $this->setTitle("Jujuy Hidrocarburos | Usuarios");
        $this->addBreadCrumb("Inicio", false, "admin_home");
        $this->addBreadCrumb("Usuarios", true);
        $this->data['usuario'] = $usuario;
        $this->data['data'] = null;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        return $this->render(
                        'admin/pozos/listado.html.twig', $this->data
        );
    }

    #[Route('/admin/mapa-render', name: 'admin_mapa_render')]
    public function mapaRender(): Response {
        $this->denyAccessUnlessGranted(ModuloPermission::POZO);
        return $this->render('admin/pozos/mapa_puro.html.twig');
    }


}
