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
    

    <!-- Generate Menu -->

    <!-- End generate Menu -->
</head>
<body>
    <div id="app" >
        @include('inc.navbar')
        <div>
            @if(!Auth::guest())
            <div class="container aligh-right">
            <!-- adding the user information -->
            <!-- name --> Welcome {{Auth::user()->name}}, &nbsp; 
            <!-- Designation -->{{Session::get('GDesignation','')}}, &nbsp;
            <!-- Location Name --> {{Session::get('GlocationName', '')}}
            <!-- End adding the user information -->
            </div>
            @endif
        </div>
        <div id="main" class="row">
            <div id="sidebar" class="col-md-3">
                @include('inc.sidebar')
            </div>
            
            <div class="col-md-9">
                @include('inc.messages')
                <main class="py-4">
                    
                    @yield('content')
                </main>
            </div>
        </div>
        @include('inc.footerbar')
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script>
</body>
</html>
