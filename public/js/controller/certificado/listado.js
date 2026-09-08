$(document).ready(function(){
    let dataTable = null;
    init();

    // Manejar el clic de botones dinámicos usando delegación
    $('#datatablesSimple').on('click', '.editar', function () {
        let certificadoId = $(this).data('id');
        window.location.href = baseUrlModulo + 'certificado/' + certificadoId; 
    });

    // Manejar el clic de botones dinámicos usando delegación
    $('#datatablesSimple').on('click', '.eliminar', function () {
        let certificadoId = $(this).data('id');
        swal({ 
            title: "Eliminar certificado",
    	    text: "¿Estás seguro de eliminar el certificado?",
    		type: "warning",
    		showCancelButton: true,
    		confirmButtonColor: "#DD6B55",
    		confirmButtonText: "Si, Eliminar",
    		closeOnConfirm: false,
    		closeOnCancel: false },
            function(isconfirm) {
                if(isconfirm) {
                    // Ajax DELETE
                    $.ajax({
                        url: baseUrlModulo + 'ajax/certificado/' + certificadoId,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(data) {
                            $(".loadFones").removeClass("working");
                            swal('Eliminado','Operación éxitosa','success');
                            window.location = baseUrlModulo + "certificados";
                        },
                        error: function(xhr, status, error) {
                            $(".loadFones").removeClass("working");
                            swal("Ocurrio un error", "Hubo un error al obtener los datos de los certificados.", "error");
                        },
                    });
                } else {
                    swal('Cancelado', 'Operación cancelada: No se pudo eliminar el certificado.','error');
    		    }	
    	    });
    });

    function formatNumber(n, decimals = 2) {
        if (n === null || n === undefined || n === '') return '';

        n = parseFloat(n);

        if (isNaN(n)) return '';

        return n.toLocaleString('es-AR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';

        let d = new Date(dateStr);

        if (isNaN(d.getTime())) return dateStr;

        let day = String(d.getDate()).padStart(2, '0');
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let year = d.getFullYear();

        return `${day}/${month}/${year}`;
    }

    function listaDeCertificados() {
        // Realizar una solicitud AJAX cuando la página se carga
        $(".loadFones").addClass("working");
        let mes = $('#mes').val();
        let anio = $('#anio').val();

        $.ajax({
            url: baseUrlModulo + 'ajax/certificados/search',
            type: 'GET',
            data: {
                mes: mes,
                anio: anio
            },
            dataType: 'json',  // Especificamos que esperamos una respuesta en formato JSON
            success: function(data) {
                $(".loadFones").removeClass("working");

                if (dataTable) {
                    dataTable.destroy();
                    dataTable = null;
                }

                const tableBody = $('#datatablesSimple tbody');
                tableBody.empty();
               
                let certificados = JSON.parse(data.data);

                let totalMetrosCubicos = 0;
                let totalBarriles = 0;
                let totalPrecioBarrilUsd = 0;

                let totalDolarNeto = 0;
                let totalDolarIva = 0;
                let totalDolarTotal = 0;

                let totalCotizacion = 0;

                let totalPesoNeto = 0;
                let totalPesoIva = 0;
                let totalPesoTotal = 0;
                
                certificados.forEach(certificado => {

                    totalMetrosCubicos += parseFloat(certificado.metros_cubicos || 0);
                    totalBarriles += parseFloat(certificado.cantidad_barriles || 0);
                    totalPrecioBarrilUsd += parseFloat(certificado.precio_barril_usd || 0);

                    totalDolarNeto += parseFloat(certificado.dolar_neto || 0);
                    totalDolarIva += parseFloat(certificado.dolar_iva || 0);
                    totalDolarTotal += parseFloat(certificado.dolar_total || 0);

                    totalCotizacion += parseFloat(certificado.cotizacion_usd || 0);

                    totalPesoNeto += parseFloat(certificado.peso_neto || 0);
                    totalPesoIva += parseFloat(certificado.peso_iva || 0);
                    totalPesoTotal += parseFloat(certificado.peso_total || 0);

                    tableBody.append(`
                        <tr>
                            <td>${certificado.numero}</td>
                            <td>${certificado.destino}</td>
                            <td>${formatDate(certificado.fecha)}</td>

                            <td class="text-end">
                                ${formatNumber(certificado.metros_cubicos, 3)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.cantidad_barriles, 3)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.precio_barril_usd, 2)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.dolar_neto, 2)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.dolar_iva, 2)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.dolar_total, 2)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.cotizacion_usd, 2)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.peso_neto, 2)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.peso_iva, 2)}
                            </td>

                            <td class="text-end">
                                ${formatNumber(certificado.peso_total, 2)}
                            </td>

                            <td>
                                ${certificado.observacion ?? ''}
                            </td>

                            <td>
                                <button
                                    class="btn btn-warning btn-circle editar"
                                    data-id="${certificado.id}"
                                    title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                $('#filaTotales').html(`
                    <th colspan="3">TOTALES</th>

                    <th>${formatNumber(totalMetrosCubicos, 3)}</th>
                    <th>${formatNumber(totalBarriles, 3)}</th>
                    <th>${formatNumber(totalPrecioBarrilUsd, 2)}</th>

                    <th>${formatNumber(totalDolarNeto, 2)}</th>
                    <th>${formatNumber(totalDolarIva, 2)}</th>
                    <th>${formatNumber(totalDolarTotal, 2)}</th>

                    <th>${formatNumber(totalCotizacion, 2)}</th>

                    <th>${formatNumber(totalPesoNeto, 2)}</th>
                    <th>${formatNumber(totalPesoIva, 2)}</th>
                    <th>${formatNumber(totalPesoTotal, 2)}</th>

                    <th></th>
                    <th></th>
                `);

                

                const datatablesSimple = document.getElementById('datatablesSimple');

                if (datatablesSimple) {

                    dataTable = new simpleDatatables.DataTable(
                        datatablesSimple,
                        {
                            searchable: true,
                            fixedHeight: false,
                            perPage: 500,
                            perPageSelect: [100, 200, 300, 400, 500],
                            labels: {
                                placeholder: "Buscar...",
                                perPage: "Items por página",
                                noRows: "No hay certificados para mostrar",
                                info: "Mostrando {start} a {end} de {rows} certificados"
                            }
                        }
                    );
                }
            },
            error: function(xhr, status, error) {
                $(".loadFones").removeClass("working");
                swal("Ocurrio un error", "Hubo un error al obtener los datos de los certificados.", "error");
            }
        });
    }

    $("#buscarCertificados").click(function () {
        listaDeCertificados();
    });

    $("#limpiarFiltros").click(function () {
        $('#mes').val('');
        $('#anio').val(new Date().getFullYear());
        listaDeCertificados();
    });

    function init() {
        listaDeCertificados();
    }

    $("#exportarExcel").click(function () {
        let mes = $('#mes').val();
        let anio = $('#anio').val();

        window.location.href =
            baseUrlModulo + 'certificados/export-excel?mes=' + mes + '&anio=' + anio;
    });
    
});