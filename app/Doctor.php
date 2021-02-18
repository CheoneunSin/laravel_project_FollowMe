<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctors';
    protected $primaryKey = 'doctor_id';
    protected $guarded = ["doctor_id"];

    public function clinic_subject() {
        return $this->belongsTo('App\ClinicSubject', 'clinic_subject_id');
    }
    public $timestamps = false;
}
