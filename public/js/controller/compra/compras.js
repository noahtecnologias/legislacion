$(document).ready(function(){
    init();

    // Manejar el clic de botones dinámicos usando delegación
    $('#datatablesSimple').on('click', '.editar', function () {
        let compraId = $(this).data('id');
        window.location.href = window.location.href = rutasCompras.editar.replace('__ID__', compraId);
    });

    // Manejar el clic de botones dinámicos usando delegación
    $('#datatablesSimple').on('click', '.eliminar', function () {
        let compraId = $(this).data('id');
        swal({ 
            title: "Eliminar compra",
    	    text: "¿Estás seguro de eliminar la compra?",
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
                        url: rutasCompras.eliminar.replace('__ID__', compraId),
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(data) {
                            $(".loadFones").removeClass("working");
                            swal('Eliminado','Operación éxitosa','success');
                            window.location = rutasCompras.listado;
                        },
                        error: function(xhr, status, error) {
                            $(".loadFones").removeClass("working");
                            swal("Ocurrio un error", "Hubo un error al obtener los datos de las compras.", "error");
                        },
                    });
                } else {
                    swal('Cancelado', 'Operación cancelada: No se pudo eliminar la compra.','error');
    		    }	
    	    });
    });

    function formatearFecha(fecha) {
        if (!fecha) {
            return '';
        }

        const date = new Date(fecha);

        if (isNaN(date.getTime())) {
            return fecha;
        }

        const dia = String(date.getDate()).padStart(2, '0');
        const mes = String(date.getMonth() + 1).padStart(2, '0');
        const anio = date.getFullYear();

        return `${dia}/${mes}/${anio}`;
    }

    function listaDeCompras() {
        // Realizar una solicitud AJAX cuando la página se carga
        $(".loadFones").addClass("working");
        $.ajax({
            url: rutasCompras.buscar,
            type: 'GET',
            dataType: 'json',  // Especificamos que esperamos una respuesta en formato JSON
            success: function(data) {
                $(".loadFones").removeClass("working");
                const tableBody = $('#datatablesSimple tbody');
                tableBody.empty();
                let compras = JSON.parse(data.data);
                compras.forEach(compra => {
                    tableBody.append(
                        `<tr>
                            <td>${compra.departamento.nombre}</td>
                            <td>${compra.municipio.nombre}</td>
                            <td>${formatearFecha(compra.fecha)}</td>
                            <td>${compra.descripcion}</td>
                            <td>${compra.unidades}</td>
                            <td>${compra.observacion}</td>
                            <td>
                                <button class="btn btn-warning btn-circle editar" data-id="${compra.id}"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-danger btn-circle eliminar" data-id="${compra.id}"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>`
                    );
                });
                const datatablesSimple = document.getElementById('datatablesSimple');
                if (datatablesSimple) {
                    new simpleDatatables.DataTable(datatablesSimple);
                }
            },
            error: function(xhr, status, error) {
                $(".loadFones").removeClass("working");
                swal("Ocurrio un error", "Hubo un error al obtener los datos de las compras.", "error");
            }
        });
    }

    function init() {
        listaDeCompras();
    }
    

});