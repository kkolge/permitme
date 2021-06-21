<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillPlans extends Model
{
    protected $table ='billplan';
    public $primaryKey = 'id';
    public $timestamp = true;
}
