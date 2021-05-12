@extends('layouts.app')

@section('content')
    <p class="h1">Edit Bed Details</p>
    <br/>
    <?php
        if($act == 'discharge'){
            $ctrlState = 'disabled';
        }
        else{
            $ctrlState = '';
        }
    ?>
    {!! Form::open(['action' => ['LinkBedPatientController@update', $link->id], 'method' => 'POST']) !!}
        <div class="form-group">
            
                <div class="px-md-5">
                    {{Form::label('bedno', 'Bed No')}}
                    {{Form::select('bedno', $beds, $link->bedId, ['class'=>'form-control', $ctrlState])}}
                </div>
                <br/>
                <div class="px-md-5">
                    {{Form::label('userid', 'Patient Name')}}
                    {{Form::select('userid', $regUsers, $link->patientId, ['class'=>'form-control', $ctrlState])}}
                </div>
            
            <br/>
                {{Form::label('isactive','Admission?')}}
            @if ($link->isactive == '1')
                    {{Form::radio('isactive', 1, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, false, ['placeholder' => 'Status'])}} No
                @else
                    {{Form::radio('isactive', 1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} No
                @endif
        </div>
        @if($act == 'discharge')
            {{Form::hidden('bedno',$link->bedId)}}
            {{Form::hidden('userid',$link->patientId)}}    
        @endif
        {{Form::hidden('act','$act')}}
        {{Form::hidden('_method','PUT')}}
        <div class="flex">
            <div class="mx-auto">
                {{Form::submit('Submit', ['class'=>'btn btn-primary'])}}
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    {!! Form::close() !!}
    <p>
        &nbsp;
    </p>
@endsection