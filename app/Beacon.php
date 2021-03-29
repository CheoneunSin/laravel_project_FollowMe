<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Beacon extends Model
{
    protected $table = 'beacons';

    protected $primaryKey = 'beacon_id_minor';
    
    protected $guarded = [];

    public $timestamps = false;

}
