<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Configuracion;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\ConfiguracionService;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Codes;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\ModuloPermission;

class ConfiguracionController extends BaseController
{
    private ConfiguracionService $configuracionService;

    // El servicio se inyecta automáticamente
    public function __construct(
        ConfiguracionService $configuracionService
        )
    {
        $this->configuracionService = $configuracionService;
    }

    #[Route('/admin/configuracion', name: 'admin_configuracion')]
    public function configuracion() {
        $this->denyAccessUnlessGranted(ModuloPermission::CONFIGURACION);
        $this->addBreadCrumb("Inicio", true);
        $this->setTitle("Jujuy Hidrocarburos | Configuración");
        $configuracion = $this->configuracionService->getConfiguracion();
        $this->data['data'] = $configuracion;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        return $this->render(
            'admin/configuracion/configuracion.html.twig', $this->data
        );
    }

    #[Route('/admin/configuracion/guardar', name: 'admin_configuracion_guardar', methods: ['POST'])]
    public function guardarConfiguracion(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(ModuloPermission::CONFIGURACION);
        try {
            $valorBarril = $request->request->get('valorBarril');

            $this->configuracionService->actualizarValorBarril($valorBarril);
            return new JsonResponse(
                [
                    'data' => $valorBarril,
                    'message' => 'Configuración actualizada correctamente.',
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

}
