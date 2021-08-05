<nav class="navbar navbar-expand-lg navbar-dark fixed-bottom font-weight-bold">
    <div class="container">
        
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Permit Me Mass') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent1" aria-controls="navbarSupportedContent1" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent1">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                  <a class="nav-link" href="http://www.ko-aaham.com">&#169; Copyright 2021 - Ko-Aaham Technologies LLP</a>
                </li>
                <!-- Removed for sidebad -->
                <li class="nav-item">
                  <!-- <a class="nav-link" href="#">Social Media links</a> -->
                </li>
                <li class="nav-item">
                  <a class="nav-link"  href="/about">About</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/support">Support</a>
                </li> 
                <li class="nav-item">
                  <div class="nav-link disabled"> Software Version: {{env('APP_VERSION')}} </div>
                </li>
            </ul>
        </div>
    </div>
</nav>