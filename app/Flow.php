<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flow extends Model
{
    protected $table = 'flows';

    protected $primaryKey = 'flow_id';
    
    protected $guarded = [];
    
    public function room_location()
    {
        return $this->belongsTo('App\RoomLocation', 'room_location_id');
    } 
    public function patient()
    {
        return $this->belongsTo('App\Patient', 'patient_id');
    } 

    public $timestamps = false;

}
