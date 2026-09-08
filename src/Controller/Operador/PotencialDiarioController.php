<?php

namespace App\Controller\Operador;

use App\Controller\BaseController;
use App\Service\PotencialService;
use App\Service\PotencialDiarioService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PotencialDiario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use JMS\Serializer\SerializerInterface;
use App\Security\ModuloPermission;

class PotencialDiarioController extends BaseController
{
    private PotencialService $potencialService;
    private PotencialDiarioService $diarioService;
    private EntityManagerInterface $em;

    public function __construct(PotencialService $potencialService, PotencialDiarioService $diarioService, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->potencialService = $potencialService;
        $this->diarioService = $diarioService;
        $this->em = $em;
    }

    #[Route('/operador/potencialDiario/nuevo', name: 'operador_potencial_diario_nuevo', methods: ['GET'])]
    public function nuevo(SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES_DIARIOS);
        $this->setTitle("Carga Diaria de Potenciales");
        $this->addBreadCrumb("Inicio", false, "operador_home");
        $this->addBreadCrumb("Operaciones", false);
        $this->addBreadCrumb("Nueva Carga", true);

        $potencialesBase = $this->potencialService->getPotenciales();
        
        $this->data['potencialesJson'] = $serializer->serialize($potencialesBase, 'json');
        $this->data['fechaHoy'] = (new \DateTime())->format('Y-m-d');

        return $this->render('operador/potencialDiario/nuevo.html.twig', $this->data);
    }

    #[Route('/operador/ajax/potencialDiario/guardar', name: 'operador_ajax_potencial_diario_guardar', methods: ['POST'])]
    public function ajaxGuardar(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES_DIARIOS);
        try {
            $payload = json_decode($request->getContent(), true);
            
            if (!$payload || !isset($payload['fecha']) || !isset($payload['detalles'])) {
                return new JsonResponse(['message' => 'Datos inválidos estructurados.'], 400);
            }

            // Pasamos la responsabilidad al servicio. Asegurate de que tu PotencialDiarioService 
            // tome el campo $det['tipo'] y haga el $diario->setTipo($det['tipo']) adentro de su lógica.
            $this->diarioService->guardarCargaDiaria($payload['fecha'], $payload['detalles']);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'El parte diario se consolidó de manera exitosa.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    #[Route('/operador/potencialDiario/listado', name: 'operador_potencial_diario_listado', methods: ['GET'])]
    public function listado()
    {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES_DIARIOS);
        $this->setTitle("Historial de Partes Diarios");
        $this->addBreadCrumb("Inicio", false, "operador_home");
        $this->addBreadCrumb("Operaciones", false);
        $this->addBreadCrumb("Historial", true);

        $conn = $this->em->getConnection();
        
        // CORRECCIÓN AQUÍ: Filtramos por tipo != 'agua' para que la sumatoria volumétrica 
        // del total real y la pérdida del día represente solo los hidrocarburos (Petróleo/Gas)
        $sql = "
            SELECT p.fecha, 
                   SUM(CASE WHEN p.tipo != 'agua' THEN CAST(p.real_prod AS DECIMAL(12,3)) ELSE 0 END) as totalReal,
                   SUM(CASE WHEN p.tipo != 'agua' THEN CAST(p.perdida AS DECIMAL(12,3)) ELSE 0 END) as totalPerdida
            FROM potencial_diario p
            GROUP BY p.fecha
            ORDER BY p.fecha DESC
        ";
        
        $this->data['historial'] = $conn->fetchAllAssociative($sql);

        return $this->render('operador/potencialDiario/listado.html.twig', $this->data);
    }

    #[Route('/operador/potencialDiario/detalle/{fecha}', name: 'operador_potencial_diario_detalle', methods: ['GET'])]
    public function detalle(string $fecha, SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::POTENCIALES_DIARIOS);
        $this->setTitle("Detalle del Parte Diario: " . $fecha);
        $this->addBreadCrumb("Inicio", false, "operador_home");
        $this->addBreadCrumb("Historial", false, "operador_potencial_diario_listado");
        $this->addBreadCrumb("Detalle", true);

        // Buscamos todos los registros guardados en esa fecha específica
        $registrosDia = $this->em->getRepository(PotencialDiario::class)->findBy([
            'fecha' => new \DateTime($fecha)
        ]);

        // Mandamos los datos serializados incluyendo el nuevo campo tipo
        $this->data['registrosJson'] = $serializer->serialize($registrosDia, 'json', \JMS\Serializer\SerializationContext::create()->setGroups(['diario_listado']));
        $this->data['fechaParte'] = $fecha;

        return $this->render('operador/potencialDiario/detalle.html.twig', $this->data);
    }
}