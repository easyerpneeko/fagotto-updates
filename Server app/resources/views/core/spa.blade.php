<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="icon" type="image/ico" href="{{ url('images/favicon.icon') }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(Auth::user())
      <meta name="auth-token" content="{{ Session::Get('JWT') }}">
      <meta name="user-data" content='<?= json_encode(Auth::user()) ?>'>
    @else
      <meta name="auth-token" content="token-not-found">
    @endif

    <title>FagottoERP</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body class="hold-transition sidebar-mini dark">

    <!-- wrapper -->
    <div class="wrapper" id="app">
        @yield('content')
    </div>
    <!-- ./wrapper -->

    <script type="text/javascript">
      const Laravel_URL = "<?=url('')?>";
      //window.Laravel_URL = Laravel_URL;
    </script>
    <!-- Scripts -->
    <script type="text/javascript" src="{{ url('js/app.js') }}"></script>

    </script>
</body>

</html>
