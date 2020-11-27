<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $table ='bedMaster';
    public $primaryKey = 'id';
    public $timestamp = true;
}
