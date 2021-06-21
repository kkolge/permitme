@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($plans) > 0)
        <p class="h1">List of Bill Plans</p>
        <br/>
        <div class="d-flex">
            <div>{{$plans->links()}}</div>
            <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center" style="height:100%">
            <tr>
               
                <th>Serial No </th>
                <th>Name</th>
                <th>Description </th>
                <th>One Time </th>
                <th>Recurring</th>
                <th>AMC </th>
                <th>Optional</th>
                <th>Status</th>
                @if(Auth::user()->hasRole(['Super Admin']))
                <th>Actions</th>
                @endif
               
            </tr>
        @foreach($plans as $plan)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$plan->name}}</td>
                <td>{{$plan->description}}</td>
                <td>
                    SD: {{$plan->secdeposit}} 
                    <br>
                    HC: {{$plan->hostingcharges}}
                </td>
                <td>
                    Rent: {{$plan->rentpermonth}}
                    <br/>
                    Tx: {{$plan->transactionrate}}
                </td>
                <td>
                    HW(%): {{$plan->hardwareamcrate}}
                    <br/>
                    SW(%): {{$plan->softwareamcrate}}
                </td>
                <td>
                    Training: {{$plan->trainingcost}}
                    <br/>
                    I &amp; S: {{$plan->installationandsetupcost}}
                </td>
                <td>
                 @if ($plan->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                @if(Auth::user()->hasRole(['Super Admin']))
                <td>
                    <a href="billplans/{{$plan->id}}/edit" class="btn btn-info">Edit</a>
                </td>
                @endif
            </tr>
        @endforeach
        </table>
        {{$plans->links()}}
    @else
        <p class="h1">No Bill Plans yet!</p>        
    @endif
    <br/>
    <p>
        <div class="flex">
            @if(Auth::user()->hasRole(['Super Admin']))
            <div class="mx-auto">
                <a href="billplans/create" class="btn btn-primary">Add Plan </a>
            </div>
            @endif
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
        <br/><br/>
        <div class="pre text-right">
        SD: Security Deposit &ensp; HC: Hosting Charges &ensp; Rent: Monthly Rent &ensp; Tx: per Transaction cost &ensp; HW: Hardware AMC Rate &ensp; SW: Software AMC Rate &ensp; Training: Training Charges &ensp; I &amp; S: Installation and Setup
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection