<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationPlanLink extends Model
{
    protected $table ='locationbillplanlink';
    public $primaryKey = 'id';
    public $timestamp = true;
}
