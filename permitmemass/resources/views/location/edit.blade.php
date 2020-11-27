@extends('layouts.app')

@section('content')
    <h1><font size="+2">Edit Location</font></h1>
    <br/>
    {!! Form::open(['action' => ['SocietyController@update', $location], 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="flex ">
                <div class="w-1/2 px-md-5">
                    {{Form::label('name', 'Name')}}
                    {{Form::text('name', $location->name, ['class' => 'form-control', 'placeholder' => 'Name'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('noofresidents', 'Number of Users/Beds')}}
                    {{Form::text('noofresidents', $location->noofresidents, ['class' => 'form-control', 'placeholder' => 'Number of Residents'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('address1', 'Address Line 1')}}
                    {{Form::text('address1', $location->address1, ['class' => 'form-control', 'placeholder' => 'Address Line 1'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('address2', 'Address Line 2')}}
                    {{Form::text('address2', $location->address2, ['class' => 'form-control', 'placeholder' => 'Address Line 2'])}}
                </div>
            </div>
            <br>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('pincode', 'Pincode')}}
                    {{Form::text('pincode', $location->pincode, ['class' => 'form-control', 'placeholder' => 'Pincode'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('city', 'City')}}
                    {{Form::text('city', $location->city, ['class' => 'form-control', 'placeholder' => 'City'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('taluka', 'Taluka')}}
                    {{Form::text('taluka', $location->taluka, ['class' => 'form-control', 'placeholder' => 'Taluka'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('district', 'District')}}
                    {{Form::text('district', $location->district, ['class' => 'form-control', 'placeholder' => 'District'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('state', 'State')}}
                    {{Form::text('state', $location->state, ['class' => 'form-control', 'placeholder' => 'State'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('isactive', 'Status')}}
                    <br/>
                    @if ($location->isactive == '1')
                        {{Form::radio('isactive', 1, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                        {{Form::radio('isactive', 0, false, ['placeholder' => 'Status'])}} No
                    @else
                        {{Form::radio('isactive', 1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                        {{Form::radio('isactive', 0, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} No
                    @endif 
                </div>
            </div>    
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('sms', 'Send SMS')}}
                    <br/>
                    @if ($location->smsnotification == '1')
                        {{Form::radio('sms', 1, true, ['placeholder' => 'Send SMS', 'checked' => 'checked'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                        {{Form::radio('sms', 0, false, ['placeholder' => 'Send SMS'])}} No
                    @else
                        {{Form::radio('sms', 1, false, ['placeholder' => 'Send SMS'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                        {{Form::radio('sms', 0, true, ['placeholder' => 'Send SMS', 'checked' => 'checked'])}} No
                    @endif 
                </div>
            </div> 
        </div>
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
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection