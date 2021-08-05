@extends('layouts.appCenter')

@section('content')
@if(!Auth::user())
    <br/><br/><br/>
@endif
<div class="mx-auto col-6 text-justify">
    <p class="h1">Support</p>

<p>Please contact Ko-Aaham Technologies for any support related queries </p>
<br/><br/>


    <div class="card bg-transparent">
        
        <div class="card-body">
            <div class="row">Email: support@permitme.in</div><br/>
            <div class="row">Phone no: +91 77188 65005 </div> <br/>
            <div class="row"> Blog: Coming soon</div><br/>
            <div class="row">Support Ticket: Coming soon </div><br/>
        </div>
    </div>


<br/>
<p class="test-small">Backend Software version: {{env('APP_VERSION')}}</p>
</div>
@endsection 