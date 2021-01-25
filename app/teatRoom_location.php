<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class teatRoom_location extends Model
{
    protected $table = 'teat_room_locations';

    protected $primaryKey = 'room_location_id';
    
    protected $guarded = [];

    public function room_node()
    {
        return $this->belongsTo('App\testNode', 'room_id');
    } 

    public function flow()
    {
        return $this->hasMany('App\teatFlow', 'room_location_id');
    } 
}
