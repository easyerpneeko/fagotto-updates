/* globals Chart:false, feather:false */

(function () {
  'use strict'

  feather.replace({ 'aria-hidden': 'true' })

  // Graphs
  var sellsByHourCanvas = document.getElementById('sellsByHourChart');
  var topSellsCanvas = document.getElementById('topSellsChart');
  var countersCanvas = document.getElementById('countersChart');
  var ordersCanvas = document.getElementById('ordersChart');

  // eslint-disable-next-line no-unused-vars
  var sellsByHourChart = new Chart(sellsByHourCanvas, {
    type: 'line',
    data: {
      labels: [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
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
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        borderWidth: 4,
        pointBackgroundColor: '#007bff'
      }]
    },
    options: {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: false
          }
        }]
      },
      legend: {
        display: false
      }
    }
  })

  var topSellsChart = new Chart(topSellsCanvas, {
    type: 'bar',
    data: {
      labels: [
        'Bolsa Winnex 105 X 150',
        'Bolsa Winnex 105 X 20',
        'Bolsa Winnex 105 X 80',
        'Bolsa Winnex 10 X 80',
      ],
      datasets: [{
        data: [
          4,
          5,
          7,
          3
        ],
        lineTension: 0,
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        borderWidth: 4,
        pointBackgroundColor: '#007bff',
      }],
    },
    options: {
      indexAxis: 'y',
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }]
      },
      legend: {
        display: false
      }
    }
  })

  var countersChart = new Chart(countersCanvas, {
    type: 'pie',
    data: {
      labels: [
        'Facturas',
        'Boletas',
        'Guia Despacho',
        'Efectivo',
        'Debito',
        'Amipass',
        'Rappi',
        'Credito',
        'Convenio Empresa',
        'Transferencia',
        'Cheque',
        'Transbank',
        'Ventas Rapidas',
      ],
      datasets: [{
        label: 'Contadores',
        data: [
          2000,
          3000,
          1000,
          5000,
          5000,
          2000,
          5000,
          3000,
          9000,
          8000,
          6000,
          3000,
          5000,
        ],
        lineTension: 0,
        backgroundColor: [
          'rgb(255, 255, 86)',
          'rgb(255, 150, 132)',
          'rgb(54, 0, 235)',
          'rgb(255, 50, 86)',
          'rgb(150, 0, 86)',
          'rgb(50, 99, 132)',
          'rgb(54, 162, 100)',
          'rgb(255, 180, 86)',
          'rgb(200, 0, 86)',
          'rgb(50, 99, 132)',
          'rgb(54, 162, 80)',
          'rgb(150, 205, 86)',
          'rgb(100, 0, 86)'
        ],
        borderColor: '#fff',
        pointBackgroundColor: '#007bff',
      }],
    },
    options: {

    }
  })

  var ordersChart = new Chart(ordersCanvas, {
    type: 'bar',
    data: {
      labels: [
        'Facturas',
        'Boletas',
        'Guia Despacho',
      ],
      datasets: [{
        // label: 'Contadores',
        data: [
          2,
          5,
          3,
        ],
        lineTension: 0,
        backgroundColor: [
          'rgb(255, 255, 86)',
          'rgb(255, 150, 132)',
          'rgb(54, 0, 235)',
        ],
        borderColor: '#fff',
        pointBackgroundColor: '#007bff',
      }],
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
      }
    }
  })
})()
