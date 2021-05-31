@extends('layouts.appCenter')

@section('content')
<br/><br/><br/>

    <div class="row text-center align-center">
        <p class="h1">
            <span class="text-primary font-weight-bold"> Permit Me </span> <br/>
            <span> COVID-19 Mass Screening Solution - Absolutely Fast, Simple and Easy to Use <span> 
        </p>
        <br/>
    </div>
 

    <div class="row d-flex justify-content-center">
           
            <div id="loginForm" class="col-md-12  col-lg-6">

                    <div class="card">
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

                                        @if (Route::has('password.request'))
                                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                                {{ __('Forgot Password?') }}
                                            </a>
                                        @endif
                                 
                                </div>
                            </form>
                        </div>
                    </div>
                
                <br/><br/>
                <div>
                    <div class="card">
                        <div class="card-header">
                            Support
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item"><span>Email:</span> <span>support@permitme.in</span></li>
                                <li class="list-group-item"><span>Phone No:<span></span>+91 77188 65005</span></li>
                                <li class="list-group-item"><span>Twitter:<span></span>@permitme</span></li>
                                <li class="list-group-item"><span>Facebook:<span></span>@permitme</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
           <!-- <div class="col-md-5 text-justified">
                <p class="text-justify"> Permit Me, Mass Screening Solution to capture most important early indicators of COVID-19.</p>
                <br/>
                <p class="text-justify">As per the government policy, anyone visiting an Office, School, Hotel, Restaurant, Business location, any Public Transport or Gathering Entrances must be screened for hygiene (Wearing Mask, Hand Sanitized), Temperature and Blood oxygen level. </p>
                <br/>
                <p class="text-justify">The Temperature and SPO2 data is combined with a unique identity using RFID Technology or phone number of the person being screened.<br/>
                Once temperature and SPO2 levels are captured, this information is analyzed by the device. If any of the readings is abnormal, an immediate alert is sounded by device and an email alert is sent to the administrator to warn about the incident.
                This information is stored on the cloud and used for analysis and generating real time dashboard and reports.
                </p>
                <br/>
                <p class="h4">
                Impact of Mass Screening
                </p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Improved confidence of citizen / Employees</li>
                    <li class="list-group-item">Positive impact on the Economy</li>
                    <li class="list-group-item">Break the chain of spread, Prediction of Hot-spots</li>
                    <li class="list-group-item">Reduce Pressure on Govt Healthcare System, Provide Vaccination details for Registered person</li>
                </ul>
            </div>
            <br/><br/><br/> -->
        
    </div>

@endsection
