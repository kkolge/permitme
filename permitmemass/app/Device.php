<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table ='device';
    public $primaryKey = 'id';
    public $timestamp = true;
}
