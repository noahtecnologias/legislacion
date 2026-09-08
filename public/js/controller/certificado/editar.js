$(document).ready(function () {
    init();

    recalcular();

    $("#guardarCertificado").click(function () {

        if ($("#formCertificado").valid()) {

            $(".loadFones").addClass("working");

            var datosFormulario = $('#formCertificado').serialize();

            $.ajax({
                url: baseUrlModulo + 'ajax/certificado/update',
                type: 'PUT',
                data: datosFormulario,

                success: function (response) {

                    if (response.code == 200) {

                        swal({
                            title: "OK",
                            text: response.message,
                            type: "success"
                        }, function () {
                            window.location = baseUrlModulo + "certificados";
                        });

                    } else {
                        swal("Error", response.message, "error");
                    }
                },

                error: function (xhr) {
                    swal("Error", xhr.responseJSON.message, "error");
                }
            });
        }

    });

    $('#metrosCubicos, #precioBarrilUsd, #cotizacionUsd').on('input', function () {
        recalcular();
    });

    function init() {

        $('#formCertificado').validate({
            ignore: 'hidden',

            rules: {
                numero: { required: true, digits: true },
                destino: { required: true, minlength: 3, maxlength: 255 },
                fecha: { required: true },
                metrosCubicos: { required: true },
                precioBarrilUsd: { required: true },
                cotizacionUsd: { required: true }
            },

            messages: {
                numero: { required: "Ingrese número de certificado" },
                destino: { required: "Ingrese destino" },
                fecha: { required: "Ingrese fecha" },
                metrosCubicos: { required: "Ingrese metros cúbicos" },
                precioBarrilUsd: { required: "Ingrese precio del barril" },
                cotizacionUsd: { required: "Ingrese la cotización USD" }
            }
        });
    }

    function parse(v) {
        if (!v) {
            return new Decimal(0);
        }

        return new Decimal(
            v.toString()
                .replace(/\./g, '')
                .replace(',', '.')
        );
    }

    function format(decimal, decimales = 2) {

        if (!decimal) {
            return '';
        }

        return new Intl.NumberFormat('es-AR', {
            minimumFractionDigits: decimales,
            maximumFractionDigits: decimales
        }).format(decimal.toNumber());
    }

    function recalcular() {

        let metrosCubicos = parse($('#metrosCubicos').val());
        let valorBarril   = parse($('#valorBarril').val());
        let precioUsd     = parse($('#precioBarrilUsd').val());
        let cotizacion    = parse($('#cotizacionUsd').val());

        const barriles = metrosCubicos.times(valorBarril);

        // USD (2 decimales)
        const dolarNeto = barriles.times(precioUsd);
        const dolarIva = dolarNeto.times(new Decimal('0.21'));
        const dolarTotal = dolarNeto.plus(dolarIva);

        // ARS (2 decimales)
        const pesoNeto = dolarNeto.times(cotizacion);

        const pesoIva = dolarIva.times(cotizacion);

        const pesoTotal = pesoNeto.plus(pesoIva);

        // SOLO OUTPUTS (readonly)
        $('#cantidadBarriles').val(format(barriles, 3));

        $('#dolarNeto').val(format(dolarNeto));
        $('#dolarIva').val(format(dolarIva));
        $('#dolarTotal').val(format(dolarTotal));

        $('#pesoNeto').val(format(pesoNeto, 2));
        $('#pesoIva').val(format(pesoIva));
        $('#pesoTotal').val(format(pesoTotal));
    }

});