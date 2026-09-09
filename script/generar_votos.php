<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Entity\Departamento;
use App\Entity\Municipio;
use App\Kernel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Dotenv\Dotenv;

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

$archivoExcel   = __DIR__ . '/../var/import/votos.xlsx';
$archivoSql     = __DIR__ . '/../var/import/votos.sql';
$archivoErrores = __DIR__ . '/../var/import/votos_errores.txt';

/*
|--------------------------------------------------------------------------
| ARRANCAR SYMFONY / DOCTRINE
|--------------------------------------------------------------------------
*/

$dotenv = new Dotenv();
$dotenv->bootEnv(dirname(__DIR__) . '/.env');

$kernel = new Kernel('dev', false);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();

/*
|--------------------------------------------------------------------------
| FUNCIONES
|--------------------------------------------------------------------------
*/

/**
 * Normaliza un nombre para compararlo.
 *
 * Ejemplos:
 *
 * "Manuel Belgrano"
 * "MANUEL BELGRANO"
 * "Manuel  Belgrano"
 * "Manuel-Belgrano"
 * "Manuel Belgrano."
 *
 * terminan siendo:
 *
 * "manuelbelgrano"
 */
function normalizarNombre(?string $valor): string
{
    if ($valor === null) {
        return '';
    }

    $valor = trim($valor);

    if ($valor === '') {
        return '';
    }

    // Minúsculas
    $valor = mb_strtolower($valor, 'UTF-8');

    // Quitar acentos
    $valor = strtr($valor, [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ñ' => 'n',
    ]);

    // Quitar todo lo que no sea letra o número
    $valor = preg_replace('/[^a-z0-9]/', '', $valor);

    return $valor;
}

/**
 * Escapa un valor para SQL.
 */
function sqlString(?string $valor): string
{
    if ($valor === null) {
        return 'NULL';
    }

    return "'" . str_replace("'", "''", trim($valor)) . "'";
}

/**
 * Convierte el resultado del Excel a entero.
 *
 * Ejemplos:
 *
 * 37.570 -> 37570
 * 20.109 -> 20109
 * 63.243 -> 63243
 * 63243  -> 63243
 */
function normalizarResultado($valor): int
{
    if ($valor === null || $valor === '') {
        return 0;
    }

    $valor = trim((string) $valor);

    // El Excel utiliza puntos/comas como separadores de miles.
    $valor = str_replace(['.', ',', ' '], '', $valor);

    // Dejar solamente números y signo negativo.
    $valor = preg_replace('/[^0-9-]/', '', $valor);

    return (int) $valor;
}

/*
|--------------------------------------------------------------------------
| EQUIVALENCIAS DEL EXCEL
|--------------------------------------------------------------------------
|
| El Excel electoral puede utilizar nombres diferentes
| a los almacenados en nuestra BD.
|
| La clave se normaliza antes de utilizarla.
|
*/

/*
 * Departamento:
 *
 * Excel:
 *     Dr. MANUEL BELGRANO
 *
 * BD:
 *     Manuel Belgrano
 */
$equivalenciasDepartamentos = [
    normalizarNombre('Dr. MANUEL BELGRANO')
        => normalizarNombre('Manuel Belgrano'),
];

/*
 * Municipios:
 *
 * La clave es:
 *
 *     DEPARTAMENTO | MUNICIPIO EXCEL
 *
 * y el valor es el nombre del municipio en la BD.
 */
$equivalenciasMunicipios = [
    normalizarNombre('San Pedro') . '|' .
        normalizarNombre('San Pedro')
        => normalizarNombre('San Pedro de Jujuy'),

    normalizarNombre('San Pedro') . '|' .
        normalizarNombre('De Rosario del Rio Grande')
        => normalizarNombre('Rosario de Río Grande'),
];

/*
|--------------------------------------------------------------------------
| VERIFICAR EXCEL
|--------------------------------------------------------------------------
*/

if (!file_exists($archivoExcel)) {
    die("No se encontró el archivo Excel:\n{$archivoExcel}\n");
}

echo "Leyendo Excel...\n";

$spreadsheet = IOFactory::load($archivoExcel);
$sheet = $spreadsheet->getActiveSheet();

$rows = $sheet->toArray(null, true, true, true);

if (empty($rows)) {
    die("El Excel está vacío.\n");
}

/*
|--------------------------------------------------------------------------
| ENCABEZADOS
|--------------------------------------------------------------------------
|
| La fila 4 contiene los encabezados.
|
*/

$filaHeader = 4;

if (!isset($rows[$filaHeader])) {
    die("No existe la fila {$filaHeader} en el Excel.\n");
}

$headers = $rows[$filaHeader];

/*
|--------------------------------------------------------------------------
| MAPEAR COLUMNAS
|--------------------------------------------------------------------------
*/

$columnas = [];

foreach ($headers as $columna => $nombre) {

    $nombreNormalizado = normalizarNombre($nombre);

    if ($nombreNormalizado !== '') {
        $columnas[$nombreNormalizado] = $columna;
    }
}

echo "Columnas encontradas:\n";

foreach ($columnas as $nombre => $columna) {
    echo "  {$nombre} => columna {$columna}\n";
}

/*
|--------------------------------------------------------------------------
| OBTENER COLUMNAS
|--------------------------------------------------------------------------
*/

$colLista = $columnas['lista'] ?? null;

$colFrente = $columnas['frente'] ?? null;

$colResultado =
    $columnas['resultados']
    ?? $columnas['resultado']
    ?? null;

$colDepartamento =
    $columnas['departamento']
    ?? null;

$colMunicipio =
    $columnas['municipalidadcomuna']
    ?? $columnas['municipalidad']
    ?? $columnas['comuna']
    ?? null;

$colPeriodo =
    $columnas['periodo']
    ?? null;

$faltantes = [];

if ($colLista === null) {
    $faltantes[] = 'lista';
}

if ($colFrente === null) {
    $faltantes[] = 'frente';
}

if ($colResultado === null) {
    $faltantes[] = 'resultados';
}

if ($colDepartamento === null) {
    $faltantes[] = 'departamento';
}

if ($colMunicipio === null) {
    $faltantes[] = 'municipalidad/comuna';
}

if ($colPeriodo === null) {
    $faltantes[] = 'periodo';
}

if (!empty($faltantes)) {

    echo "\nFaltan columnas:\n";

    foreach ($faltantes as $faltante) {
        echo "- {$faltante}\n";
    }

    exit(1);
}

/*
|--------------------------------------------------------------------------
| CARGAR DEPARTAMENTOS
|--------------------------------------------------------------------------
*/

echo "\nCargando departamentos desde la BD...\n";

$departamentos = $entityManager
    ->getRepository(Departamento::class)
    ->findAll();

$mapDepartamentos = [];

foreach ($departamentos as $departamento) {

    $nombre = normalizarNombre($departamento->getNombre());

    if ($nombre === '') {
        continue;
    }

    $mapDepartamentos[$nombre] = $departamento->getId();
}

echo "Departamentos cargados: "
    . count($mapDepartamentos)
    . "\n";

/*
|--------------------------------------------------------------------------
| CARGAR MUNICIPIOS
|--------------------------------------------------------------------------
*/

echo "Cargando municipios desde la BD...\n";

$municipios = $entityManager
    ->getRepository(Municipio::class)
    ->findAll();

$mapMunicipios = [];

foreach ($municipios as $municipio) {

    $nombreMunicipio =
        normalizarNombre($municipio->getNombre());

    $departamento =
        $municipio->getDepartamento();

    if (
        $nombreMunicipio === '' ||
        $departamento === null
    ) {
        continue;
    }

    /*
     * La clave incluye el departamento.
     *
     * Ejemplo:
     *
     * 3|sansalvadordejujuy
     */
    $clave =
        $departamento->getId()
        . '|'
        . $nombreMunicipio;

    $mapMunicipios[$clave] =
        $municipio->getId();
}

echo "Municipios cargados: "
    . count($mapMunicipios)
    . "\n";

/*
|--------------------------------------------------------------------------
| GENERAR SQL
|--------------------------------------------------------------------------
*/

$sql = [];

$sql[] = "-- ============================================================";
$sql[] = "-- SQL generado automáticamente desde votos.xlsx";
$sql[] = "-- ============================================================";
$sql[] = "";
$sql[] = "START TRANSACTION;";
$sql[] = "";

$errores = [];

$contador = 0;
$contadorErrores = 0;

/*
|--------------------------------------------------------------------------
| RECORRER EXCEL
|--------------------------------------------------------------------------
|
| Los datos comienzan en la fila 5.
|
*/

foreach ($rows as $numeroFila => $row) {

    if ($numeroFila <= $filaHeader) {
        continue;
    }

    $lista =
        trim((string) ($row[$colLista] ?? ''));

    $frente =
        trim((string) ($row[$colFrente] ?? ''));

    $resultado =
        normalizarResultado(
            $row[$colResultado] ?? null
        );

    $departamentoNombre =
        trim((string) ($row[$colDepartamento] ?? ''));

    $municipioNombre =
        trim((string) ($row[$colMunicipio] ?? ''));

    $periodo =
        trim((string) ($row[$colPeriodo] ?? ''));

    /*
     * Ignorar fila completamente vacía.
     */
    if (
        $lista === '' &&
        $frente === '' &&
        $departamentoNombre === '' &&
        $municipioNombre === '' &&
        $periodo === ''
    ) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCAR DEPARTAMENTO
    |--------------------------------------------------------------------------
    */

    $departamentoKey =
        normalizarNombre($departamentoNombre);

    /*
     * Primero intentamos coincidencia directa.
     */
    $departamentoId =
        $mapDepartamentos[$departamentoKey] ?? null;

    /*
     * Si no existe, buscamos equivalencia.
     */
    if ($departamentoId === null) {

        $departamentoEquivalente =
            $equivalenciasDepartamentos[$departamentoKey]
            ?? null;

        if ($departamentoEquivalente !== null) {

            $departamentoId =
                $mapDepartamentos[$departamentoEquivalente]
                ?? null;
        }
    }

    if ($departamentoId === null) {

        $mensaje = sprintf(
            "Fila %d: Departamento NO encontrado: '%s'",
            $numeroFila,
            $departamentoNombre
        );

        $errores[] = $mensaje;
        $contadorErrores++;

        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCAR MUNICIPIO
    |--------------------------------------------------------------------------
    */

    $municipioKey =
        normalizarNombre($municipioNombre);

    /*
     * Primero intentamos coincidencia directa.
     */
    $claveMunicipio =
        $departamentoId
        . '|'
        . $municipioKey;

    $municipioId =
        $mapMunicipios[$claveMunicipio]
        ?? null;

    /*
     * Si no existe, buscamos una equivalencia.
     */
    if ($municipioId === null) {

        $claveEquivalencia =
            normalizarNombre($departamentoNombre)
            . '|'
            . $municipioKey;

        $municipioEquivalente =
            $equivalenciasMunicipios[$claveEquivalencia]
            ?? null;

        if ($municipioEquivalente !== null) {

            $claveMunicipioEquivalente =
                $departamentoId
                . '|'
                . $municipioEquivalente;

            $municipioId =
                $mapMunicipios[$claveMunicipioEquivalente]
                ?? null;
        }
    }

    if ($municipioId === null) {

        $mensaje = sprintf(
            "Fila %d: Municipio NO encontrado: '%s' | Departamento: '%s' | Departamento ID: %d",
            $numeroFila,
            $municipioNombre,
            $departamentoNombre,
            $departamentoId
        );

        $errores[] = $mensaje;
        $contadorErrores++;

        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $sql[] = "INSERT INTO voto";
    $sql[] = "(";
    $sql[] = "    lista,";
    $sql[] = "    frente,";
    $sql[] = "    fecha_creacion,";
    $sql[] = "    fecha_actualizacion,";
    $sql[] = "    municipio_id,";
    $sql[] = "    departamento_id,";
    $sql[] = "    periodo,";
    $sql[] = "    resultado";
    $sql[] = ")";
    $sql[] = "VALUES";
    $sql[] = "(";
    $sql[] = "    " . sqlString($lista) . ",";
    $sql[] = "    " . sqlString($frente) . ",";
    $sql[] = "    CURRENT_TIMESTAMP,";
    $sql[] = "    CURRENT_TIMESTAMP,";
    $sql[] = "    {$municipioId},";
    $sql[] = "    {$departamentoId},";
    $sql[] = "    " . sqlString($periodo) . ",";
    $sql[] = "    {$resultado}";
    $sql[] = ");";
    $sql[] = "";

    $contador++;
}

/*
|--------------------------------------------------------------------------
| FINALIZAR SQL
|--------------------------------------------------------------------------
*/

$sql[] = "COMMIT;";
$sql[] = "";

file_put_contents(
    $archivoSql,
    implode("\n", $sql)
);

/*
|--------------------------------------------------------------------------
| GUARDAR ERRORES
|--------------------------------------------------------------------------
*/

if (!empty($errores)) {

    file_put_contents(
        $archivoErrores,
        implode("\n", $errores) . "\n"
    );
} else {

    /*
     * Si no hubo errores, eliminar archivo anterior
     * para evitar confusiones.
     */
    if (file_exists($archivoErrores)) {
        unlink($archivoErrores);
    }
}

/*
|--------------------------------------------------------------------------
| RESULTADO
|--------------------------------------------------------------------------
*/

echo "\n";
echo "============================================\n";
echo "IMPORTACIÓN ANALIZADA\n";
echo "============================================\n";
echo "INSERTS generados: {$contador}\n";
echo "Errores: {$contadorErrores}\n";
echo "SQL: {$archivoSql}\n";

if ($contadorErrores > 0) {
    echo "Errores: {$archivoErrores}\n";
}

echo "============================================\n";