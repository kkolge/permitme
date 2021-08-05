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
    <style>
        #app1 {
            height:100%;
            background-color:purple; /* For browsers that do not support gradients */
            background-image: linear-gradient(to right, rgba(50,51,52,0.4), rgba(91,10,145,0.5));
        };

        #appbody {
            height:100%;
            background-color:purple; /* For browsers that do not support gradients */
            background-image: linear-gradient(to right, rgba(50,51,52,0.4), rgba(91,10,145,0.5));
        }

    </style>
</head>
    <body style="height:100%;
            background-color:purple; /* For browsers that do not support gradients */
            background-image: linear-gradient(to right, rgba(50,51,52,0.4), rgba(91,10,145,0.5));">
        <div class="container-fluid" id="app" >
            <div class="row">
                @include('inc.navbar')
            </div>
            
        <div id="main" class="row d-flex justify-content-cente" style="margin-left:0;">
            
            <div class="col-lg-12 col-sm-12 col-md-8 text-light  ">
                
                <br/><br/>
                @include('inc.messages')

                <main class="py-md-5 ">
                    @yield('content')
                </main>
            </div>
            <div class="col-md-2">
            </div>
        </div>
        <div class="row">
                @include('inc.footerbar')
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script>
</body>
</html>
