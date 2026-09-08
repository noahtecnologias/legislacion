$(document).ready(function () {

    init();

    $("#nuevoUsuario").click(function () {

        if (!$("#formUsuario").valid()) {
            return;
        }

        const modulosSeleccionados = $('.modulo-checkbox:checked').length;

        if (modulosSeleccionados === 0) {
            swal(
                "Módulos requeridos",
                "Debe seleccionar al menos un módulo.",
                "warning"
            );
            return;
        }

        $(".loadFones").addClass("working");

        const formulario = $('#formUsuario');

        /*
         * Los módulos del Administrador están disabled para evitar
         * que el usuario pueda modificarlos.
         *
         * Como los campos disabled no son enviados por serialize(),
         * los habilitamos momentáneamente antes de serializar.
         */
        const modulosDeshabilitados = $('.modulo-checkbox:disabled');

        modulosDeshabilitados.prop('disabled', false);

        const datosFormulario = formulario.serialize();

        modulosDeshabilitados.prop('disabled', true);

        $.ajax({
            url: usuarioNuevoUrl,
            type: 'POST',
            data: datosFormulario,

            success: function (response) {

                $(".loadFones").removeClass("working");

                if (response.code == 200) {

                    swal({
                        title: "Operación exitosa",
                        text: response.message,
                        type: "success"
                    }, function () {

                        window.location = usuariosListadoUrl;

                    });

                } else {

                    swal(
                        "Ocurrió un error",
                        response.message,
                        "error"
                    );
                }
            },

            error: function (response) {

                $(".loadFones").removeClass("working");

                let mensaje = "Ocurrió un error al guardar el usuario.";

                if (
                    response.responseJSON &&
                    response.responseJSON.error
                ) {
                    mensaje = response.responseJSON.error;
                }

                swal(
                    "Ocurrió un error",
                    mensaje,
                    "error"
                );
            }
        });
    });


    function init() {

        $('#formUsuario').validate({

            ignore: 'hidden',

            rules: {

                nombre: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },

                apellido: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },

                email: {
                    required: true,
                    email: true
                },

                celular: {
                    required: true,
                    minlength: 10,
                    maxlength: 10,
                    digits: true
                },

                rol: {
                    required: true
                },

                password: {
                    required: true,
                    minlength: 6,
                    maxlength: 8
                },

                password1: {
                    required: true,
                    equalTo: "#password"
                }

            },

            messages: {

                nombre: {
                    required: "Por favor, ingresa tu nombre.",
                    minlength: "Tu nombre debe tener al menos 3 caracteres.",
                    maxlength: "Tu nombre debe tener menos de 100 caracteres."
                },

                apellido: {
                    required: "Por favor, ingresa tu apellido.",
                    minlength: "Tu apellido debe tener al menos 3 caracteres.",
                    maxlength: "Tu apellido debe tener menos de 100 caracteres."
                },

                email: {
                    required: "Por favor, ingresa tu correo electrónico.",
                    email: "Por favor, ingresa un correo electrónico válido."
                },

                celular: {
                    required: "Por favor, ingresa tu número de celular.",
                    minlength: "Tu número de celular debe tener 10 dígitos.",
                    maxlength: "Tu número de celular debe tener 10 dígitos.",
                    digits: "Por favor, ingresa solo números."
                },

                rol: {
                    required: "Por favor, seleccione un rol."
                },

                password: {
                    required: "Por favor, ingresa una contraseña.",
                    minlength: "La contraseña debe tener al menos 6 caracteres.",
                    maxlength: "La contraseña no debe superar los 8 caracteres."
                },

                password1: {
                    required: "Por favor, ingresa una contraseña.",
                    equalTo: "Las contraseñas no coinciden."
                }

            }
        });

        actualizarModulosPorRol();
    }


    function actualizarModulosPorRol() {

        const rolSeleccionado = $('#rol option:selected')
            .text()
            .trim()
            .toUpperCase();

        const esAdministrador =
            rolSeleccionado.includes('ADMINISTRADOR');

        $('.modulo-checkbox').each(function () {

            if (esAdministrador) {

                $(this)
                    .prop('checked', true)
                    .prop('disabled', true);

            } else {

                $(this)
                    .prop('disabled', false);
            }
        });

        /*
         * IMPORTANTE:
         * Usar exactamente los IDs definidos en _modulos.html.twig.
         */
        $('#seleccionarTodosModulos')
            .prop('disabled', esAdministrador);

        $('#deseleccionarTodosModulos')
            .prop('disabled', esAdministrador);
    }


    $('#rol').on('change', function () {

        actualizarModulosPorRol();

    });

});