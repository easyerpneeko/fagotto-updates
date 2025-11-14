<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <title>PDF</title>
    <style>
        body {
            margin: auto;
            max-width: 700px;
            padding: 50px;
            font-size: 12px;
            font-family: 'Roboto', sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo {
            width: 115px;
            height: 80px;
        }

        .logo_fagotto {
            width: 80px;
        }

        .header p {
            margin-right: 10px;
        }

        .header b {
            margin-left: 8px;
        }

        .doc_tittle {
            font-size: 12px;
        }

        table td,
        th {
            padding: 8px;
        }

        table thead {
            text-align: left;
        }

        .table thead tr {
            background-color: rgb(32, 32, 32);
            color: whitesmoke;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        .table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .table-totales {
            text-align: right;
        }
    </style>
</head>

<body>
    <header>
        <table class="header-table">
            <tr>
                <td style="text-align: left;">
                    <img class="logo" src="{{ asset('images/fagottoerplogo.png') }}" alt="Logo">
                </td>
                <td style="text-align: center;">
                    <h4 class="doc_tittle">DOCUMENTO DE COBRO</h4>
                </td>
                <td style="text-align: right;">
                    <img class="logo_fagotto" src="{{ asset('images/logo_fagotto.png') }}" alt="Logo Fagotto">
                </td>
            </tr>
        </table>
    </header>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <p>Sucursal: <b>{{ strtoupper($app->name)}}</b></p>
                    <p>RUT Emisor: <b>{{ $cobro->rut_emisor }}</b></p>
                    <p>RUT Receptor: <b>{{ $cobro->rut_receptor }}</b></p>
                    <p>Direccion: <b>{{ $cobro->direccion }}</b></p>
                </td>
                <td style="text-align: right;">
                    <p>Metodo de pago: <b>{{ strtoupper($cobro->paymode) }}</b></p>
                    <p>Fecha de emisión: <b>{{ \Carbon\Carbon::parse($cobro->created_at)->format('Y-m-d') }}</b></p>
                    <p>Fecha de pago: <b>{{ \Carbon\Carbon::parse($cobro->date_pago)->format('Y-m-d') }}</b></p>
                    <p>Fecha de vencimiento: <b>{{ \Carbon\Carbon::parse($cobro->date_vencimiento)->format('Y-m-d') }}</b></p>
                </td>
            </tr>
        </table>
    </div>
    <div class="body">
        <table class="table">
            <thead>
                <tr>
                    <th style="text-align: left;">Description</th>
                    <th style="text-align: right;">Monto</th>
                    <th style="text-align: center;">Porcentaje (%)</th>
                    <th style="text-align: right;">Monto Final</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($denominaciones as $denominacion)
                <tr>
                    <td style="text-align: left;">{{ $denominacion->description }}</td>
                    <td style="text-align: right;">${{ number_format($denominacion->amount/1.19, 0, ',', '.') }}</td>
                    <td style="text-align: center;">
                        @if (is_null($denominacion->percentage) || $denominacion->percentage <= 0)
                            ---
                            @else
                            {{ $denominacion->percentage }}%
                            @endif
                            </td>
                    <td style="text-align: right;">
                        @if (is_null($denominacion->percentage) || $denominacion->percentage <= 0)
                            ---
                            @else
                            ${{ number_format(($denominacion->amount) * ($denominacion->percentage / 100), 0, ',', '.') }}
                            @endif
                            </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <hr>
    <div class="footer">
        <table class="table-totales">
            <thead></thead>
            <tbody>
                <tr>
                    <td><b>TOTAL DE VENTAS</b></td>
                    <td>${{ number_format($cobro->total_ventas, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><b>NETO</b></td>
                    <td>${{ number_format($cobro->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><b>IVA 19%</b></td>
                    <td>${{ number_format($cobro->iva, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    El neto en realidad es el total porque ya 
                    <td><b>TOTAL</b></td>
                    <td>${{ number_format($cobro->total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>