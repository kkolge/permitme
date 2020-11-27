<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class IotData extends Model
{
    use Notifiable;

    protected $table = 'iotdata';
    public $primaryKey = 'id';
    public $timestamps = true;

    

}
