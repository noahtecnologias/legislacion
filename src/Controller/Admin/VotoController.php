<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Voto;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\VotoService;
use App\Service\ModuloService;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Codes;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use App\Security\ModuloPermission;

class VotoController extends BaseController
{
    private VotoService $votoService;

    // El servicio se inyecta automáticamente
    public function __construct(VotoService $votoService, ModuloService $moduloService)
    {
        $this->votoService = $votoService;
        $this->moduloService = $moduloService;
    }

    #[Route('/admin/votos', name: 'admin_votos_listado')]
    public function votosListado() {
        $this->denyAccessUnlessGranted(ModuloPermission::VOTOS);
        $voto = $this->getUser();
        $this->setTitle("Legislación | Votos");
        $this->data['voto'] = $voto;
        $this->data['data'] = null;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        return $this->render(
                        'admin/votos/listado.html.twig', $this->data
        );
    }

    /**
     * consulta de votos
     */
    #[Route('/admin/ajax/votos/search', name: 'admin_ajax_voto_listado', methods: ['GET'])]
    public function listado(Request $request, \JMS\Serializer\SerializerInterface $serializer) {
        $this->denyAccessUnlessGranted(ModuloPermission::VOTOS);
        try {
            $votos = $this->votoService->getVotos();
            // Crear el contexto de serialización
            $context = SerializationContext::create()->setGroups(['voto']);
            $votosJson = $serializer->serialize($votos, 'json', $context);
            return new JsonResponse(
                [
                    'data' => $votosJson,
                    'message' => 'Listado de votos éxitoso.',
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
