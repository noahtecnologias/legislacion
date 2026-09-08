$(document).ready(function () {

    function validarDecimal(valor) {
        const regex = /^(\d{1,3}(\.\d{3})*|\d+)(,\d{1,4})?$/;
        return regex.test(valor);
    }

    function convertirNumero(valor) {
        return valor
            .replace(/\./g, '') // quita miles
            .replace(',', '.'); // decimal SQL
    }

    $('#btnGuardarConfiguracion').on('click', function () {

        let valorBarril = $('#valorBarril').val();
        $(".loadFones").addClass("working");
        $.ajax({
            url: baseUrlModulo + 'configuracion/guardar',
            type: 'POST',
            data: {
                valorBarril: valorBarril
            },
            success: function(response) {
                    $(".loadFones").removeClass("working");
                    if (response.code == 200) {
                        swal({
                            title: "Operación éxitosa",
                            text: response.message,
                            type: "success"
                        },function() {
                            window.location = baseUrlModulo + "configuracion";
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

    });

});