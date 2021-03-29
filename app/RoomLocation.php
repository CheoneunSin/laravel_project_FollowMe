<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoomLocation extends Model
{
    protected $table = 'room_locations';

    protected $primaryKey = 'room_location_id';
    
    protected $guarded = [];

    public function room_node()
    {
        return $this->belongsTo('App\Node', 'room_node');
    } 

    public function flow()
    {
        return $this->hasMany('App\Flow', 'room_location_id');
    } 

    public function scopeRoom($query, $room)  
    {
        return $query->select('room_node')->whereRoom_name($room)->first();
    }

    public $timestamps = false;

}
