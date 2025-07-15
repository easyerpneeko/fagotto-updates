/* globals Chart:false, feather:false */

(function () {
    'use strict'
    
    const countersObjeto = {
        "init_money": "0.00",
        "balanceTotal": 0,
        "gananciaTotal": 0,
        "facturas": 0,
        "boletas": 0,
        "fastSells": 0,
        "noSii": 0,
        "debito": 0,
        "transferencia": 0,
        "cheque": 0,
        "banco": 0,
        "amipass": 0,
        "multicaja": 0,
        "convenio_empresa": 0,
        "edenred": 0,
        "expenses_day": 2000,
        "totalToExpenses": -2000
      };
      
    const counters = Object.entries(countersObjeto);

    feather.replace({ 'aria-hidden': 'true' })
  
    // Graphs
    var sucursalesCanvas = document.getElementById('sucursalesChart');
    // var topSellsCanvas = document.getElementById('topSellsChart');
    // var countersCanvas = document.getElementById('countersChart');
    // var ordersCanvas = document.getElementById('ordersChart');
  
    //Custom tooltips
    var customTooltips = function(tooltip) {
        // Tooltip Element
        var tooltipEl = document.getElementById('chartjs-tooltip');

        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.id = 'chartjs-tooltip';
            tooltipEl.innerHTML = "<table></table>"
            this._chart.canvas.parentNode.appendChild(tooltipEl);
        }

        // Hide if no tooltip
        if (tooltip.opacity === 0) {
            tooltipEl.style.opacity = 0;
            return;
        }

        // Set caret Position
        tooltipEl.classList.remove('above', 'below', 'no-transform');
        if (tooltip.yAlign) {
            tooltipEl.classList.add(tooltip.yAlign);
        } else {
            tooltipEl.classList.add('no-transform');
        }

        function getBody(bodyItem) {
            return bodyItem.lines;
        }

        // Set Text
        if (tooltip.body) {
            var titleLines = tooltip.title || [];
            var bodyLines = tooltip.body.map(getBody);

            var innerHtml = '<thead>';

            titleLines.forEach(function(title) {
                innerHtml += '<tr><th>' + title + '</th></tr>';
            });
            innerHtml += '</thead><tbody>';

            bodyLines.forEach(function(body, i) {
                var colors = tooltip.labelColors[i];
                var style = 'background:' + colors.backgroundColor;
                style += '; border-color:' + colors.borderColor;
                style += '; border-width: 2px'; 
                var span = '<span class="chartjs-tooltip-key" style="' + style + '"></span>';
                innerHtml += '<tr><td>' + span + body + '</td></tr>';
            });

            const objeto = {
                // "init_money": "0.00",
                // "balanceTotal": 0,
                // "gananciaTotal": 0,
                "facturas": 0,
                "boletas": 0,
                "fastSells": 0,
                "noSii": 0,
                "debito": 0,
                "transferencia": 0,
                "cheque": 0,
                "banco": 0,
                "amipass": 0,
                "multicaja": 0,
                "convenio_empresa": 0,
                "edenred": 0,
                // "expenses_day": 2000,
                // "totalToExpenses": -2000
              };
          
              const array = Object.entries(objeto);
          
              array.forEach(function(entry) {
                var key = entry[0];
                var value = entry[1];
                innerHtml += '<tr><td>' + key + ': ' + value + '</td></tr>';
              });

            innerHtml += '</tbody>';

            var tableRoot = tooltipEl.querySelector('table');
            tableRoot.innerHTML = innerHtml;
        }

        var positionY = this._chart.canvas.offsetTop;
        var positionX = this._chart.canvas.offsetLeft;

        // Display, position, and set styles for font
        tooltipEl.style.opacity = 1;
        tooltipEl.style.left = positionX + tooltip.caretX + 'px';
        tooltipEl.style.top = positionY + tooltip.caretY + 'px';
        tooltipEl.style.fontFamily = tooltip._fontFamily;
        tooltipEl.style.fontSize = tooltip.fontSize;
        tooltipEl.style.fontStyle = tooltip._fontStyle;
        tooltipEl.style.padding = tooltip.yPadding + 'px ' + tooltip.xPadding + 'px';
    };

    var sucursalesChart = new Chart(sucursalesCanvas, {
      type: 'bar',
      data: {
        labels: [
          'KYB LOCAL 14',
          'KYB COMPUTACION LOCAL 47',
          'Local951',
          'KYB LOCAL 15',
          'KYB COMPUTACION LOCAL 47',
          'KYB LOCAL 14',
          'KYB COMPUTACION LOCAL 47'
        ],
        datasets: [{
          data: [
            15339,
            21345,
            18483,
            24003,
            23489,
            24092,
            12034
          ],
          lineTension: 0,
          backgroundColor: '#0d6efd',
          borderColor: '#007bff',
          borderWidth: 4,
        //   pointBackgroundColor: '#0d6efd'
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        },
        legend: {
          display: false
        },
        tooltips: {
            enabled: false,
            mode: 'index',
            position: 'nearest',
            custom: customTooltips
        }
      }
    })
  
    // var topSellsChart = new Chart(topSellsCanvas, {
    //   type: 'bar',
    //   data: {
    //     labels: [
    //       'Bolsa Winnex 105 X 150',
    //       'Bolsa Winnex 105 X 20',
    //       'Bolsa Winnex 105 X 80',
    //       'Bolsa Winnex 10 X 80',
    //     ],
    //     datasets: [{
    //       data: [
    //         4,
    //         5,
    //         7,
    //         3
    //       ],
    //       lineTension: 0,
    //       backgroundColor: 'transparent',
    //       borderColor: '#007bff',
    //       borderWidth: 4,
    //       pointBackgroundColor: '#007bff',
    //     }],
    //   },
    //   options: {
    //     indexAxis: 'y',
    //     scales: {
    //       yAxes: [{
    //         ticks: {
    //           beginAtZero: true
    //         }
    //       }]
    //     },
    //     legend: {
    //       display: false
    //     }
    //   }
    // })
  
    // var countersChart = new Chart(countersCanvas, {
    //   type: 'pie',
    //   data: {
    //     labels: [
    //       'Facturas',
    //       'Boletas',
    //       'Guia Despacho',
    //       'Efectivo',
    //       'Debito',
    //       'Amipass',
    //       'Rappi',
    //       'Junaeb',
    //       'Credito',
    //       'Convenio Empresa',
    //       'Transferencia',
    //       'Cheque',
    //       'Transbank',
    //       'Ventas Rapidas',
    //     ],
    //     datasets: [{
    //       label: 'Contadores',
    //       data: [
    //         2000,
    //         3000,
    //         1000,
    //         5000,
    //         5000,
    //         2000,
    //         5000,
    //         3000,
    //         9000,
    //         8000,
    //         6000,
    //         3000,
    //         5000,
    //       ],
    //       lineTension: 0,
    //       backgroundColor: [
    //         'rgb(255, 255, 86)',
    //         'rgb(255, 150, 132)',
    //         'rgb(54, 0, 235)',
    //         'rgb(255, 50, 86)',
    //         'rgb(150, 0, 86)',
    //         'rgb(50, 99, 132)',
    //         'rgb(54, 162, 100)',
    //         'rgb(255, 180, 86)',
    //         'rgb(200, 0, 86)',
    //         'rgb(50, 99, 132)',
    //         'rgb(54, 162, 80)',
    //         'rgb(150, 205, 86)',
    //         'rgb(100, 0, 86)'
    //       ],
    //       borderColor: '#fff',
    //       pointBackgroundColor: '#007bff',
    //     }],
    //   },
    //   options: {
  
    //   }
    // })
  
    // var ordersChart = new Chart(ordersCanvas, {
    //   type: 'bar',
    //   data: {
    //     labels: [
    //       'Facturas',
    //       'Boletas',
    //       'Guia Despacho',
    //     ],
    //     datasets: [{
    //       // label: 'Contadores',
    //       data: [
    //         2,
    //         5,
    //         3,
    //       ],
    //       lineTension: 0,
    //       backgroundColor: [
    //         'rgb(255, 255, 86)',
    //         'rgb(255, 150, 132)',
    //         'rgb(54, 0, 235)',
    //       ],
    //       borderColor: '#fff',
    //       pointBackgroundColor: '#007bff',
    //     }],
    //   },
    //   options: {
    //     scales: {
    //       yAxes: [{
    //         ticks: {
    //           beginAtZero: true
    //         }
    //       }]
    //     },
    //     legend: {
    //       display: false
    //     }
    //   }
    // })
  })()
  