<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegUser extends Model
{
    protected $table ='reguser';
    public $primaryKey = 'id';
    public $timestamp = true;
}
