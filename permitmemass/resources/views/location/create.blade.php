@extends('layouts.app')

@section('content')
    <h1><font size="+2">Add Location</font></h1>
    {!! Form::open(['action' => 'SocietyController@store', 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="flex ">
                <div class="w-1/2 px-md-5">
                    {{Form::label('name', 'Name')}}
                    {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Name'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('noofresidents', 'Number of Users/Beds')}}
                    {{Form::text('noofresidents', '', ['class' => 'form-control', 'placeholder' => 'Number of Residents'])}}
                </div>
            </div>
            <br/>
            <div class="flex ">
                <div class="w-1/2 px-md-5">
                    {{Form::label('address1', 'Address Line 1')}}
                    {{Form::text('address1', '', ['class' => 'form-control', 'placeholder' => 'Address Line 1'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('address2', 'Address Line 2')}}
                    {{Form::text('address2', '', ['class' => 'form-control', 'placeholder' => 'Address Line 2'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('pincode', 'Pincode')}}
                    {{Form::text('pincode', '', ['class' => 'form-control', 'placeholder' => 'Pincode'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('city', 'City')}}
                    {{Form::text('city', '', ['class' => 'form-control', 'placeholder' => 'City'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('taluka', 'Taluka')}}
                    {{Form::text('taluka', '', ['class' => 'form-control', 'placeholder' => 'Taluka'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('district', 'District')}}
                    {{Form::text('district', '', ['class' => 'form-control', 'placeholder' => 'District'])}}
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('state', 'State')}}
                    {{Form::text('state', '', ['class' => 'form-control', 'placeholder' => 'State'])}}
                </div>
                <div class="w-1/2 px-md-5">
                    {{Form::label('isactive', 'Status')}}<br/>
                    {{Form::radio('isactive',1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive',0, true, ['placeholder' => 'Status', 'checked' => 'true'])}} No
                </div>
            </div>
            <br/>
            <div class="flex">
                <div class="w-1/2 px-md-5">
                    {{Form::label('sms', 'Send SMS')}}<br/>
                    {{Form::radio('sms',1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('sms',0, true, ['placeholder' => 'Status', 'checked' => 'true'])}} No
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="mx-auto">
                {{Form::submit('Submit', ['class'=>'btn btn-primary'])}}
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    {!! Form::close() !!}
    <p>&nbsp;</p><p>&nbsp;</p>
    <p>&nbsp;</p>
@endsection