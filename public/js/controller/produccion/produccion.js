// Variable global para almacenar los registros y acceder a ellos desde el pop-up
let datosProduccion = [];
let dataTable = null;

$(document).ready(function() {
    // Inicializar DataTable
    const tableElement = document.querySelector("#datatablesSimple");
    if (tableElement) {
        dataTable = new simpleDatatables.DataTable(tableElement, {
            searchable: true,
            fixedHeight: false,
            paging: true,
            perPage: 25,
            labels: {
                placeholder: "Buscar...",
                perPage: "{select} registros por página",
                noRows: "No se encontraron registros",
                info: "Mostrando {start} a {end} de {rows} registros",
            }
        });
    }

    cargarListado();
});

function cargarListado() {
    $.ajax({
        url: baseUrlModulo + 'ajax/produccion/listado',
        type: 'GET',
        success: function(response) {
            if (response.code == 200) {
                datosProduccion = response.data; 
                let rows = [];

                $.each(datosProduccion, function(i, item) {
                    let fecha = item.fecha ? new Date(item.fecha).toLocaleDateString('es-AR', {timeZone: 'UTC'}) : '---';
                    
                    // Preparamos la fila para la tabla
                    let acciones = `<button class="btn btn-sm btn-info" onclick="verDetalle(${i})">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>`;
                    
                    rows.push([
                        fecha,
                        item.gas_producido || "0",
                        item.bruta || "0",
                        item.neta || "0",
                        acciones
                    ]);
                });

                // Si usas simple-datatables, la forma correcta de actualizar es:
                if (dataTable) {
                    dataTable.destroy(); // Destruimos la instancia vieja
                    $("#datatablesSimple tbody").html(""); // Limpiamos el HTML
                    
                    // Re-insertamos los datos
                    $.each(rows, function(i, row) {
                        let tr = `<tr>
                            <td>${row[0]}</td>
                            <td>${row[1]}</td>
                            <td>${row[2]}</td>
                            <td>${row[3]}</td>
                            <td>${row[4]}</td>
                        </tr>`;
                        $("#datatablesSimple tbody").append(tr);
                    });

                    // Re-inicializamos
                    dataTable = new simpleDatatables.DataTable("#datatablesSimple", {
                        fixedHeight: false,
                        perPage: 25
                    });
                }
            }
        }
    });
}

function verDetalle(index) {
    const p = datosProduccion[index];
    
    // HTML estructurado para el detalle completo
    let detalleHtml = `
        <div class="container-fluid" style="text-align: left; font-size: 0.9rem;">
            <div class="row border-bottom pb-2 mb-2">
                <div class="col-12">
                    <h5 class="text-primary">Resumen de Gas</h5>
                </div>
                <div class="col-6"><strong>Producido:</strong> ${p.gas_producido} m³</div>
                <div class="col-6"><strong>Venteado:</strong> ${p.gas_venteado} m³</div>
                <div class="col-6"><strong>Inyectado:</strong> ${p.gas_inyectado} m³</div>
                <div class="col-6"><strong>9300 Kcal:</strong> ${p.gas_9300_kcal} m³</div>
                <div class="col-6"><strong>Petróleo Despachado:</strong> ${p.petroleo_despachado} m³</div>
            </div>
            <div class="row border-bottom pb-2 mb-2">
                <div class="col-12">
                    <h5 class="text-success">Líquidos y Stock</h5>
                </div>
                <div class="col-6"><strong>Bruta:</strong> ${p.bruta} m³</div>
                <div class="col-6"><strong>Neta:</strong> ${p.neta} m³</div>
                <div class="col-6"><strong>Agua Prod:</strong> ${p.agua_producida} m³</div>
                <div class="col-6"><strong>Stock Actual:</strong> ${p.stock_actual} m³</div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h5 class="text-warning">Servicios</h5>
                </div>
                <div class="col-6"><strong>Horas Gen:</strong> ${p.horas_escio_generador || 0}</div>
                <div class="col-6"><strong>Horas LTS:</strong> ${p.horas_escio_lts || 0}</div>
            </div>
        </div>
    `;

    Swal.fire({
        title: `Producción del ${new Date(p.fecha).toLocaleDateString('es-AR', {timeZone: 'UTC'})}`,
        html: detalleHtml,
        width: '650px',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#0d6efd'
    });
}