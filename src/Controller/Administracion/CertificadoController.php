<?php

namespace App\Controller\Administracion;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Certificado;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\CertificadoService;
use App\Service\ConfiguracionService;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Codes;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Security\ModuloPermission;

class CertificadoController extends BaseController
{
    private CertificadoService $certificadoService;
    private ConfiguracionService $configuracionService;

    // El servicio se inyecta automáticamente
    public function __construct(CertificadoService $certificadoService, ConfiguracionService $configuracionService)
    {
        $this->certificadoService = $certificadoService;
        $this->configuracionService = $configuracionService;
    }

    #[Route('/administracion/certificados', name: 'administracion_certificados_listado')]
    public function usuariosListado() {
        $this->denyAccessUnlessGranted(ModuloPermission::CERTIFICADO);
        $this->setTitle("Jujuy Hidrocarburos | Certificados");
        $this->addBreadCrumb("Inicio", false, "admininstracion_home");
        $this->addBreadCrumb("Certificados", true);
        $this->data['data'] = null;
        $this->data['homeUrl'] = $this->generateUrl($this->getHomeRoute());
        return $this->render(
                        'administracion/certificados/listado.html.twig', $this->data
        );
    }

    /**
     * consulta de certificados
     */
    #[Route('/administracion/ajax/certificados/search', name: 'administracion_ajax_certificado_listado', methods: ['GET'])]
    public function listado(Request $request, \JMS\Serializer\SerializerInterface $serializer) {
        $this->denyAccessUnlessGranted(ModuloPermission::CERTIFICADO);
        try {

            $mes = $request->query->get('mes');
            $anio = $request->query->get('anio');

            $certificados = $this->certificadoService->getCertificados(
                $mes ? (int) $mes : null,
                $anio ? (int) $anio : null
            );
            // Crear el contexto de serialización
            $context = SerializationContext::create()->setGroups(['certificado']);
            $certificaosJson = $serializer->serialize($certificados, 'json', $context);
            return new JsonResponse(
                [
                    'data' => $certificaosJson,
                    'message' => 'Listado de certificados éxitoso.',
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

    #[Route('/administracion/certificado', name: 'administracion_certificado_nuevo')]
    public function usuarioNuevo(SerializerInterface $serializer) {
        $this->denyAccessUnlessGranted(ModuloPermission::CERTIFICADO);
        $this->setTitle("Jujuy Hidrocarburos | Nuevo Certificado");
        $this->addBreadCrumb("Inicio", false, "administracion_home");
        $this->addBreadCrumb("Certificados", false, "administracion_certificados_listado");
        $this->addBreadCrumb("Nuevo", true);
        $configuracion = $this->configuracionService->getConfiguracion();
        $this->data['configuracion'] = $configuracion;
        $this->data['data'] = null;
        return $this->render(
                        'administracion/certificados/nuevo.html.twig', $this->data
        );
    }

    #[Route('/administracion/ajax/certificado/nuevo', name: 'admininstracion_ajax_certificado_nuevo', methods: ['POST'])]
    public function ajaxcertificadoNuevo(Request $request, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::CERTIFICADO);
        $data = $request->request->all();
        try {
            $this->certificadoService->crear($data);
            return new JsonResponse(
                [
                    'message' => 'Certificado creado éxitosamente.',
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

    #[Route('/administracion/certificado/{id}', name: 'administracion_certificado_update')]
    public function updateNuevo($id, SerializerInterface $serializer) {;
        $this->denyAccessUnlessGranted(ModuloPermission::CERTIFICADO);
        $this->setTitle("Jujuy Hidrocarburos | Modificar Usuario");
        $this->addBreadCrumb("Inicio", false, "admin_home");
        $this->addBreadCrumb("Usuarios", false, "admin_usuarios_listado");
        $this->addBreadCrumb("Modificar", true);
        $certificado = $this->certificadoService->getById($id, $serializer);
        $configuracion = $this->configuracionService->getConfiguracion();
        $this->data['certificado'] = $certificado;
        $this->data['configuracion'] = $configuracion;
        $this->data['data'] = $id;
        return $this->render(
                        'administracion/certificados/editar.html.twig', $this->data
        );
    }

    #[Route('/administracion/ajax/certificado/update', name: 'administracion_ajax_certificado_update', methods: ['PUT'])]
    public function ajaxUsuarioUpdate(Request $request, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::CERTIFICADO);
        $data = $request->request->all();
        try {
            $this->certificadoService->actualizar((int)$data['_id'], $data);
            return new JsonResponse(
                [
                    'message' => 'Certificado actualizado éxitosamente.',
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

    #[Route('/administracion/ajax/certificado/{id}', name: 'admininstracion_ajax_certificado_delete', methods: ['DELETE'])]
    public function ajaxUsuarioDelete($id, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->denyAccessUnlessGranted(ModuloPermission::CERTIFICADO);
        try {
            $this->certificadoService->delete((int)$id);
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

    #[Route('/administracion/certificados/export-excel', name: 'certificados_export_excel', methods: ['GET'])]
    public function exportExcel(Request $request)
    {
        $mes  = $request->query->get('mes');
        $anio = $request->query->get('anio');

        $certificados = $this->certificadoService->getCertificados(
            $mes ? (int) $mes : null,
            $anio ? (int) $anio : null
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /* =========================
        * HEADER
        * ========================= */
        $headers = [
            'N°',
            'Destino',
            'Fecha',
            'M3',
            'Barriles',
            'Precio USD',
            'USD Neto',
            'USD IVA',
            'USD Total',
            'Cotización',
            'ARS Neto',
            'ARS IVA',
            'ARS Total',
            'Observaciones'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Estilo header
        /* =========================
        * HEADER
        * ========================= */

        // Combinar celdas
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->mergeCells('D1:D2');
        $sheet->mergeCells('E1:E2');
        $sheet->mergeCells('F1:F2');

        $sheet->mergeCells('G1:I1');

        $sheet->mergeCells('J1:J2');

        $sheet->mergeCells('K1:M1');

        $sheet->mergeCells('N1:N2');

        // Primera fila
        $sheet->setCellValue('A1', 'Certif Nro');
        $sheet->setCellValue('B1', 'DESTINO');
        $sheet->setCellValue('C1', 'Fecha');
        $sheet->setCellValue('D1', 'Metros' . PHP_EOL . 'cúbicos');
        $sheet->setCellValue('E1', 'Cant' . PHP_EOL . 'Barriles');
        $sheet->setCellValue('F1', 'Precio' . PHP_EOL . 'barril U$D');

        $sheet->setCellValue('G1', 'Dólares');

        $sheet->setCellValue('J1', 'Cotiz U$D' . PHP_EOL . 'divisa');

        $sheet->setCellValue('K1', 'Pesos');

        $sheet->setCellValue('N1', 'Observaciones');

        // Segunda fila
        $sheet->setCellValue('G2', 'P.Neto');
        $sheet->setCellValue('H2', 'IVA');
        $sheet->setCellValue('I2', 'P.Total');

        $sheet->setCellValue('K2', 'P.Neto');
        $sheet->setCellValue('L2', 'IVA');
        $sheet->setCellValue('M2', 'P.Total');

        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 9,
                'name' => 'Calibri'
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF']
            ]
        ];

        $sheet->getStyle('A1:N2')->applyFromArray($headerStyle);

        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $sheet->freezePane('A3');

        /* =========================
        * DATA
        * ========================= */
        $row = 3;

        $totalM3 = 0;
        $totalBarriles = 0;
        $totalUsdNeto = 0;
        $totalUsdIva = 0;
        $totalUsdTotal = 0;
        $totalArsNeto = 0;
        $totalArsIva = 0;
        $totalArsTotal = 0;

        foreach ($certificados as $c) {

            $sheet->setCellValue("A$row", $c->getNumero());
            $sheet->setCellValue("B$row", $c->getDestino());
            $sheet->setCellValue("C$row", $c->getFecha()->format('d/m/Y'));

            $sheet->setCellValue("D$row", (float) $c->getMetrosCubicos());
            $sheet->setCellValue("E$row", (float) $c->getCantidadBarriles());
            $sheet->setCellValue("F$row", (float) $c->getPrecioBarrilUsd());

            $sheet->setCellValue("G$row", (float) $c->getDolarNeto());
            $sheet->setCellValue("H$row", (float) $c->getDolarIva());
            $sheet->setCellValue("I$row", (float) $c->getDolarTotal());

            $sheet->setCellValue("J$row", (float) $c->getCotizacionUsd());

            $sheet->setCellValue("K$row", (float) $c->getPesoNeto());
            $sheet->setCellValue("L$row", (float) $c->getPesoIva());
            $sheet->setCellValue("M$row", (float) $c->getPesoTotal());

            $sheet->setCellValue("N$row", $c->getObservacion());

            // acumulados
            $totalM3 += $c->getMetrosCubicos();
            $totalBarriles += $c->getCantidadBarriles();
            $totalUsdNeto += $c->getDolarNeto();
            $totalUsdIva += $c->getDolarIva();
            $totalUsdTotal += $c->getDolarTotal();
            $totalArsNeto += $c->getPesoNeto();
            $totalArsIva += $c->getPesoIva();
            $totalArsTotal += $c->getPesoTotal();

            $row++;
        }

        /* =========================
        * FILA TOTALES
        * ========================= */
        $sheet->setCellValue("A$row", "TOTALES");

        $sheet->setCellValue("D$row", $totalM3);
        $sheet->setCellValue("E$row", $totalBarriles);

        $sheet->setCellValue("G$row", $totalUsdNeto);
        $sheet->setCellValue("H$row", $totalUsdIva);
        $sheet->setCellValue("I$row", $totalUsdTotal);

        $sheet->setCellValue("K$row", $totalArsNeto);
        $sheet->setCellValue("L$row", $totalArsIva);
        $sheet->setCellValue("M$row", $totalArsTotal);

        $sheet->getStyle("A$row:N$row")->getFont()->setBold(true);

        /* =========================
        * FORMATO NUMÉRICO
        * ========================= */
        $numberFormat = '#,##0.00';
        $formats = [
            'D' => '#,##0.000',
            'E' => '#,##0.000',
            'F' => '#,##0.00',
            'G' => '#,##0.00',
            'H' => '#,##0.00',
            'I' => '#,##0.00',
            'J' => '#,##0.00',
            'K' => '#,##0.00',
            'L' => '#,##0.00',
            'M' => '#,##0.00',
        ];

        foreach ($formats as $column => $format) {
            $sheet->getStyle("{$column}3:{$column}{$row}")
                ->getNumberFormat()
                ->setFormatCode($format);
        }

        /* =========================
        * AUTO SIZE
        * ========================= */

        $sheet->getStyle('A1:N2')
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        /* =========================
        * RESPONSE
        * ========================= */
        $writer = new Xlsx($spreadsheet);

        $filename = 'certificados_' . date('Y-m-d_His') . '.xlsx';

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
