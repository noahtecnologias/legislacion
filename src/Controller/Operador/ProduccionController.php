<?php

namespace App\Controller\Operador;

use App\Controller\BaseController;
use App\Service\ProduccionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse; // Importante
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Codes;
use JMS\Serializer\SerializerInterface;
use App\Security\ModuloPermission;

class ProduccionController extends BaseController
{
    private $produccionService;

    public function __construct(ProduccionService $produccionService) {
        $this->produccionService = $produccionService;
    }

    #[Route('/operador/produccion', name: 'operador_produccion_listado')]
    public function listado(): Response {
        $this->denyAccessUnlessGranted(ModuloPermission::PRODUCCION_DIARIA);
        $this->setTitle("Jujuy Hidrocarburos | Producción");
        $this->addBreadCrumb("Inicio", false, "operador_home");
        $this->addBreadCrumb("Producción", true);
        
        return $this->render('operador/produccion/listado.html.twig', $this->data);
    }

    #[Route('/operador/produccion/nuevo', name: 'operador_produccion_nuevo')]
    public function nuevo(): Response {
        $this->denyAccessUnlessGranted(ModuloPermission::PRODUCCION_DIARIA);
        $this->setTitle("Jujuy Hidrocarburos | Nueva Carga");
        $this->addBreadCrumb("Inicio", false, "operador_home");
        $this->addBreadCrumb("Producción", false, "operador_produccion_listado");
        $this->addBreadCrumb("Nuevo", true);
        
        return $this->render('operador/produccion/nuevo.html.twig', $this->data);
    }

    #[Route('/operador/ajax/produccion/nuevo', name: 'operador_ajax_produccion_nuevo', methods: ['POST'])]
    public function ajaxNuevo(Request $request) {
        $this->denyAccessUnlessGranted(ModuloPermission::PRODUCCION_DIARIA);
        try {
            $data = $request->request->all();
            $this->produccionService->save($data);
            
            // Usamos el formato que ya te funciona en Usuarios
            return new JsonResponse([
                'message' => 'Producción guardada éxitosamente.',
                'status' => 'success',
                'code' => Codes::OK
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage(),
                'code' => Codes::ERROR
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
#[Route('/operador/ajax/produccion/listado', name: 'operador_ajax_produccion_listado')]
    public function ajaxListado(SerializerInterface $serializer) { // <--- Inyectamos aquí
        try {
            $this->denyAccessUnlessGranted(ModuloPermission::PRODUCCION_DIARIA);
            // Ahora usamos el $serializer que entra por parámetro
            $res = $this->produccionService->getListado($serializer);
            
            return new JsonResponse([
                'data' => $res,
                'message' => 'Éxito',
                'code' => 200
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}