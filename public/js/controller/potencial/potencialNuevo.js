$(document).ready(function() {
    
    $("#guardarPotencial").click(function() {
        // 1. Obtención de valores
        const pozo = $("#pozo").val();
        const potencialNeta = $("#potencial_neta").val();
        const tipo = $("#tipo").val();

        // 2. Validación de campos obligatorios
        if (pozo === "" || potencialNeta === "" || tipo === "") {
            swal({
                title: "Campos incompletos",
                text: "Por favor, completa el número de pozo, la potencial neta y el tipo.",
                type: "warning",
                confirmButtonColor: "#f8bb86",
                confirmButtonText: "Entendido"
            });
            return; // Corta la ejecución si falta algo
        }

        // 3. Preparación de datos y envío AJAX
        const formData = $("#formPotencial").serialize();
        
        $(".loadFones").addClass("working"); // Animación de carga si la tenés definida

        $.ajax({
            url: baseUrlModulo + 'ajax/potencial/nuevo',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $(".loadFones").removeClass("working");
                
                if (response.code === 200) {
                    swal({
                        title: "¡Éxito!",
                        text: "El registro de potencial se guardó correctamente.",
                        type: "success",
                        confirmButtonText: "OK"
                    }, function() {
                        // Redirigir al listado al cerrar el mensaje
                        window.location.href = baseUrlModulo + "potenciales";
                    });
                } else {
                    swal("Error", "No se pudo guardar: " + response.message, "error");
                }
            },
            error: function(xhr) {
                $(".loadFones").removeClass("working");
                let errorMsg = "Hubo un problema con el servidor.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                swal("Ocurrió un error", errorMsg, "error");
            }
        });
    });

    // Opcional: Permitir guardar al presionar 'Enter' en los inputs
    $("#formPotencial input").keypress(function(e) {
        if (e.which == 13) {
            $("#guardarPotencial").click();
        }
    });
});