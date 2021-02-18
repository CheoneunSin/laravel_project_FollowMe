<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClinicRoom extends Model
{
    protected $table = 'clinic_rooms';
    protected $primaryKey = 'clinic_room_id';
    protected $guarded = ["clinic_room_id"];

    public function clinic_subject() {
        return $this->belongsTo('App\ClinicSubject', 'clinic_subject_id');
    }
    public $timestamps = false;
}
