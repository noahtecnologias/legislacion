$(document).ready(function () {

    init();

    function init() {
        inicializarEventos();

        // jquery validate del form
        $('#formCompra').validate({

            ignore: 'hidden',

            rules: {

                departamento: {
                    required: true
                },

                municipio: {
                    required: true
                },

                fecha: {
                    required: true,
                    date: true
                }
            },

            messages: {

                departamento: {
                    required: "Por favor, selecciona un departamento."
                },

                municipio: {
                    required: "Por favor, selecciona un municipio."
                },

                fecha: {
                    required: "Por favor, ingresa una fecha.",
                    date: "Por favor, ingresa un fecha válido."
                }
            }
        });

    }

    function inicializarEventos() {

        $("#formCompra").on("submit", function (e) {
            e.preventDefault();

            guardarCompra();
        });

        $("#departamento").on("change", function () {

            const departamentoId = $(this).val();

            cargarMunicipios(departamentoId);
        });
    }

    function cargarMunicipios(departamentoId) {

        const municipio = $("#municipio");

        municipio.empty();

        if (!departamentoId) {

            municipio
                .append(
                    $("<option>", {
                        value: "",
                        text: "=== Seleccione Municipio/Comuna ==="
                    })
                )
                .prop("disabled", true);

            return;
        }

        municipio
            .append(
                $("<option>", {
                    value: "",
                    text: "Cargando municipios..."
                })
            )
            .prop("disabled", true);

        $.ajax({
            url: rutasCompra.municipios,
            type: "GET",
            data: {
                departamento: departamentoId
            },
            dataType: "json",

            success: function (data) {

                municipio.empty();

                municipio.append(
                    $("<option>", {
                        value: "",
                        text: "Seleccione un municipio/comuna"
                    })
                );

                let municipios = data.data;

                if (typeof municipios === "string") {
                    municipios = JSON.parse(municipios);
                }

                municipios.forEach(function (item) {

                    municipio.append(
                        $("<option>", {
                            value: item.id,
                            text: item.nombre
                        })
                    );
                });

                municipio.prop("disabled", false);
            },

            error: function (xhr) {

                console.error(xhr);

                municipio.empty();

                municipio.append(
                    $("<option>", {
                        value: "",
                        text: "No se pudieron cargar los municipios"
                    })
                );

                municipio.prop("disabled", true);

                swal(
                    "Ocurrió un error",
                    "No se pudieron obtener los municipios.",
                    "error"
                );
            }
        });
    }

    function guardarCompra() {

        const form = $("#formCompra");

        if (!$("#formCompra").valid()) {
            return;
        }

        $(".loadFones").addClass("working");

        $.ajax({
            url: rutasCompra.guardar,
            type: "POST",
            data: form.serialize(),
            dataType: "json",

            success: function (data) {

                $(".loadFones").removeClass("working");

                if (data.code === 200) {

                    swal({
                        title: "Operación exitosa",
                        text: data.message,
                        type: "success"
                    }, function () {

                        window.location = comprasListadoUrl;

                    });

                    return;
                } else {
                    swal(
                        "Ocurrió un error",
                        data.message,
                        "error"
                    );
                }
            },

            error: function (xhr) {
                $(".loadFones").removeClass("working");

                let mensaje =
                    "Ocurrió un error al guardar la compra.";

                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {
                    mensaje = xhr.responseJSON.message;
                }

                swal(
                    "Ocurrió un error",
                    mensaje,
                    "error"
                );
            }
        });
    }

});