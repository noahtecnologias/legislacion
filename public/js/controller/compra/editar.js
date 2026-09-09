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

        cargarMunicipios(
            $("#departamento").val(),
            compraMunicipioId
        );
    }

    function inicializarEventos() {

        $("#formCompra").on("submit", function (e) {

            e.preventDefault();

            actualizarCompra();
        });

        $("#departamento").on("change", function () {

            const departamentoId = $(this).val();

            cargarMunicipios(departamentoId, null);
        });
    }

    function cargarMunicipios(departamentoId, municipioSeleccionadoId) {

        const municipio = $("#municipio");

        municipio.empty();

        if (!departamentoId) {

            municipio
                .append(
                    $("<option>", {
                        value: "",
                        text: "Seleccione primero un departamento"
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

                    const option = $("<option>", {
                        value: item.id,
                        text: item.nombre
                    });

                    if (
                        municipioSeleccionadoId &&
                        String(item.id) === String(municipioSeleccionadoId)
                    ) {
                        option.prop("selected", true);
                    }

                    municipio.append(option);
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

    function actualizarCompra() {

        const form = $("#formCompra");

        if (!$("#formCompra").valid()) {
            return;
        }

        $(".loadFones").addClass("working");

        $.ajax({

            url: rutasCompra.actualizar,

            type: "PUT",

            data: form.serialize(),

            dataType: "json",

            success: function (data) {

                $(".loadFones").removeClass("working");

                if (data.status === "success") {

                    swal({
                        title: "Operación exitosa",
                        text: data.message,
                        type: "success"
                    }, function () {

                        window.location = rutasCompra.listado;

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
                    "Ocurrió un error al actualizar la compra.";

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