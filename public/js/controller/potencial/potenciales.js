$(document).ready(function() {
    init();

    // Evento Editar (Delegado para que funcione en todas las tablas)
    $('table').on('click', '.editar', function() {
        let id = $(this).data('id');
        window.location.href = baseUrlModulo + 'potencial/' + id;
    });

    // Evento Eliminar (Delegado con SweetAlert)
    $('table').on('click', '.eliminar', function() {
        let id = $(this).data('id');
        swal({
            title: "¿Estás seguro?",
            text: "El registro de potencial será eliminado permanentemente",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: baseUrlModulo + 'ajax/potencial/' + id,
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(data) {
                        swal("¡Eliminado!", "El registro ha sido borrado.", "success");
                        listaDePotenciales(); // Recargamos las tablas
                    },
                    error: function(xhr) {
                        swal("Error", "No se pudo eliminar el registro.", "error");
                    }
                });
            }
        });
    });

    function listaDePotenciales() {
        $(".loadFones").addClass("working");
        $.ajax({
            url: baseUrlModulo + 'ajax/potenciales/search',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $(".loadFones").removeClass("working");
                
                const bodyPetroleo = $('#tablaPetroleo tbody').empty();
                const bodyAgua = $('#tablaAgua tbody').empty();
                const bodyGas = $('#tablaGas tbody').empty();

                let potenciales = JSON.parse(data.data);

                potenciales.forEach(p => {
                    let fila = `<tr>
                        <td>${p.pozo}</td>
                        <td>${p.potencial_neta}</td>
                        <td>
                            <button class="btn btn-warning btn-sm editar" data-id="${p.id}"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm eliminar" data-id="${p.id}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`;

                    let tipo = p.tipo.toLowerCase();
                    if (tipo === 'petróleo' || tipo === 'petroleo') {
                        bodyPetroleo.append(fila);
                    } else if (tipo === 'agua') {
                        bodyAgua.append(fila);
                    } else if (tipo === 'gas') {
                        bodyGas.append(fila);
                    }
                });

                const ids = ['tablaPetroleo', 'tablaAgua', 'tablaGas'];
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) { 
                        // Destruir instancia previa si existe para evitar duplicados al recargar
                        new simpleDatatables.DataTable(el); 
                    }
                });
            },
            error: function() {
                $(".loadFones").removeClass("working");
                swal("Error", "No se pudieron cargar los potenciales", "error");
            }
        });
    }

    function init() {
        listaDePotenciales();
    }
});