<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkLocUser extends Model
{
    protected $table ='linklocusers';
    public $primaryKey = 'id';
    public $timestamp = true;
}
