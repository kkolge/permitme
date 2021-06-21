@extends('layouts.app')

@section('content')
    <p class="h1">Edit Bill Plan</p>
    <br/>
    {!! Form::open(['action' => ['BillPlansController@update', $plan], 'method' => 'POST']) !!}
    <div class="form-group">
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('name', 'Plan Name')}}
                    {{Form::text('name', $plan->name, ['class' => 'form-control', 'placeholder' => 'Plan Name'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('description', 'Description')}}
                    {{Form::text('description', $plan->description, ['class' => 'form-control', 'placeholder' => 'Description'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold" >{{Form::label('nouse','Fixed Charges')}}</div>
         
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('secdeposit', 'Security Deposit')}}
                    {{Form::text('secdeposit', $plan->secdeposit, ['class' => 'form-control', 'placeholder' => 'Security Deposit'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('hostingcharges', 'Hosting Charges')}}
                    {{Form::text('hostingcharges', $plan->hostingcharges, ['class' => 'form-control', 'placeholder' => 'Hosting Charges'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold">{{Form::label('nouse','Recurring Charges')}}</div>
            
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('rent', 'Monthly Rent')}}
                    {{Form::text('rent', $plan->rentpermonth, ['class' => 'form-control', 'placeholder' => 'Monthly Rent'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('txcharges', 'Per Transaction Charges')}}
                    {{Form::text('txcharges', $plan->transactionrate, ['class' => 'form-control', 'placeholder' => 'Per Transaction Charges'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold">{{Form::label('nouse','AMC')}}</div>
            
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('hardwareamc', 'Hardware AMC (%)')}}
                    {{Form::text('hardwareamc', $plan->hardwareamcrate, ['class' => 'form-control', 'placeholder' => 'Hardware AMC %'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('softwareamc', 'Software AMC (%)')}}
                    {{Form::text('softwareamc', $plan->softwareamcrate, ['class' => 'form-control', 'placeholder' => 'Software AMC %'])}}
                </div>
            </div>
            <br/>
            <div class="font-weight-bold">{{Form::label('nouse','Optional Charges')}} </div>
            
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('training', 'Training Cost')}}
                    {{Form::text('training', $plan->trainingcost, ['class' => 'form-control', 'placeholder' => 'Training Cost'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('ins', 'Installation & Support')}}
                    {{Form::text('ins', $plan->installationandsetupcost, ['class' => 'form-control', 'placeholder' => 'Installation & Support'])}}
                </div>
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br>
                @if ($plan->isactive == '1')
                    {{Form::radio('isactive', 1, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, false, ['placeholder' => 'Status'])}} No
                @else
                    {{Form::radio('isactive', 1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} No
                @endif
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
    <p>
        &nbsp;
    </p>
@endsection