@extends('layouts.app')

@section('content')
    <p class="h1">Add Bill Plan</p>
    <br/>
    {!! Form::open(['action' => 'BillPlansController@store', 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('name', 'Plan Name')}}
                    {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Plan Name'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('description', 'Description')}}
                    {{Form::text('description', '', ['class' => 'form-control', 'placeholder' => 'Description'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold" >{{Form::label('nouse','Fixed Charges')}}</div>
         
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('secdeposit', 'Security Deposit')}}
                    {{Form::text('secdeposit', '', ['class' => 'form-control', 'placeholder' => 'Security Deposit'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('hostingcharges', 'Hosting Charges')}}
                    {{Form::text('hostingcharges', '', ['class' => 'form-control', 'placeholder' => 'Hosting Charges'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold">{{Form::label('nouse','Recurring Charges')}}</div>
            
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('rent', 'Monthly Rent')}}
                    {{Form::text('rent', '', ['class' => 'form-control', 'placeholder' => 'Monthly Rent'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('txcharges', 'Per Transaction Charges')}}
                    {{Form::text('txcharges', '', ['class' => 'form-control', 'placeholder' => 'Per Transaction Charges'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold">{{Form::label('nouse','AMC')}}</div>
            
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('hardwareamc', 'Hardware AMC (%)')}}
                    {{Form::text('hardwareamc', '', ['class' => 'form-control', 'placeholder' => 'Hardware AMC %'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('softwareamc', 'Software AMC (%)')}}
                    {{Form::text('softwareamc', '', ['class' => 'form-control', 'placeholder' => 'Software AMC %'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold">{{Form::label('nouse','Optional Charges')}} </div>
            
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('training', 'Training Cost')}}
                    {{Form::text('training', '', ['class' => 'form-control', 'placeholder' => 'Training Cost'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('ins', 'Installation & Support')}}
                    {{Form::text('ins', '', ['class' => 'form-control', 'placeholder' => 'Installation & Support'])}}
                </div>
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br>
                {{Form::radio('isactive',1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                {{Form::radio('isactive',0, true, ['placeholder' => 'Status', 'checked' => 'true'])}} No
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
    <p>
       
    </p>
@endsection