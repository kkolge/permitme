<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DevAuths extends Model
{
        protected $table ='devauth';
        public $primaryKey = 'id';
        public $timestamp = true;   
}
