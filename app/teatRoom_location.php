<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class teatRoom_location extends Model
{
    protected $table = 'test_room_locations';

    protected $primaryKey = 'room_location_id';
    
    protected $guarded = [];

}
