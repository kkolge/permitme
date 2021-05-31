@extends('layouts.appLogin')

@section('content')
<br/>
    <div class="row">
        <p class="h1 mx-auto text-center">
            <span class="text-primary font-weight-bold"> Permit Me </span> <br/>
            <span> COVID-19 Mass Screening Solution - Absolutely Fast, Simple and Easy to Use <span> 
        </p>
        <br/>
    </div>
    <br/>
    <br/>
    <div class="row mx-auto">
        <div class="mx-auto" id="loginForm">
            <div class="card bg-transparent">
                <div class="card-header ">{{ __('Login') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="email" class="col-form-label text-md-right">{{ __('E-Mail') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-form-label text-md-right">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                        </div>
                        <div class="form-group row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>
                        <div class="form-group row justify-content-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                                @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="mx-auto" >
            <div class="card bg-transparent">
                <div class="card-header">
                    Support
                </div>
                <div class="card-body">
                    <div class="row">Email: support@permitme.in</div><br/>
                    <div class="row">Phone no: +91 77188 65005 </div> <br/>
                    <div class="row">Twitter: @permitme</div><br/>
                    <div class="row">Facebook: @permitme</div><br/>
                </div>
            </div>
        </div>
    </div>
@endsection
