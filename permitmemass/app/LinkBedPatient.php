<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkBedPatient extends Model
{
    protected $table ='linkHospitalBedUser';
    public $primaryKey = 'id';
    public $timestamp = true;
}
