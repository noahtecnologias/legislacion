// public/js/controller/sahara/sahara_nuevo.js
$(document).ready(function() {
    $('#guardarSahara').on('click', function() {
        let formData = $('#formSahara').serialize();

        $.ajax({
            url: baseUrlModulo + 'ajax/sahara/nuevo', // La ruta que definimos en el Controller
            method: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire('¡Guardado!', response.message, 'success').then(() => {
                    window.location.href = baseUrlModulo + 'sahara';
                });
            },
            error: function(xhr) {
                Swal.fire('Error', 'No se pudo guardar: ' + xhr.responseJSON.message, 'error');
            }
        });
    });
});


// public/js/controller/sahara/sahara_nuevo.js

$(document).ready(function() {
    
    function solicitarPrecalculos() {
        let fecha = $('#fecha').val();
        
        $.ajax({
            url: baseUrlModulo + 'ajax/sahara/precalcular',
            method: 'POST',
            data: { fecha: fecha },
            success: function(response) {
                // Bloque Petróleo
                $('input[name="oil_dc"]').val(response.oil_dc);
                $('input[name="oil"]').val(response.oil);
                $('input[name="npp"]').val(response.npp);
    
                // Bloque Agua
                $('input[name="agua_dc"]').val(response.agua_dc);
                $('input[name="wpp"]').val(response.wpp);
                //bloque gas
                $('input[name="gas_dc"]').val(response.gas_dc);
                $('input[name="gas"]').val(response.gas);
                // Indicadores Finales
                $('input[name="bruta"]').val(response.bruta);
                $('input[name="porcentaje_w"]').val(response.porcentaje_w);
                $('input[name="rgp"]').val(response.rgp);
            }
        });
    }

    // Si cambia la fecha o pierde el foco el input de fecha
    $('#fecha').on('change blur', solicitarPrecalculos);

    // Primera carga al abrir el formulario
    solicitarPrecalculos();
});
