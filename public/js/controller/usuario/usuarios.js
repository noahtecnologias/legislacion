$(document).ready(function(){
    init();

    // Manejar el clic de botones dinámicos usando delegación
    $('#datatablesSimple').on('click', '.editar', function () {
        let usuarioId = $(this).data('id');
        window.location.href = window.location.href = rutasUsuario.editar.replace('__ID__', usuarioId);
    });

    // Manejar el clic de botones dinámicos usando delegación
    $('#datatablesSimple').on('click', '.eliminar', function () {
        let usuarioId = $(this).data('id');
        swal({ 
            title: "Eliminar usuario",
    	    text: "¿Estás seguro de eliminar el usuario?",
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
                        url: rutasUsuario.eliminar.replace('__ID__', usuarioId),
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(data) {
                            $(".loadFones").removeClass("working");
                            swal('Eliminado','Operación éxitosa','success');
                            window.location = baseUrlByRol + "usuarios";
                        },
                        error: function(xhr, status, error) {
                            $(".loadFones").removeClass("working");
                            swal("Ocurrio un error", "Hubo un error al obtener los datos de los usuarios.", "error");
                        },
                    });
                } else {
                    swal('Cancelado', 'Operación cancelada: No se pudo eliminar el usuario.','error');
    		    }	
    	    });
    });

    function generarModulos(usuario) {

        if (!usuario.modulos || usuario.modulos.length === 0) {
            return `
                <span class="badge bg-secondary">
                    Sin módulos
                </span>
            `;
        }

        return usuario.modulos.map(modulo => `
            <span class="badge bg-light text-dark border me-1 mb-1">
                <i class="fas fa-cube me-1 text-primary"></i>
                ${escapeHtml(modulo.nombre)}
            </span>
        `).join('');
    }

    function escapeHtml(text) {

        if (text === null || text === undefined) {
            return '';
        }

        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function listaDeUsuarios() {
        // Realizar una solicitud AJAX cuando la página se carga
        $(".loadFones").addClass("working");
        $.ajax({
            url: rutasUsuario.buscar,
            type: 'GET',
            dataType: 'json',  // Especificamos que esperamos una respuesta en formato JSON
            success: function(data) {
                $(".loadFones").removeClass("working");
                const tableBody = $('#datatablesSimple tbody');
                tableBody.empty();
                let usuarios = JSON.parse(data.data);
                usuarios.forEach(usuario => {
                    let activo = 'Activo';
                    if (usuario.activo == 0 || usuario.activo == null) {
                        activo = 'Inactivo';
                    }
                    tableBody.append(
                        `<tr>
                            <td>${usuario.nombre}</td>
                            <td>${usuario.apellido}</td>
                            <td>${usuario.email}</td>
                            <td>${usuario.celular}</td>
                            <td>${usuario.roles[0].descripcion}</td>
                            <td>
                                ${generarModulos(usuario)}
                            </td>
                            <td>${activo}</td>
                            <td>
                                <button class="btn btn-warning btn-circle editar" data-id="${usuario.id}"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-danger btn-circle eliminar" data-id="${usuario.id}"><i class="fa fa-trash"></i></button>
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
                swal("Ocurrio un error", "Hubo un error al obtener los datos de los usuarios.", "error");
            }
        });
    }

    function init() {
        listaDeUsuarios();
    }
    

});