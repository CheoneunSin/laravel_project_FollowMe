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
        return $this->belongsTo('App\Node', 'room_id');
    } 

    public function flow()
    {
        return $this->hasMany('App\Flow', 'room_location_id');
    } 

    public $timestamps = false;

}
