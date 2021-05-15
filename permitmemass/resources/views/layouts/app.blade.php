<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
   
    <link href="{{ asset('css/custom-app.css') }}" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script> 
    

    <!-- Generate Menu -->
    @yield('head-script')

    <!-- End generate Menu -->
</head>
<body>
 <!-- <body class="text-white" style="background-color:black"> -->
    <div id="app">
        <div class="row">
            @include('inc.navbar')
        </div>
            
        <div id="main" class="row" style="margin-left: 0;">
            <div id="sidebar" class="col-2" style="padding-left: 0px; padding-top: 0px; height: calc(100vh);">
                <br/>
                @include('inc.sidebar')
            </div>
            
            <div class="col-10">
                <br/><br/>
                @include('inc.messages')
                <main class="py-4">
                    @yield('content')
                </main>
            </div>
        </div>
        <div class="row">
            @include('inc.footerbar')
        </div>
        
    </div>
   
</body>
</html>
