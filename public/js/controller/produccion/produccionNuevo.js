$(document).ready(function(){
    $("#guardarProduccion").click(function() {
        if ($("#formProduccion").valid()) {
            $(".loadFones").addClass("working");
            $.ajax({
                url: baseUrlModulo + 'ajax/produccion/nuevo',
                type: 'POST',
                data: $('#formProduccion').serialize(),
                success: function(response) {
                    $(".loadFones").removeClass("working");
                    if (response.code == 200) {
                        swal("Éxito", response.message, "success", function() {
                            window.location = baseUrlModulo + "produccion";
                        });
                    } else {
                        swal("Error", response.message, "error");
                    }
                },
                error: function() {
                    $(".loadFones").removeClass("working");
                    swal("Error", "No se pudo conectar con el servidor", "error");
                }
            });
        }
    });

    $('#formProduccion').validate({
        rules: { fecha: { required: true } },
        messages: { fecha: "La fecha es obligatoria" },
        errorElement: 'span',
        errorClass: 'text-danger'
    });
});