$(document).ready(function(){
    init();

    $("#nuevaReserva").click(function() {
        if ($("#formReserva").valid()) {
            $(".loadFones").addClass("working");
            var formulario = $('#formReserva');
            var datosFormulario = formulario.serialize();
            var cabaniaId = $("#_id").val();
            $.ajax({
                url: baseUrlByRol + 'ajax/reserva/nueva',
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
                            window.location = baseUrlByRol + "cabania/"+cabaniaId+"/detalle";
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
        $('#formReserva').validate({
            ignore: 'hidden',
            // Definición de las reglas
            rules: {
                fecha_desde: {
                    required: true,
                    date: true
                },
                fecha_hasta: {
                    required: true,
                    date: true
                },
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
                dni: {
                    digits: true
                },
                cantidad: {
                    required: true,
                    digits: true
                },
                origen: {
                    required: true
                },
                importe: {
                    required: true,
                    number: true
                }
            },
            // Mensajes personalizados
            messages: {
                fecha_desde: {
                    required: "Por favor, ingresa una fecha.",
                    date: "Por favor ingresa una fecha valida."
                },
                fecha_hasta: {
                    required: "Por favor, ingresa una fecha.",
                    date: "Por favor ingresa una fecha valida."
                },
                nombre: {
                    required: "Por favor, ingresa el nombre.",
                    minlength: "Tu nombre debe tener al menos 3 caracteres.",
                    maxlength: "Tu nombre debe tener menos 100 caracteres."
                },
                apellido: {
                    required: "Por favor, ingresa el apellido.",
                    minlength: "Tu nombre debe tener al menos 3 caracteres.",
                    maxlength: "Tu nombre debe tener menos 100 caracteres."
                },
                dni: {
                    digits: "Pro favor, ingresa solo números."
                },
                origen: {
                    required: "Por favor, seleccione un origen de venta.",
                },
                cantidad: {
                    required: "Por favor, seleccione un origen de venta.",
                    digits: "Pro favor, ingresa solo números."
                },
                importe: {
                    required: "Por favor, seleccione un origen de venta.",
                    number: "Pro favor, ingresa solo números."
                }
            },
        });
    }
    

});