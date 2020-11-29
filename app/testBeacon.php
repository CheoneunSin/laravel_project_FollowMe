<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class testBeacon extends Model
{
    protected $table = 'test_beacons';

    protected $primaryKey = 'beacon_id_minor';
    
    protected $guarded = [];

}
