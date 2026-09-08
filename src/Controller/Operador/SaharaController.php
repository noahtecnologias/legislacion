<?php

namespace App\Controller\Operador;

use App\Controller\BaseController;
use App\Entity\Sahara;
use App\Service\SaharaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Codes;
use App\Security\ModuloPermission;

class SaharaController extends BaseController
{
    private $saharaService;
    private $em;

    public function __construct(SaharaService $saharaService, EntityManagerInterface $em) {
        $this->saharaService = $saharaService;
        $this->em = $em;
    }

    #[Route('/operador/sahara', name: 'app_sahara_index')]
    public function listado(): Response {
        $this->denyAccessUnlessGranted(ModuloPermission::SAHARA);
        $this->setTitle("Jujuy Hidrocarburos | Sahara");
        $this->addBreadCrumb("Inicio", false, "operador_home");
        $this->addBreadCrumb("Sahara", true);
        
        // Pasamos los datos directamente para simple-datatables
        $this->data['registros'] = $this->em->getRepository(Sahara::class)->findBy([], ['fecha' => 'DESC']);
        
        return $this->render('operador/sahara/listado.html.twig', $this->data);
    }

    #[Route('/operador/sahara/nuevo', name: 'app_sahara_new')]
    public function nuevo(): Response {
        $this->denyAccessUnlessGranted(ModuloPermission::SAHARA);
        $this->setTitle("Sahara | Nueva Carga");
        $this->addBreadCrumb("Inicio", false, "operador_home");
        $this->addBreadCrumb("Sahara", false, "app_sahara_index");
        $this->addBreadCrumb("Nuevo", true);
        $this->data['registro'] = null;
        
        return $this->render('operador/sahara/nuevo.html.twig', $this->data);
    }

    #[Route('/operador/ajax/sahara/nuevo', name: 'app_ajax_sahara_nuevo', methods: ['POST'])]
    public function ajaxNuevo(Request $request): JsonResponse {
        $this->denyAccessUnlessGranted(ModuloPermission::SAHARA);
        try {
            $data = $request->request->all();
            $this->saharaService->save($data);
            
            return new JsonResponse([
                'message' => 'Datos guardados con éxito',
                'status' => 'success',
                'code' => Codes::OK
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }


// src/Controller/Operador/SaharaController.php

#[Route('/operador/ajax/sahara/precalcular', name: 'app_ajax_sahara_precalcular', methods: ['POST'])]
public function ajaxPrecalcular(Request $request): JsonResponse 
{
    $this->denyAccessUnlessGranted(ModuloPermission::SAHARA);
    $fechaStr = $request->request->get('fecha');
    $fecha = \DateTime::createFromFormat('d/m/Y', $fechaStr);
    
    if (!$fecha) return new JsonResponse(['error' => 'Fecha inválida'], 400);

    $mes = $fecha->format('m');
    $anio = $fecha->format('Y');

    $calculos = $this->saharaService->calcularDatosSahara($mes, $anio);

    return new JsonResponse([
        'oil_dc'  => number_format($calculos['oil_dc'], 2, ',', ''), // Cuarto parámetro vacío
        'oil'     => number_format($calculos['oil'], 2, ',', ''),
        'npp'     => number_format($calculos['npp'], 2, ',', ''),
        'agua_dc' => number_format($calculos['agua_dc'], 2, ',', ''),
        'wpp'     => number_format($calculos['wpp'], 2, ',', ''),
        'gas_dc'  => number_format($calculos['gas_dc'], 2, ',', ''),
        'gas'     => number_format($calculos['gas'], 2, ',', ''),
        'bruta'   => number_format($calculos['bruta'], 2, ',', ''),
        'porcentaje_w' => number_format($calculos['porcentaje_w'], 2, ',', ''),
        'rgp'     => number_format($calculos['rgp'], 2, ',', '')
    ]);
}


}