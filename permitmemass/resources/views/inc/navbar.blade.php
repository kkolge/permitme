<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top text-white font-weight-bold" >
    <!-- brand part -->
    <div>
    <span class="navbar-brand mb-0 h1">Permit Me</span> <br/>
    </div>


    
    <!-- Right Navigation -->
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        
    <!-- Contents -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
    @if(Auth::user())
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class=nav-link href="/home">Home</a>
            </li>
            <li>
                <a class="nav-link" href="/about">About</a>
            </li>
            <li>
                <a class="nav-link" href="/support">Support</a>
            </li>
        </ul>
        @endif
        <ul class="navbar-nav ml-auto ">
            @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                </li>
            @else
                <form class="form-inline ">
                    <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success my-5 my-sm-0" type="submit">Search</button>
                </form>
                <span class="badge badge-pill badge-light">Alerts</span>
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Welcome {{ Auth::user()->name }} <span class="caret"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dark bg-primary " aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/home">Home</a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            @endguest
        </ul>
    </div>

</nav>
