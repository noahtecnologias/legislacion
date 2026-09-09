$(document).ready(function () {

    let votosOriginales = [];
    let datatable = null;

    init();

    function listaDeVotos() {

        $(".loadFones").addClass("working");

        $.ajax({
            url: rutasVoto.buscar,
            type: 'GET',
            dataType: 'json',

            success: function (data) {

                $(".loadFones").removeClass("working");

                votosOriginales = JSON.parse(data.data);

                cargarFiltros(votosOriginales);

                inicializarDataTable();

                actualizarTabla(votosOriginales);

                inicializarEventosFiltros();
            },

            error: function () {

                $(".loadFones").removeClass("working");

                swal(
                    "Ocurrió un error",
                    "Hubo un error al obtener los datos de los votos.",
                    "error"
                );
            }
        });
    }

    /**
     * Inicializa Simple-DataTables
     */
    function inicializarDataTable() {

        const tabla = document.getElementById('datatablesSimple');

        if (!tabla) {
            return;
        }

        datatable = new simpleDatatables.DataTable(tabla, {
            perPage: 500,
            perPageSelect: [500, 400, 300, 200, 100, 50, 25, 10],

            labels: {
                placeholder: "Buscar...",
                perPage: "registros por página",
                noRows: "No hay registros",
                info: "Mostrando {start} a {end} de {rows} registros"
            }
        });

        /*
         * Cuando Simple-DataTables realiza una búsqueda
         * actualizamos el total visible.
         */
        datatable.on("datatable.search", function () {
            actualizarTotalBusqueda();
        });
    }

    /**
     * Carga los valores de los filtros
     */
    function cargarFiltros(votos) {

        const listas = [...new Set(
            votos
                .map(voto => voto.lista)
                .filter(valor => valor !== null && valor !== undefined)
        )].sort();

        const frentes = [...new Set(
            votos
                .map(voto => voto.frente)
                .filter(valor => valor !== null && valor !== undefined)
        )].sort();

        const departamentos = [...new Set(
            votos
                .filter(voto => voto.departamento)
                .map(voto => voto.departamento.nombre)
        )].sort();

        const municipios = [...new Set(
            votos
                .filter(voto => voto.municipio)
                .map(voto => voto.municipio.nombre)
        )].sort();

        const periodos = [...new Set(
            votos
                .map(voto => voto.periodo)
                .filter(valor => valor !== null && valor !== undefined)
        )].sort();

        llenarSelect("#filtroLista", listas);
        llenarSelect("#filtroFrente", frentes);
        llenarSelect("#filtroDepartamento", departamentos);
        llenarSelect("#filtroMunicipio", municipios);
        llenarSelect("#filtroPeriodo", periodos);
    }

    /**
     * Llena un select
     */
    function llenarSelect(selector, valores) {

        const select = $(selector);

        valores.forEach(function (valor) {

            select.append(
                $('<option>', {
                    value: valor,
                    text: valor
                })
            );
        });
    }

    /**
     * Eventos de los filtros
     */
    function inicializarEventosFiltros() {

        $(
            "#filtroLista, " +
            "#filtroFrente, " +
            "#filtroDepartamento, " +
            "#filtroMunicipio, " +
            "#filtroPeriodo"
        ).on("change", function () {

            aplicarFiltros();
        });

        $("#btnLimpiarFiltros").on("click", function () {

            $("#filtroLista").val("");
            $("#filtroFrente").val("");
            $("#filtroDepartamento").val("");
            $("#filtroMunicipio").val("");
            $("#filtroPeriodo").val("");

            actualizarTabla(votosOriginales);
        });
    }

    /**
     * Aplica los filtros
     */
    function aplicarFiltros() {

        const lista = $("#filtroLista").val();
        const frente = $("#filtroFrente").val();
        const departamento = $("#filtroDepartamento").val();
        const municipio = $("#filtroMunicipio").val();
        const periodo = $("#filtroPeriodo").val();

        const votosFiltrados = votosOriginales.filter(function (voto) {

            if (lista && voto.lista !== lista) {
                return false;
            }

            if (frente && voto.frente !== frente) {
                return false;
            }

            if (
                departamento &&
                (
                    !voto.departamento ||
                    voto.departamento.nombre !== departamento
                )
            ) {
                return false;
            }

            if (
                municipio &&
                (
                    !voto.municipio ||
                    voto.municipio.nombre !== municipio
                )
            ) {
                return false;
            }

            if (periodo && voto.periodo !== periodo) {
                return false;
            }

            return true;
        });

        actualizarTabla(votosFiltrados);
    }

    /**
     * Actualiza los datos de Simple-DataTables
     */
    function actualizarTabla(votos) {

        let total = 0;

        const datos = votos.map(function (voto) {

            const resultado = parseInt(voto.resultado, 10) || 0;

            total += resultado;

            return [
                voto.lista ?? '',
                voto.frente ?? '',
                resultado.toLocaleString('es-AR'),
                voto.departamento?.nombre ?? '',
                voto.municipio?.nombre ?? '',
                voto.periodo ?? ''
            ];
        });

        /*
         * En Simple-DataTables 10.x
         * rows es una propiedad.
         *
         * Para reemplazar todos los datos,
         * limpiamos data y volvemos a insertar.
         */
        datatable.data.data = [];

        datatable.insert({
            data: datos
        });

        /*
         * Actualizamos el total correspondiente
         * a los filtros seleccionados.
         */
        actualizarTotal(total);

        /*
         * Volvemos a la primera página.
         */
        datatable.page(1);
    }

    /**
     * Actualiza el total
     */
    function actualizarTotal(total) {

        $("#totalResultados").text(
            total.toLocaleString('es-AR')
        );
    }

    /**
     * Actualiza el total teniendo en cuenta
     * la búsqueda interna de Simple-DataTables.
     */
    function actualizarTotalBusqueda() {

        /*
         * Si no hay búsqueda, no hacemos nada.
         * El total ya corresponde a los filtros.
         */
        if (!datatable.searching) {
            return;
        }

        const textoBusqueda =
            datatable.searching.toLowerCase();

        let total = 0;

        /*
         * Buscamos sobre los datos actualmente
         * cargados en la tabla.
         */
        votosOriginales.forEach(function (voto) {

            const texto = [
                voto.lista,
                voto.frente,
                voto.resultado,
                voto.departamento?.nombre,
                voto.municipio?.nombre,
                voto.periodo
            ]
                .filter(valor => valor !== null && valor !== undefined)
                .join(" ")
                .toLowerCase();

            if (texto.includes(textoBusqueda)) {

                total += parseInt(voto.resultado, 10) || 0;
            }
        });

        actualizarTotal(total);
    }

    function init() {
        listaDeVotos();
    }

});