$(document).ready(function() {
    init(); 

    $("#updatePerfil").click(function() {
        if ($("#formPerfil").valid()) {
            $(".loadFones").addClass("working");
            var formulario = $('#formPerfil');
            var url = formulario.attr('action');
            var datosFormulario = formulario.serialize();
            
            $.ajax({
                url: url,
                type: 'PUT',
                data: datosFormulario,
                success: function(response) {
                    $(".loadFones").removeClass("working");
                    if (response.code == 200) {
                        swal({
                            title: "Operación éxitosa",
                            text: response.message,
                            type: "success"
                        },function() {
                            window.location = baseUrlByRol + "perfil";
                        });
                    } else {
                        swal("Ocurrio un error", response.message, "error");
                    }
                },
                error: function(response) {
                    $(".loadFones").removeClass("working");
                    swal("Ocurrio un error", response.responseJSON.error, "error");
                }
            });
        }
    });
    
    function init() {
        $('#formPerfil').validate({
            ignore: 'hidden',
            // Definición de las reglas
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
                password: {
                    minlength: 6,
                    maxlength: 8
                },
                password1: {
                    equalTo: "#password"
                },
            },
            // Mensajes personalizados
            messages: {
                nombre: {
                    required: "Por favor, ingresa tu nombre.",
                    minlength: "Tu nombre debe tener al menos 3 caracteres.",
                    maxlength: "Tu nombre debe tener menos 100 caracteres."
                },
                apellido: {
                    required: "Por favor, ingresa tu nombre.",
                    minlength: "Tu nombre debe tener al menos 3 caracteres.",
                    maxlength: "Tu nombre debe tener menos 100 caracteres."
                },
                email: {
                    required: "Por favor, ingresa tu correo electrónico.",
                    email: "Por favor, ingresa un correo electrónico válido."
                },
                celular: {
                    required: "Por favor, ingresa tu nombre.",
                    minlength: "Tu número de celular debe tener al menos 10 digitos.",
                    maxlength: "Tu número de celular debe tener al menos 10 digitos.",
                    digits: "Pro favor, ingresa solo números."
                },
                password: {
                    required: "Por favor, ingresa una contraseña.",
                    minlength: "La contraseña debe tener al menos 6 caracteres."
                },
                password1: {
                    equalTo: "Por favor, ingresa una contraseña que coincida con el campo Password."
                },
            },
        });
    }
    

});