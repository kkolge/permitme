<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllDataReport extends Model
{
    protected $table = 'iotdata';
    public $primaryKey = 'id';
    public $timestamps = true;
}
