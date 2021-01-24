<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class teatFlow extends Model
{
    protected $table = 'teat_flows';

    protected $primaryKey = 'flow_id';
    
    protected $guarded = [];

    public function room_location()
    {
        return $this->belongsTo('App\teatRoom', 'room_location_id');
    } 
    public function patient()
    {
        return $this->belongsTo('App\testPatient', 'patient_id');
    } 
}
