$(document).ready(function () {

    let votosOriginales = [];

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

                actualizarTablaResultados(votosOriginales);

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

    function actualizarMunicipios(votos, departamentoId) {

        const select = $("#filtroMunicipio");

        // Limpiar municipios actuales
        select.find("option:not(:first)").remove();

        const municipios = new Map();

        votos.forEach(function (voto) {

            if (!voto.municipio || !voto.departamento) {
                return;
            }

            // Si hay departamento seleccionado,
            // solo tomamos los municipios de ese departamento
            if (
                departamentoId &&
                String(voto.departamento.id) !== String(departamentoId)
            ) {
                return;
            }

            municipios.set(
                voto.municipio.id,
                voto.municipio
            );
        });

        [...municipios.values()]
            .sort((a, b) => a.nombre.localeCompare(b.nombre))
            .forEach(function (municipio) {

                select.append(
                    $('<option>', {
                        value: municipio.id,
                        text: municipio.nombre
                    })
                );
            });

        // Volver siempre a "Todos"
        select.val("");
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

        const departamentos = [...new Map(
            votos
                .filter(voto => voto.departamento)
                .map(voto => [
                    voto.departamento.id,
                    voto.departamento
                ])
        ).values()]
            .sort((a, b) => a.nombre.localeCompare(b.nombre));

        llenarSelectDepartamentos("#filtroDepartamento", departamentos);

        // Al iniciar mostramos todos los municipios
        actualizarMunicipios(votos, "");

        const periodos = [...new Set(
            votos
                .map(voto => voto.periodo)
                .filter(valor => valor !== null && valor !== undefined)
        )].sort();

        llenarSelect("#filtroLista", listas);
        llenarSelect("#filtroFrente", frentes);
        llenarSelect("#filtroPeriodo", periodos);
    }

    function llenarSelectDepartamentos(selector, departamentos) {

        const select = $(selector);

        select.find("option:not(:first)").remove();

        departamentos.forEach(function (departamento) {

            select.append(
                $('<option>', {
                    value: departamento.id,
                    text: departamento.nombre
                })
            );
        });
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

        $("#filtroDepartamento").on("change", function () {

            const departamentoId = $(this).val();

            // Actualizamos los municipios
            actualizarMunicipios(
                votosOriginales,
                departamentoId
            );

            // Aplicamos nuevamente todos los filtros
            aplicarFiltros();
        });

        $(
            "#filtroLista, " +
            "#filtroFrente, " +
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

            // Restauramos todos los municipios
            actualizarMunicipios(
                votosOriginales,
                ""
            );

            actualizarTablaResultados(votosOriginales);
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

            if (lista && String(voto.lista) !== String(lista)) {
                return false;
            }

            if (frente && String(voto.frente) !== String(frente)) {
                return false;
            }

            if (
                departamento &&
                (
                    !voto.departamento ||
                    String(voto.departamento.id) !== String(departamento)
                )
            ) {
                return false;
            }

            if (
                municipio &&
                (
                    !voto.municipio ||
                    String(voto.municipio.id) !== String(municipio)
                )
            ) {
                return false;
            }

            if (
                periodo &&
                String(voto.periodo) !== String(periodo)
            ) {
                return false;
            }

            return true;
        });

        actualizarTablaResultados(votosFiltrados);
    }

    /**
     * Agrupa los votos filtrados por Lista + Frente + Periodo
     * y actualiza la tabla de resultados
     */
    function actualizarTablaResultados(votos) {

        const resultados = new Map();

        votos.forEach(function (voto) {

            const lista = voto.lista ?? '';
            const frente = voto.frente ?? '';
            const municipio = voto.municipio?.nombre ?? '';
            const periodo = voto.periodo ?? '';
            const resultado = parseInt(voto.resultado, 10) || 0;

            /*
            * Agrupamos por:
            * Lista + Frente + Municipio + Periodo
            */
            const clave = [
                lista,
                frente,
                municipio,
                periodo
            ].join('|');

            if (!resultados.has(clave)) {

                resultados.set(clave, {
                    lista: lista,
                    frente: frente,
                    municipio: municipio,
                    resultado: 0,
                    periodo: periodo
                });
            }

            resultados.get(clave).resultado += resultado;
        });

        const datos = [...resultados.values()];

        const tbody = $("#tablaResultados tbody");

        tbody.empty();

        if (datos.length === 0) {

            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center">
                        No hay resultados para los filtros seleccionados
                    </td>
                </tr>
            `);

            return;
        }

        datos.forEach(function (resultado) {

            tbody.append(`
                <tr>
                    <td>${escapeHtml(resultado.lista)}</td>
                    <td>${escapeHtml(resultado.frente)}</td>
                    <td class="text-end">
                        ${resultado.resultado.toLocaleString('es-AR')}
                    </td>
                    <td>${escapeHtml(resultado.municipio)}</td>
                    <td>${escapeHtml(resultado.periodo)}</td>
                </tr>
            `);
        });
    }

    function escapeHtml(valor) {

        return String(valor)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function init() {
        listaDeVotos();
    }

});