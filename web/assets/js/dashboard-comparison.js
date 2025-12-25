/**
 * Dashboard Comparison - Comparación Año Actual vs Año Anterior
 * Compara ventas de ayer vs mismo día año pasado por sucursal
 */

// Helper para formatear fecha bonita (DD/MM/YYYY)
function formatDateNice(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

async function loadYearComparison() {
    console.log('📊 Cargando comparación año a año...');
    
    // ============================================
    // LÓGICA: Comparar AYER vs MISMO DÍA DE LA SEMANA del año pasado
    // Ejemplo: Si ayer fue Lunes 20 Oct 2025, buscar el Lunes más cercano de Oct 2024
    // ============================================
    
    // AYER (restar 1 día a HOY)
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = formatDateForAPI(yesterday);
    const dayOfWeekYesterday = yesterday.getDay(); // 0=Domingo, 1=Lunes, ..., 6=Sábado
    
    // Ir exactamente 1 año atrás desde AYER
    const sameDate365DaysAgo = new Date(yesterday);
    sameDate365DaysAgo.setFullYear(yesterday.getFullYear() - 1);
    
    // Ver qué día de la semana era hace 1 año en esa fecha
    const dayOfWeek365DaysAgo = sameDate365DaysAgo.getDay();
    
    // Calcular diferencia de días para ajustar al MISMO día de la semana
    let daysDiff = dayOfWeekYesterday - dayOfWeek365DaysAgo;
    
    // Ajustar la fecha del año pasado para que sea el mismo día de la semana
    const sameDayOfWeekLastYear = new Date(sameDate365DaysAgo);
    sameDayOfWeekLastYear.setDate(sameDate365DaysAgo.getDate() + daysDiff);
    
    const sameDayOfWeekLastYearStr = formatDateForAPI(sameDayOfWeekLastYear);
    
    // Nombres de días para logs
    const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    
    console.log(`\n📅 ========== COMPARACIÓN INTELIGENTE (AYER) ==========`);
    console.log(`🟢 AYER:         ${yesterdayStr} (${dayNames[dayOfWeekYesterday]})`);
    console.log(`🔵 AÑO PASADO:   ${sameDayOfWeekLastYearStr} (${dayNames[dayOfWeekYesterday]}) ✅`);
    console.log(`⚠️  Si comparamos la misma fecha: ${formatDateForAPI(sameDate365DaysAgo)} era (${dayNames[dayOfWeek365DaysAgo]}) ❌`);
    console.log(`✅ Ajuste: ${daysDiff > 0 ? '+' : ''}${daysDiff} días para que sea el mismo día de la semana`);
    console.log(`======================================================\n`);
    
    try {
        // Obtener datos de AYER (2025)
        const data2025 = await fetchCountersData(yesterdayStr, yesterdayStr + ' 23:59:59');
        
        // Obtener datos del MISMO DÍA DE LA SEMANA del año pasado (2024)
        const data2024 = await fetchCountersData(sameDayOfWeekLastYearStr, sameDayOfWeekLastYearStr + ' 23:59:59');
        
        // Renderizar tabla comparativa
        renderComparisonTable(data2025, data2024, yesterdayStr, sameDayOfWeekLastYearStr);
        
        // Calcular totales globales
        const totals2025 = calculateTotals(data2025);
        const totals2024 = calculateTotals(data2024);
        
        // Renderizar KPIs globales
        renderGlobalComparisonKPIs(totals2025, totals2024, yesterdayStr, sameDayOfWeekLastYearStr);
        
    } catch (error) {
        console.error('❌ Error al cargar comparación:', error);
    }
}

function formatDateForAPI(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

async function fetchCountersData(startDate, endDate) {
    return new Promise((resolve, reject) => {
        __conection({
            url: generarURLApi(`/getAppCounters?startDate=${startDate}&endDate=${endDate}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (response) {
            console.log(`✅ Datos obtenidos para ${startDate}:`, response);
            resolve(response);
        }, function(error) {
            console.error(`❌ Error obteniendo datos para ${startDate}:`, error);
            reject(error);
        });
    });
}

function calculateTotals(data) {
    let totalVentas = 0;
    let totalTickets = 0;
    let totalDelivery = 0;
    
    for (const app in data) {
        const counters = data[app].original.counters;
        totalVentas += parseFloat(counters.balanceTotal) || 0;
        totalTickets += parseInt(counters.orders) || 0;
        
        const uber = parseFloat(counters.uber) || 0;
        const rappi = parseFloat(counters.rappi) || 0;
        const pedidosYa = parseFloat(counters.pedidos_ya) || 0;
        totalDelivery += uber + rappi + pedidosYa;
    }
    
    return {
        ventas: totalVentas,
        tickets: totalTickets,
        delivery: totalDelivery
    };
}

function renderGlobalComparisonKPIs(totals2025, totals2024, date2025, date2024) {
    const diferencia = totals2025.ventas - totals2024.ventas;
    const porcentaje = totals2024.ventas > 0 ? ((diferencia / totals2024.ventas) * 100) : 0;
    const isPositive = diferencia >= 0;
    
    // Actualizar KPIs
    const kpiCurrentYear = document.getElementById('kpi-current-year');
    const kpiPreviousYear = document.getElementById('kpi-previous-year');
    const kpiDifferenceAmount = document.getElementById('kpi-difference-amount');
    const kpiDifferencePercent = document.getElementById('kpi-difference-percent');
    const comparisonTrend = document.getElementById('comparison-trend');
    const comparisonCurrentDate = document.getElementById('comparison-current-date');
    const comparisonPreviousDate = document.getElementById('comparison-previous-date');
    
    if (kpiCurrentYear) kpiCurrentYear.textContent = formatearMontoChile(totals2025.ventas);
    if (kpiPreviousYear) kpiPreviousYear.textContent = formatearMontoChile(totals2024.ventas);
    if (kpiDifferenceAmount) kpiDifferenceAmount.textContent = formatearMontoChile(Math.abs(diferencia));
    if (kpiDifferencePercent) {
        kpiDifferencePercent.textContent = `${isPositive ? '+' : '-'}${Math.abs(porcentaje).toFixed(1)}%`;
        kpiDifferencePercent.style.color = isPositive ? '#10b981' : '#ef4444';
    }
    
    // Actualizar trend icon
    if (comparisonTrend) {
        const icon = comparisonTrend.querySelector('i');
        if (icon) {
            icon.className = isPositive ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
        }
        comparisonTrend.style.color = isPositive ? '#10b981' : '#ef4444';
    }
    
    // Actualizar fechas con día de la semana
    const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const date2025Obj = new Date(date2025);
    const date2024Obj = new Date(date2024);
    const dayName2025 = dayNames[date2025Obj.getDay()];
    const dayName2024 = dayNames[date2024Obj.getDay()];
    
    if (comparisonCurrentDate) comparisonCurrentDate.textContent = `AYER ${dayName2025} (${formatDateNice(date2025)})`;
    if (comparisonPreviousDate) comparisonPreviousDate.textContent = `${dayName2024} 2024 (${formatDateNice(date2024)})`;
    
    console.log('📊 KPIs Globales actualizados:', { totals2025, totals2024, diferencia, porcentaje });
}

function renderComparisonTable(data2025, data2024, date2025, date2024) {
    const tbody = document.getElementById('tabla-comparacion-tbody');
    tbody.innerHTML = '';
    
    // Actualizar headers con fechas reales y día de la semana
    const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const date2025Obj = new Date(date2025);
    const date2024Obj = new Date(date2024);
    const dayName2025 = dayNames[date2025Obj.getDay()];
    const dayName2024 = dayNames[date2024Obj.getDay()];
    
    const header2025 = document.getElementById('header-fecha-2025');
    const header2024 = document.getElementById('header-fecha-2024');
    if (header2025) header2025.textContent = `AYER ${dayName2025} (${formatDateNice(date2025)})`;
    if (header2024) header2024.textContent = `${dayName2024} 2024 (${formatDateNice(date2024)})`;
    
    let totalVentas2025 = 0;
    let totalVentas2024 = 0;
    let totalTickets2025 = 0;
    let totalTickets2024 = 0;
    
    // Crear un mapa con todas las sucursales
    const sucursales = new Set();
    for (const app in data2025) {
        const appName = app.split(',')[1] || app;
        sucursales.add(appName);
    }
    for (const app in data2024) {
        const appName = app.split(',')[1] || app;
        sucursales.add(appName);
    }
    
    // Ordenar sucursales alfabéticamente
    const sucursalesArray = Array.from(sucursales).sort();
    
    sucursalesArray.forEach(sucursalName => {
        // Buscar datos de 2025
        let ventas2025 = 0;
        let tickets2025 = 0;
        for (const app in data2025) {
            const appName = app.split(',')[1] || app;
            if (appName === sucursalName) {
                const counters = data2025[app].original.counters;
                ventas2025 = parseFloat(counters.balanceTotal) || 0;
                tickets2025 = parseInt(counters.orders) || 0;
                break;
            }
        }
        
        // Buscar datos de 2024
        let ventas2024 = 0;
        let tickets2024 = 0;
        for (const app in data2024) {
            const appName = app.split(',')[1] || app;
            if (appName === sucursalName) {
                const counters = data2024[app].original.counters;
                ventas2024 = parseFloat(counters.balanceTotal) || 0;
                tickets2024 = parseInt(counters.orders) || 0;
                break;
            }
        }
        
        // Calcular diferencias
        const difVentas = ventas2025 - ventas2024;
        const difPorcentaje = ventas2024 > 0 ? ((difVentas / ventas2024) * 100) : 0;
        const isPositive = difVentas >= 0;
        
        // Calcular ticket promedio
        const ticketPromedio2025 = tickets2025 > 0 ? (ventas2025 / tickets2025) : 0;
        const ticketPromedio2024 = tickets2024 > 0 ? (ventas2024 / tickets2024) : 0;
        
        // Acumular totales
        totalVentas2025 += ventas2025;
        totalVentas2024 += ventas2024;
        totalTickets2025 += tickets2025;
        totalTickets2024 += tickets2024;
        
        // Renderizar fila
        const colorClass = isPositive ? 'text-success' : 'text-danger';
        const arrow = isPositive ? '↗️' : '↘️';
        
        const fila = `<tr>
            <td><strong>${sucursalName}</strong></td>
            <td style="color: #10b981; font-weight: bold;">${formatearMontoChile(ventas2025)}</td>
            <td><span class="badge bg-info" style="font-size: 0.9rem;">${tickets2025}</span></td>
            <td style="color: #059669; font-size: 0.85rem;">${formatearMontoChile(ticketPromedio2025)}</td>
            <td style="color: #f59e0b; font-weight: bold;">${formatearMontoChile(ventas2024)}</td>
            <td><span class="badge bg-secondary" style="font-size: 0.9rem;">${tickets2024}</span></td>
            <td style="color: #d97706; font-size: 0.85rem;">${formatearMontoChile(ticketPromedio2024)}</td>
            <td class="${colorClass}">
                <strong>${arrow} ${formatearMontoChile(Math.abs(difVentas))}</strong>
            </td>
            <td class="${colorClass}">
                <strong style="font-size: 1.1rem;">${isPositive ? '+' : ''}${difPorcentaje.toFixed(1)}%</strong>
            </td>
        </tr>`;
        
        tbody.innerHTML += fila;
    });
    
    // Fila de totales
    const difTotalVentas = totalVentas2025 - totalVentas2024;
    const difTotalPorcentaje = totalVentas2024 > 0 ? ((difTotalVentas / totalVentas2024) * 100) : 0;
    const isTotalPositive = difTotalVentas >= 0;
    const totalColorClass = isTotalPositive ? 'text-success' : 'text-danger';
    const totalArrow = isTotalPositive ? '↗️' : '↘️';
    
    const ticketPromedioTotal2025 = totalTickets2025 > 0 ? (totalVentas2025 / totalTickets2025) : 0;
    const ticketPromedioTotal2024 = totalTickets2024 > 0 ? (totalVentas2024 / totalTickets2024) : 0;
    
    const filaTotal = `<tr class="table-dark" style="font-size: 1.05rem;">
        <td><strong>🏪 TOTALES</strong></td>
        <td><strong style="color: #10b981;">${formatearMontoChile(totalVentas2025)}</strong></td>
        <td><strong><span class="badge bg-info" style="font-size: 0.95rem;">${totalTickets2025}</span></strong></td>
        <td><strong style="color: #059669;">${formatearMontoChile(ticketPromedioTotal2025)}</strong></td>
        <td><strong style="color: #f59e0b;">${formatearMontoChile(totalVentas2024)}</strong></td>
        <td><strong><span class="badge bg-secondary" style="font-size: 0.95rem;">${totalTickets2024}</span></strong></td>
        <td><strong style="color: #d97706;">${formatearMontoChile(ticketPromedioTotal2024)}</strong></td>
        <td class="${totalColorClass}">
            <strong>${totalArrow} ${formatearMontoChile(Math.abs(difTotalVentas))}</strong>
        </td>
        <td class="${totalColorClass}">
            <strong style="font-size: 1.2rem;">${isTotalPositive ? '+' : ''}${difTotalPorcentaje.toFixed(1)}%</strong>
        </td>
    </tr>`;
    
    tbody.innerHTML += filaTotal;
    
    // Actualizar fechas en el título
    document.getElementById('fecha-2025').textContent = formatDateNice(date2025);
    document.getElementById('fecha-2024').textContent = formatDateNice(date2024);
}

function formatDateNice(dateStr) {
    const [year, month, day] = dateStr.split('-');
    const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return `${day} ${monthNames[parseInt(month) - 1]} ${year}`;
}
