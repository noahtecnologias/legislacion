<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Compra;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\CompraService;
use App\Service\ModuloService;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Codes;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Security\ModuloPermission;

class CompraController extends BaseController
{
    private CompraService $compraService;

    // El servicio se inyecta automáticamente
    public function __construct(CompraService $compraService, ModuloService $moduloService)
    {
        $this->compraService = $compraService;
        $this->moduloService = $moduloService;
    }

    #[Route('/admin/compras', name: 'admin_compras_listado')]
    public function comprasListado() {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);
        $compra = $this->getUser();
        $this->setTitle("Jujuy Hidrocarburos | Compras");
        $this->data['compra'] = $compra;
        $this->data['data'] = null;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        return $this->render(
                        'admin/compras/listado.html.twig', $this->data
        );
    }

    /**
     * consulta de compras
     */
    #[Route('/admin/ajax/compras/search', name: 'admin_ajax_compra_listado', methods: ['GET'])]
    public function listado(Request $request, \JMS\Serializer\SerializerInterface $serializer) {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);
        try {
            $compras = $this->compraService->getCompras();
            // Crear el contexto de serialización
            $context = SerializationContext::create()->setGroups(['compra']);
            $comprasJson = $serializer->serialize($compras, 'json', $context);
            return new JsonResponse(
                [
                    'data' => $comprasJson,
                    'message' => 'Listado de compras éxitoso.',
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

    #[Route('/admin/compra', name: 'admin_compra_nuevo')]
    public function compraNuevo()
    {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);

        $this->setTitle("Jujuy Hidrocarburos | Nueva Compra");

        $departamentos = $this->compraService->getDepartamentos();

        $this->data['departamentos'] = $departamentos;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        $this->data['data'] = null;

        return $this->render(
            'admin/compras/nueva.html.twig',
            $this->data
        );
    }

    #[Route('/admin/ajax/compra/nuevo', name: 'admin_ajax_compra_nuevo', methods: ['POST'])]
    public function ajaxCompraNuevo(Request $request, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);
        $data = $request->request->all();
        try {
            $this->compraService->save($data);
            return new JsonResponse(
                [
                    'message' => 'Compra creada éxitosamente.',
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

    #[Route('/admin/compra/{id}', name: 'admin_compra_update', methods: ['GET'])]
    public function updateNuevo(int $id)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);

        $this->setTitle("Jujuy Hidrocarburos | Modificar Compra");

        $compra = $this->compraService->getById($id);

        if (!$compra) {
            throw $this->createNotFoundException('La compra no existe.');
        }

        $departamentos = $this->compraService->getDepartamentos();

        $this->data['compra'] = $compra;
        $this->data['departamentos'] = $departamentos;
        $this->data['data'] = $id;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());

        return $this->render(
            'admin/compras/editar.html.twig',
            $this->data
        );
    }

    #[Route('/admin/ajax/compra/update', name: 'admin_ajax_compra_update', methods: ['PUT'])]
    public function ajaxCompraUpdate(Request $request)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);

        $data = $request->request->all();

        try {

            if (empty($data['_id'])) {
                throw new \Exception('No se indicó la compra a modificar.');
            }

            $this->compraService->update(
                (int) $data['_id'],
                $data
            );

            return new JsonResponse([
                'message' => 'Compra actualizada exitosamente.',
                'status' => 'success',
                'code' => Codes::OK
            ], JsonResponse::HTTP_OK);

        } catch (\Exception $e) {

            return new JsonResponse([
                'message' => $e->getMessage(),
                'status' => 'error',
                'code' => Codes::ERROR
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/admin/ajax/compra/{id}', name: 'admin_ajax_compra_delete', methods: ['DELETE'])]
    public function ajaxCompraDelete($id, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);
        try {
            $this->compraService->delete((int)$id);
            return new JsonResponse(
                [
                    'message' => 'Compra eliminada éxitosamente.',
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

    #[Route('/admin/ajax/compra/municipios', name: 'admin_ajax_compra_municipios', methods: ['GET'])]
    public function ajaxCompraMunicipios(
        Request $request,
        \JMS\Serializer\SerializerInterface $serializer
    ) {
        $this->denyAccessUnlessGranted(ModuloPermission::COMPRAS);

        try {

            $departamentoId = (int) $request->query->get('departamento');

            if (!$departamentoId) {
                return new JsonResponse([
                    'message' => 'Debe seleccionar un departamento.',
                    'status' => 'error',
                    'code' => Codes::ERROR
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $municipios = $this->compraService->getMunicipios($departamentoId);

            return new JsonResponse([
                'data' => $serializer->serialize($municipios, 'json'),
                'message' => 'Municipios obtenidos exitosamente.',
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

}
