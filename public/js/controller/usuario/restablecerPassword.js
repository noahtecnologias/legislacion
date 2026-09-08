$(document).ready(function(){
    init();

    $("#restablecerPassword").click(function() {
        if ($("#formRestablecer").valid()) {
            $(".loadFones").addClass("working");
            var formulario = $('#formRestablecer');
            var url = formulario.attr('action');
            var datosFormulario = formulario.serialize();
            
            $.ajax({
                url: baseUrlPublic + 'ajax/restablecer/password',
                type: 'POST',
                data: datosFormulario,
                success: function(response) {
                    $(".loadFones").removeClass("working");
                    if (response.code == 200) {
                        swal({
                            title: "Operación éxitosa",
                            text: response.message,
                            type: "success"
                        },function() {
                            window.location = baseUrl + "login";
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
        $('#formRestablecer').validate({
            ignore: 'hidden',
            // Definición de las reglas
            rules: {
                email: {
                    required: true,
                    email: true
                }
            },
            // Mensajes personalizados
            messages: {
                email: {
                    required: "Por favor, ingresa tu correo electrónico.",
                    email: "Por favor, ingresa un correo electrónico válido."
                }
            },
        });
    }
    

});