<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Service\PotencialService;
use App\Utils\Codes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Security\ModuloPermission;

class PotencialController extends BaseController
{
    private PotencialService $potencialService;

    public function __construct(PotencialService $potencialService)
    {
        parent::__construct();
        $this->potencialService = $potencialService;
    }

    #[Route('/admin/potenciales', name: 'admin_potenciales_listado')]
    public function index() {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES);

        $this->setTitle("Jujuy Hidrocarburos | Potenciales");
        $this->addBreadCrumb("Inicio", false, "admin_home");
        $this->addBreadCrumb("Potenciales", true);
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        
        return $this->render('admin/potenciales/listado.html.twig', $this->data);
    }

    #[Route('/admin/ajax/potenciales/search', name: 'admin_ajax_potenciales_listado', methods: ['GET'])]
    public function ajaxListado(SerializerInterface $serializer) {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES);
        try {
            $potenciales = $this->potencialService->getPotenciales();
            
            // Forzamos el contexto para que los nombres de los campos coincidan con lo que espera tu JS (potencial_neta)
            $context = SerializationContext::create()->setSerializeNull(true);
            $json = $serializer->serialize($potenciales, 'json', $context);
            
            return new JsonResponse([
                'data' => $json,
                'message' => 'Listado exitoso',
                'status' => 'success',
                'code' => Codes::OK
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/admin/potencial', name: 'admin_potencial_nuevo')]
    public function nuevo() {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES);
        $this->setTitle("Nuevo Potencial");
        $this->addBreadCrumb("Inicio", false, "admin_home");
        $this->addBreadCrumb("Potenciales", false, "admin_potenciales_listado");
        $this->addBreadCrumb("Nuevo", true);
        
        return $this->render('admin/potenciales/nuevo.html.twig', $this->data);
    }

    #[Route('/admin/ajax/potencial/nuevo', name: 'admin_ajax_potencial_nuevo', methods: ['POST'])]
    public function ajaxSave(Request $request) {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES);
        try {
            $this->potencialService->save($request->request->all());
            return new JsonResponse(['status' => 'success', 'code' => Codes::OK]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    #[Route('/admin/potencial/{id}', name: 'admin_potencial_editar', methods: ['GET'])]
    public function editar(int $id) {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES);
        $potencial = $this->potencialService->getById($id);
        
        if (!$potencial) {
            return $this->redirectToRoute('admin_potenciales_listado');
        }

        $this->setTitle("Editar Potencial");
        $this->addBreadCrumb("Inicio", false, "admin_home");
        $this->addBreadCrumb("Potenciales", false, "admin_potenciales_listado");
        $this->addBreadCrumb("Editar", true);

        $this->data['potencial'] = $potencial;
        
        return $this->render('admin/potenciales/editar.html.twig', $this->data);
    }

    /**
     * ESTA ES LA RUTA QUE FALTABA: Recibe los datos del formulario de edición
     */
    #[Route('/admin/ajax/potencial/editar/{id}', name: 'admin_ajax_potencial_actualizar', methods: ['POST'])]
    public function ajaxUpdate(int $id, Request $request) {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES);
        try {
            $this->potencialService->update($id, $request->request->all());
            return new JsonResponse([
                'status' => 'success', 
                'code' => Codes::OK,
                'message' => 'Registro actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    #[Route('/admin/ajax/potencial/{id}', name: 'admin_ajax_potencial_eliminar', methods: ['DELETE'])]
    public function ajaxEliminar(int $id): JsonResponse {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES);
        try {
            $this->potencialService->delete($id);
            return new JsonResponse(['status' => 'success', 'code' => Codes::OK]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }
}