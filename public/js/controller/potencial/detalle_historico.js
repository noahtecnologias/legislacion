$(document).ready(function() {
    renderizarDetalle();

    function renderizarDetalle() {
        if (!registrosHistoricos || registrosHistoricos.length === 0) return;

        const listasPorTipo = { petroleo: [], agua: [], gas: [] };

        // 1. Clasificación basada en el tipo del registro diario
        registrosHistoricos.forEach(r => {
            let tipoOrigen = r.tipo ? r.tipo : (r.potencial_base ? r.potencial_base.tipo : '');
            if (!tipoOrigen) return;

            let t = tipoOrigen.toLowerCase().trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            
            if (listasPorTipo[t]) {
                listasPorTipo[t].push(r);
            }
        });

        // 2. Renderizado de las tablas convencionales (Petróleo y Agua)
        ['petroleo', 'agua'].forEach(tipo => {
            const registros = listasPorTipo[tipo];
            const $tbody = $(`#tabla-${tipo} tbody`).empty();

            if (!registros || registros.length === 0) return;

            let totalNetaGrupo = registros.reduce((sum, r) => sum + parseFloat(r.potencial_base ? r.potencial_base.potencial_neta : 0), 0);

            registros.forEach(r => {
                let pBase = r.potencial_base;
                if (!pBase) return;

                let neta = parseFloat(pBase.potencial_neta || 0);
                let part = totalNetaGrupo > 0 ? (neta / totalNetaGrupo) * 100 : 0;
                let dt = parseFloat(r.downtime || 0);
                
                let realProd = parseFloat(r.real_prod || 0);
                let perdida = parseFloat(r.perdida || 0);
                let difercHs = parseFloat(r.diferc_hs || 0);

                let row = `
                    <tr class="${dt === 24 ? 'table-danger' : ''}">
                        <td class="fw-bold">${pBase.pozo}</td>
                        <td class="text-end bg-light font-monospace">${neta.toFixed(3)}</td>
                        <td class="text-end text-muted font-monospace">${part.toFixed(2)} %</td>
                        <td class="text-end font-monospace bg-white">${r.valor_control ? parseFloat(r.valor_control).toFixed(3) : '0.000'}</td>
                        <td class="text-end font-monospace bg-white">${r.porcentaje_control ? parseFloat(r.porcentaje_control).toFixed(2) + ' %' : '0.00 %'}</td>
                        <td class="text-end font-monospace fw-bold ${dt === 24 ? 'text-danger' : ''}">${dt.toFixed(1)}</td>
                        <td class="text-end font-monospace fw-bold bg-light ${dt === 24 ? 'text-muted' : 'text-success'}">${realProd.toFixed(3)}</td>
                        <td class="text-end font-monospace text-danger">${perdida.toFixed(3)}</td>
                        <td class="text-end font-monospace bg-light text-dark">${difercHs.toFixed(2)}</td>
                    </tr>
                `;
                $tbody.append(row);
            });

            calcularTotalesTablaEstandar($(`#tabla-${tipo}`));
        });

                // 3. Renderizado de la tabla de Gas
            const registrosGas = listasPorTipo['gas'];
            const $tbodyGas = $('#tabla-gas tbody').empty();

            if (registrosGas && registrosGas.length > 0) {

                registrosGas.forEach(r => {

                    let pBase = r.potencial_base;
                    if (!pBase) return;

                    let netaPetroleo = parseFloat(pBase.potencial_neta || 0);
                    let factorGOR = parseFloat(r.gor || 0);
                    let dt = parseFloat(r.downtime || 0);

                    // Potencial Gas reconstruido
                    let potGasCalculado = netaPetroleo * factorGOR;

                    // ==========================
                    // NUEVA LÓGICA
                    // ==========================
                    // Si existe valor_control usamos el balanceado.
                    // Si no existe usamos el valor teórico almacenado en real_prod.
                    let totalGasMostrar;
                    let fueBalanceado = false;

                    if (
                        r.valor_control !== null &&
                        r.valor_control !== '' &&
                        !isNaN(parseFloat(r.valor_control))
                    ) {
                        totalGasMostrar = parseFloat(r.valor_control);
                        fueBalanceado = true;
                    } else {
                        totalGasMostrar = parseFloat(r.real_prod || 0);
                    }

                    let claseGas = fueBalanceado
                        ? 'text-info fw-bold'
                        : 'text-warning';

                    let row = `
                        <tr class="${dt === 24 ? 'table-danger' : ''}">
                            <td class="fw-bold">${pBase.pozo}</td>

                            <td class="text-center font-monospace fw-bold text-secondary bg-white">
                                ${factorGOR.toFixed(0)}
                            </td>

                            <td class="text-end font-monospace bg-light">
                                ${potGasCalculado.toFixed(2)}
                            </td>

                            <td class="text-end font-monospace bg-light text-muted">
                                ${dt.toFixed(1)}
                            </td>

                            <td class="text-end font-monospace ${claseGas} bg-dark">
                                ${totalGasMostrar.toFixed(2)}
                            </td>
                        </tr>
                    `;

                    $tbodyGas.append(row);
                });
            }

            calcularTotalesTablaGas();
    }

    function calcularTotalesTablaEstandar($table) {
        let totalNeta = 0, totalReal = 0, totalPerdida = 0;

        $table.find('tbody tr').each(function() {
            totalNeta += parseFloat($(this).find('td:nth-child(2)').text()) || 0;
            totalReal += parseFloat($(this).find('td:nth-child(7)').text()) || 0;
            totalPerdida += parseFloat($(this).find('td:nth-child(8)').text()) || 0;
        });

        const $tfoot = $table.find('tfoot').empty();
        $tfoot.append(`
            <tr>
                <td><strong>TOTAL</strong></td>
                <td class="text-end font-monospace font-weight-bold">${totalNeta.toFixed(3)}</td>
                <td class="text-end font-monospace">100 %</td>
                <td></td><td></td><td></td>
                <td class="text-end font-monospace font-weight-bold text-success">${totalReal.toFixed(3)}</td>
                <td class="text-end font-monospace font-weight-bold text-danger">${totalPerdida.toFixed(3)}</td>
                <td></td>
            </tr>
        `);
    }

    function calcularTotalesTablaGas() {
        let totalPotencialGas = 0, totalGasAcumulado = 0;

        $('#tabla-gas tbody tr').each(function() {
            // td:nth-child(3) -> Lee Potencial Gas | td:nth-child(5) -> Lee Total Gas
            totalPotencialGas += parseFloat($(this).find('td:nth-child(3)').text()) || 0;
            totalGasAcumulado += parseFloat($(this).find('td:nth-child(5)').text()) || 0;
        });

        const $tfoot = $('#tabla-gas tfoot').empty();
        $tfoot.append(`
            <tr>
                <td><strong>TOTAL</strong></td>
                <td></td> <td class="text-end font-monospace font-weight-bold">${totalPotencialGas.toFixed(2)}</td>
                <td></td>
                <td class="text-end font-monospace font-weight-bold text-warning bg-dark">${totalGasAcumulado.toFixed(2)}</td>
            </tr>
        `);
    }
});