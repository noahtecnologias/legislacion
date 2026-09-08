$(document).ready(function() { 
    // Variable global para almacenar la sumatoria teórica original de gas (E18 en tu Excel) 
    let totalGasTeoricoOriginal = 0; 

    init(); 

    function init() { 
        procesarYRenderizarTablas(); 
         
        // Listener interactivo para cambios en Downtime o Valores de Control de Petróleo 
        $(document).on('input', '.input-downtime, .input-control', function() { 
            let $row = $(this).closest('tr'); 
            let $table = $(this).closest('table'); 
             
            calcularFila($row); 
            calcularTotalesTabla($table); 

            // COMPORTAMIENTO ESPEJO MAESTRO 
            if ($table.attr('id') === 'tabla-petroleo') { 
                let pozoNum = $row.data('pozo'); 
                let nuevoDowntime = $row.find('.input-downtime').val() || 0; 
                 
                // 1. Espejar a la tabla de AGUA 
                let $filaAgua = $(`#tabla-agua tbody tr[data-pozo="${pozoNum}"]`); 
                if ($filaAgua.length > 0) { 
                    $filaAgua.find('.col-downtime-espejo').text(parseFloat(nuevoDowntime)); 
                    calcularFilaEspejo($filaAgua); 
                    calcularTotalesTabla($('#tabla-agua')); 
                } 

                // 2. Espejar a la tabla de GAS y recalcular en base a su GOR individual 
                let $filaGas = $(`#tabla-gas tbody tr[data-pozo="${pozoNum}"]`); 
                if ($filaGas.length > 0) { 
                    $filaGas.find('.col-downtime-gas').text(parseFloat(nuevoDowntime)); 
                    calcularFilaGas($filaGas); 
                    calcularTotalesGas(); 
                } 
            } 
        }); 

        // Listener reactivo para el cambio de opción en el Select de GOR de cada pozo 
        $(document).on('change', '.select-gor', function() { 
            let $row = $(this).closest('tr'); 
            let $inputCustom = $row.find('.input-gor-custom'); 
             
            if ($(this).val() === 'custom') { 
                $inputCustom.removeClass('d-none').focus(); 
            } else { 
                $inputCustom.addClass('d-none').val(''); 
                calcularFilaGas($row); 
                calcularTotalesGas(); 
            } 
        }); 

        // Listener reactivo si escriben un GOR personalizado 
        $(document).on('input', '.input-gor-custom', function() { 
            let $row = $(this).closest('tr'); 
            calcularFilaGas($row); 
            calcularTotalesGas(); 
        }); 

        // DETECTAR CAMBIO EN EL TOTAL GLOBAL DE GAS (BALANCEO MANUAL) 
        $(document).on('input change', '#input-total-gas-global', function() { 
            let nuevoTotalForzado = parseFloat($(this).val()); 
             
            if (isNaN(nuevoTotalForzado) || totalGasTeoricoOriginal === 0) return; 

            // Recorremos cada pozo de gas para aplicar el balanceo proporcional 
            $('#tabla-gas tbody tr').each(function() { 
                let $row = $(this); 
                 
                // Calculamos el valor teórico individual que le correspondía originalmente (E6, E7, etc.) 
                let potenciaGas = parseFloat($row.find('.col-potencial-gas').text()) || 0; 
                let dt = parseFloat($row.find('.col-downtime-gas').text()) || 0; 
                let totalGasFilaTeorico = potenciaGas * ((24 - dt) / 24); 

                // Fórmula del Excel corregida con el divisor de 1000 para mantener la escala Mm3 
                let totalGasBalanceado = (totalGasFilaTeorico * nuevoTotalForzado) / (totalGasTeoricoOriginal * 1000); 

                // Reemplazamos el valor en la columna visible de la tabla 
                let $celdaTotal = $row.find('.col-total-gas'); 
                $celdaTotal.text(totalGasBalanceado.toFixed(2)); 

                // Cambio visual opcional para denotar que el dato está afectado por el balanceo 
                if (nuevoTotalForzado !== totalGasTeoricoOriginal) { 
                    $celdaTotal.removeClass('text-warning').addClass('text-danger fw-bold'); 
                } else { 
                    $celdaTotal.removeClass('text-danger fw-bold').addClass('text-warning'); 
                } 
            }); 

            // Si el valor vuelve a ser el original, le quitamos el color de alerta al input 
            if (nuevoTotalForzado !== totalGasTeoricoOriginal) { 
                $(this).removeClass('text-warning').addClass('text-danger'); 
            } else { 
                $(this).removeClass('text-danger').addClass('text-warning'); 
            } 
        }); 
    } 

    function procesarYRenderizarTablas() { 
        const agrupar = { petroleo: [], agua: [], gas: [] }; 

        // Clasificación inicial sin acentos 
        potencialesBaseAdmin.forEach(p => { 
            let t = p.tipo.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); 
            if (agrupar[t]) agrupar[t].push(p); 
        }); 

        // Renderizado de Petróleo y Agua 
        Object.keys(agrupar).forEach(tipo => { 
            if (tipo === 'gas') return;  

            const lista = agrupar[tipo]; 
            const $tbody = $(`#tabla-${tipo} tbody`).empty(); 
            let totalNetaGrupo = lista.reduce((sum, p) => sum + parseFloat(p.potencial_neta || 0), 0); 

            lista.forEach(p => { 
                let neta = parseFloat(p.potencial_neta || 0); 
                let porcentajeParticipacion = totalNetaGrupo > 0 ? (neta / totalNetaGrupo) * 100 : 0; 

                let celdaDowntime = (tipo === 'petroleo')  
                    ? `<td>
                        <input type="number" step="0.1" min="0" max="24" 
                               class="form-control form-control-sm text-end input-downtime font-monospace fw-bold" 
                               value="0">
                       </td>` 
                    : `<td class="text-end font-monospace fw-bold col-downtime-espejo bg-light text-muted">0</td>`; 

                let row = ` 
                    <tr data-id="${p.id}" data-pozo="${p.pozo}" data-neta="${neta}"> 
                        <td class="fw-bold">${p.pozo}</td> 
                        <td class="text-end bg-light font-monospace">${neta.toFixed(3)}</td> 
                        <td class="text-end text-muted font-monospace col-porcentaje" data-part="${porcentajeParticipacion}">
                            ${porcentajeParticipacion.toFixed(2)} %
                        </td> 
                        <td>
                            <input type="number" step="0.001" class="form-control form-control-sm text-end input-control font-monospace" placeholder="0.000">
                        </td> 
                        <td>
                            <input type="number" step="0.01" class="form-control form-control-sm text-end input-porcentaje-control font-monospace" placeholder="0.00">
                        </td> 
                        ${celdaDowntime} 
                        <td class="text-end font-monospace fw-bold col-real bg-light text-success">${neta.toFixed(3)}</td> 
                        <td class="text-end font-monospace text-danger col-perdida">0.000</td> 
                        <td class="text-end font-monospace col-diferc bg-light text-dark">24.00</td> 
                    </tr> 
                `; 
                $tbody.append(row); 
            }); 

            calcularTotalesTabla($(`#tabla-${tipo}`)); 
        }); 

        // Generar la tabla de Gas con el GOR individualizado 
        renderizarTablaGas(); 
    } 

    function renderizarTablaGas() { 
        const $tbody = $('#tabla-gas tbody').empty(); 

        // Armamos el cuerpo de la tabla mapeando los pozos existentes en Petróleo 
        $('#tabla-petroleo tbody tr').each(function() { 
            let idBase = $(this).data('id');  
            let pozoNum = $(this).data('pozo'); 
            let downtimePetroleo = parseFloat($(this).find('.input-downtime').val()) || 0; 

            let row = ` 
                <tr data-id="${idBase}" data-pozo="${pozoNum}"> 
                    <td class="fw-bold">${pozoNum}</td> 
                    <td> 
                        <div class="d-flex gap-1 align-items-center"> 
                            <select class="form-select form-select-sm select-gor fw-bold" style="width: 90px;"> 
                                <option value="280" selected>280</option> 
                                <option value="300">300</option> 
                                <option value="custom">Otro...</option> 
                            </select> 
                            <input type="number" class="form-control form-control-sm input-gor-custom d-none text-end font-monospace" placeholder="Valor" style="width: 75px;" min="0"> 
                        </div> 
                    </td> 
                    <td class="text-end font-monospace bg-light col-potencial-gas">0.00</td> 
                    <td class="text-end font-monospace col-downtime-gas bg-light text-muted">${downtimePetroleo}</td> 
                    <td class="text-end font-monospace fw-bold col-total-gas text-warning bg-dark">0.00</td> 
                </tr> 
            `; 
            $tbody.append(row); 
            calcularFilaGas($tbody.find('tr').last()); 
        }); 

        calcularTotalesGas(); 
    } 

    // Retorna el valor real del GOR de una fila analizando el estado de sus controles 
    function obtenerGorFila($row) { 
        let selectVal = $row.find('.select-gor').val(); 
        if (selectVal === 'custom') { 
            return parseFloat($row.find('.input-gor-custom').val()) || 0; 
        } 
        return parseFloat(selectVal) || 0; 
    } 

    function calcularFila($row) { 
        const neta = parseFloat($row.data('neta')) || 0; 
        let downtime = parseFloat($row.find('.input-downtime').val()); 

        if (isNaN(downtime) || downtime < 0) downtime = 0; 
        if (downtime > 24) downtime = 24; 
        $row.find('.input-downtime').val(downtime); 

        let realProd = neta * (1 - (downtime / 24)); 
        let perdida = neta - realProd; 
        let difercHs = 24 - downtime; 

        $row.find('.col-real').text(realProd.toFixed(3)); 
        $row.find('.col-perdida').text(perdida.toFixed(3)); 
        $row.find('.col-diferc').text(difercHs.toFixed(2)); 

        if (downtime === 24) { 
            $row.find('.col-real').removeClass('text-success').addClass('text-muted'); 
            $row.addClass('table-danger'); 
        } else { 
            $row.find('.col-real').removeClass('text-muted').addClass('text-success'); 
            $row.removeClass('table-danger'); 
        } 
    } 

    function calcularFilaEspejo($row) { 
        const neta = parseFloat($row.data('neta')) || 0; 
        let downtime = parseFloat($row.find('.col-downtime-espejo').text()) || 0; 

        let realProd = neta * (1 - (downtime / 24)); 
        let perdida = neta - realProd; 
        let difercHs = 24 - downtime; 

        $row.find('.col-real').text(realProd.toFixed(3)); 
        $row.find('.col-perdida').text(perdida.toFixed(3)); 
        $row.find('.col-diferc').text(difercHs.toFixed(2)); 

        if (downtime === 24) { 
            $row.find('.col-real').removeClass('text-success').addClass('text-muted'); 
            $row.addClass('table-danger'); 
        } else { 
            $row.find('.col-real').removeClass('text-muted').addClass('text-success'); 
            $row.removeClass('table-danger'); 
        } 
    } 

    function calcularFilaGas($row) { 
        let pozoNum = $row.data('pozo'); 
        let $filaPetroleo = $(`#tabla-petroleo tbody tr[data-pozo="${pozoNum}"]`); 
        let netaPetroleo = parseFloat($filaPetroleo.data('neta')) || 0; 

        const gor = obtenerGorFila($row); 
        let potenciaGas = netaPetroleo * gor; 
         
        $row.find('.col-potencial-gas').text(potenciaGas.toFixed(2)); 

        const dt = parseFloat($row.find('.col-downtime-gas').text()) || 0; 
        let totalGas = potenciaGas * ((24 - dt) / 24); 
        $row.find('.col-total-gas').text(totalGas.toFixed(2)).removeClass('text-danger').addClass('text-warning'); 

        if (dt === 24) $row.addClass('table-danger'); 
        else $row.removeClass('table-danger'); 
    } 

    function calcularTotalesTabla($table) { 
        let totalNeta = 0, totalPart = 0, totalReal = 0, totalPerdida = 0; 

        $table.find('tbody tr').each(function() { 
            totalNeta += parseFloat($(this).data('neta')) || 0; 
            totalPart += parseFloat($(this).find('.col-porcentaje').data('part')) || 0; 
            totalReal += parseFloat($(this).find('.col-real').text()) || 0; 
            totalPerdida += parseFloat($(this).find('.col-perdida').text()) || 0; 
        }); 

        const $tfoot = $table.find('tfoot').empty(); 
        let footerRow = ` 
            <tr> 
                <td><strong>TOTAL</strong></td> 
                <td class="text-end font-monospace font-weight-bold">${totalNeta.toFixed(3)}</td> 
                <td class="text-end font-monospace">${Math.round(totalPart)} %</td> 
                <td></td><td></td><td></td> 
                <td class="text-end font-monospace font-weight-bold text-success">${totalReal.toFixed(3)}</td> 
                <td class="text-end font-monospace font-weight-bold text-danger">${totalPerdida.toFixed(3)}</td> 
                <td></td> 
            </tr> 
        `; 
        $tfoot.append(footerRow); 
    } 

    function calcularTotalesGas() { 
        let totalPotencialGas = 0, totalGasAcumulado = 0; 

        $('#tabla-gas tbody tr').each(function() { 
            let $row = $(this); 
            let potenciaGas = parseFloat($row.find('.col-potencial-gas').text()) || 0; 
            let dt = parseFloat($row.find('.col-downtime-gas').text()) || 0; 
            let totalGasFilaTeorico = potenciaGas * ((24 - dt) / 24); 

            totalPotencialGas += potenciaGas; 
            totalGasAcumulado += totalGasFilaTeorico; 

            // Restablecemos visualmente el valor a su estado calculado original por si venía de un balanceo previo 
            $row.find('.col-total-gas').text(totalGasFilaTeorico.toFixed(2)).removeClass('text-danger').addClass('text-warning'); 
        }); 

        // Guardamos el total acumulado teórico en la variable global para el prorrateo posterior 
        totalGasTeoricoOriginal = totalGasAcumulado; 

        const $tfoot = $('#tabla-gas tfoot').empty(); 
        let footerRow = ` 
            <tr> 
                <td><strong>TOTAL</strong></td> 
                <td></td> 
                <td class="text-end font-monospace font-weight-bold">${totalPotencialGas.toFixed(2)}</td> 
                <td></td> 
                <td class="bg-dark p-1 text-end"> 
                    <input type="number" step="0.01" id="input-total-gas-global"  
                           class="form-control form-control-sm text-end font-monospace fw-bold text-warning bg-dark border-0"  
                           value="${totalGasAcumulado.toFixed(2)}" style="width: 120px; display: inline-block;"> 
                </td> 
            </tr> 
        `; 
        $tfoot.append(footerRow); 
    } 

    // ACCIÓN GLOBAL: Guardar lote completo 
    $('#btnGuardarDia').click(function() { 
        const fecha = $('#fechaParte').val(); 
        if (!fecha) { 
            swal("Atención", "Seleccioná una fecha válida.", "warning"); 
            return; 
        } 

        const detalles = []; 

        // 1. PETRÓLEO 
        $('#tabla-petroleo tbody tr').each(function() { 
            detalles.push({ 
                potencial_base_id: $(this).data('id'), 
                downtime: $(this).find('.input-downtime').val() || "0", 
                valor_control: $(this).find('.input-control').val() || "", 
                porcentaje_control: $(this).find('.input-porcentaje-control').val() || "", 
                gor: null, 
                tipo: 'petroleo' 
            }); 
        }); 

        // 2. AGUA 
        $('#tabla-agua tbody tr').each(function() { 
            detalles.push({ 
                potencial_base_id: $(this).data('id'), 
                downtime: $(this).find('.col-downtime-espejo').text() || "0", 
                valor_control: $(this).find('.input-control').val() || "", 
                porcentaje_control: $(this).find('.input-porcentaje-control').val() || "", 
                gor: null, 
                tipo: 'agua' 
            }); 
        }); 

        // 3. GAS (Se extrae el GOR particular calculado para cada fila) 
        $('#tabla-gas tbody tr').each(function() {

            let gorParticular = obtenerGorFila($(this));
        
            let totalGasMostrado =
                $(this).find('.col-total-gas').text() || "0";
        
            detalles.push({
                potencial_base_id: $(this).data('id'),
                downtime: $(this).find('.col-downtime-gas').text() || "0",
                valor_control: totalGasMostrado,
                porcentaje_control: null,
                gor: gorParticular.toString(),
                tipo: 'gas'
            });
        });

        $(".loadFones").addClass("working"); 

        $.ajax({ 
            url: baseUrlByRol + 'ajax/potencialDiario/guardar', 
            type: 'POST', 
            contentType: 'application/json', 
            data: JSON.stringify({ fecha: fecha, detalles: detalles }), 
            success: function(response) { 
                $(".loadFones").removeClass("working"); 
                if (response.code === 200 || response.status === 'success') { 
                    swal("¡Éxito!", "El parte diario se consolidó de manera exitosa.", "success"); 
                } else { 
                    swal("Error", "Error al procesar la carga.", "error"); 
                } 
            }, 
            error: function(xhr) { 
                $(".loadFones").removeClass("working"); 
                swal("Error Crítico", "No se pudo guardar el lote diario.", "error"); 
            } 
        }); 
    }); 
});