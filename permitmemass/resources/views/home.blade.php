@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @if(Session::get('GisActive') == true)
            <div class="col-md-12">
                <!--
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        
                        

                        {{ __('You are logged in!')  }}

                    </div>
                </div>
                -->
                
                <div class="row">
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-primary text-center ">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-primary">Location Name</div>
                            <div class="card-body text-primary">{{Session::get('GlocationName','Unknown')}}</div>
                        </div> 
                    </div>
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-primary text-center">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-primary">No. of Residents</div>
                            <div class="card-body text-primary">{{Session::get('GnoOfResidents','0')}} </div>
                        </div> 
                    </div>
                    
                </div>
                <div class="row ">
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-success text-center">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-success">Total Scanned Till Date</div>
                            <div class="card-body text-success">{{$totScan}}</div>
                        </div> 
                    </div>
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-success text-center">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-success">Total Scanned Today</div>
                            <div class="card-body text-success">{{$totScanToday}} </div>
                        </div> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-danger text-center">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-danger">Total Scanned - SPO2 below 93%</div>
                            <div class="card-body text-danger">{{$totScanSPO2}}</div>
                        </div> 
                    </div>
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-danger text-center">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-danger">Total Scanned Today - SPO2 below 93%</div>
                            <div class="card-body text-danger">{{$totScanTodaySPO2}} </div>
                        </div> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-danger text-center">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-danger">Total Scanned - Temp above 99F</div>
                            <div class="card-body text-danger">{{$totScanTemp}}</div>
                        </div> 
                    </div>
                    <div class="col-6 h-100 mb-3">
                        <div class="card border-danger text-center">
                            <!-- <h4 class="card-title">Card title</h4> -->
                            <div class="card-header text-danger">Total Scanned Today - Temp above 99F</div>
                            <div class="card-body text-danger">{{$totScanTodayTemp}} </div>
                        </div> 
                    </div>
                </div>
                
            </div>
        @else
            <strong>Your location is not active. Please contact service provider.</strong>
        @endif
    </div>
</div>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection
