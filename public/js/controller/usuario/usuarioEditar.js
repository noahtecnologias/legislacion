$(document).ready(function () {

    init();

    $("#updateUsuario").click(function () {

        if (!$("#formUsuario").valid()) {
            return;
        }

        /*
         * Verificamos que exista al menos un módulo seleccionado.
         */
        if ($('.modulo-checkbox:checked').length === 0) {
            swal(
                "Módulos requeridos",
                "Debe seleccionar al menos un módulo.",
                "warning"
            );
            return;
        }

        $(".loadFones").addClass("working");

        var formulario = $('#formUsuario');

        /*
         * Los módulos del Administrador están disabled.
         * Los habilitamos momentáneamente para que serialize()
         * los incluya en la petición.
         */
        const modulosDeshabilitados = $('.modulo-checkbox:disabled');

        modulosDeshabilitados.prop('disabled', false);

        var datosFormulario = formulario.serialize();

        /*
         * Volvemos a bloquearlos visualmente después de serializar.
         */
        modulosDeshabilitados.prop('disabled', true);

        $.ajax({
            url: usuarioUpdateUrl,
            type: 'PUT',
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

                let mensaje = "Ocurrió un error al actualizar el usuario.";

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
                    minlength: 6,
                    maxlength: 8
                },

                password1: {
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
                    minlength: "La contraseña debe tener al menos 6 caracteres.",
                    maxlength: "La contraseña no debe superar los 8 caracteres."
                },

                password1: {
                    equalTo: "Las contraseñas no coinciden."
                }

            }

        });

        /*
         * Aplicar estado inicial según el rol seleccionado.
         */
        actualizarModulosPorRol();
    }


    /**
     * Habilita/deshabilita los módulos dependiendo del rol.
     *
     * Administrador:
     * - Todos los módulos seleccionados.
     * - Todos los módulos bloqueados.
     * - Botones de selección bloqueados.
     *
     * Otros roles:
     * - Los módulos quedan editables.
     * - Se mantienen las selecciones existentes.
     */
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
         * Los botones también se bloquean para Administrador.
         */
        $('#seleccionarTodosModulos')
            .prop('disabled', esAdministrador);

        $('#deseleccionarTodosModulos')
            .prop('disabled', esAdministrador);
    }


    /*
     * Si cambia el rol, actualizamos inmediatamente
     * el estado de los módulos.
     */
    $('#rol').on('change', function () {

        actualizarModulosPorRol();

    });


    /*
     * Seleccionar todos los módulos.
     */
    $('#seleccionarTodosModulos').on('click', function () {

        $('.modulo-checkbox')
            .prop('checked', true);

    });


    /*
     * Deseleccionar todos los módulos.
     */
    $('#deseleccionarTodosModulos').on('click', function () {

        $('.modulo-checkbox')
            .prop('checked', false);

    });

});