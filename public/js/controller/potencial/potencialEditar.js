$(document).ready(function() {
    
    $("#actualizarPotencial").click(function() {
        // 1. Obtención de valores
        const id = $("#potencialId").val();
        const pozo = $("#pozo").val();
        const potencialNeta = $("#potencial_neta").val();
        const tipo = $("#tipo").val();

        // 2. Validación manual extra antes de enviar
        if (!pozo || !potencialNeta || !tipo) {
            swal({
                title: "Atención",
                text: "Todos los campos son obligatorios.",
                type: "warning",
                confirmButtonText: "Revisar"
            });
            return;
        }

        // 3. Preparación de datos
        // Aseguramos que serialize() capture todo, pero podrías enviar un objeto manual si falla
        const formData = $("#formPotencial").serialize();
        
        $(".loadFones").addClass("working");

        $.ajax({
            url: baseUrlModulo + 'ajax/potencial/editar/' + id,
            type: 'POST', 
            data: formData,
            dataType: 'json',
            success: function(response) {
                $(".loadFones").removeClass("working");
                
                // IMPORTANTE: Verificamos tanto response.code como el status success
                if (response.code == 200 || response.status === 'success') {
                    swal({
                        title: "¡Actualizado!",
                        text: "Los cambios se guardaron correctamente.",
                        type: "success",
                        confirmButtonText: "OK"
                    }, function() {
                        window.location.href = baseUrlModulo + "potenciales";
                    });
                } else {
                    swal("Error", response.message || "No se pudo actualizar", "error");
                }
            },
            error: function(xhr) {
                $(".loadFones").removeClass("working");
                console.error(xhr.responseText); // Para que veas el error real en la consola F12
                let msg = "Error crítico en el servidor.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                swal("Ocurrió un error", msg, "error");
            }
        });
    });

    // Soporte para Enter
    $("#formPotencial input").keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault(); // Evita que el formulario se envíe de forma tradicional
            $("#actualizarPotencial").click();
        }
    });
});