@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($data) > 0)
        <h1> <font size="+2">Hospital Dashboard</font> </h1>
        <br/>
        
        <div class="card-columns">
            @foreach($data as $cardData)
                <?php 
                    if ($cardData[3] >=94.5 || $cardData[4] < 93 || $cardData[5] > 120)
                        $bg = 'bg-danger';                    
                    else
                        $bg = 'border-success';
                    
                ?>
                <div class="card {{ $bg }}">
                    <div class="card-body ">
                        <h5 class="card-title d-flex justify-content-center"> <b>
                            {!! $cardData[1] !!} &nbsp;
                            {!! $cardData[0] !!}
                        </b></h5>
                        <p class="card-text d-flex justify-content-center">
                            Temp: {!! $cardData[3] !!}
                            <br/>
                            SPO2: {!! $cardData[4] !!}
                            <br/>
                            Pulse Rate: {!! $cardData[5] !!}
                        </p>
                        <p class="card-footer">
                            <small class="text-muted d-flex justify-content-center">
                                Captured at: {!! $cardData[6] !!} <br/>
                                Admitted on: {!! $cardData[2] !!}
                            </small>
                        </p>
                        
                    </div>
                </div>
            @endforeach
        </div>
        <p><b>Normal Temperature < 94.5, SPO2 > 93, Pulse Rate < 120</b></p>
    @else
        <h1><font size="+2">No Beds defined for your location or No Data available!</font></h1>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection