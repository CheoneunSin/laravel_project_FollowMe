<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClinicSubject extends Model
{
    protected $table = 'clinic_subjects';
    protected $primaryKey = 'clinic_subject_id';
    protected $guarded = ["clinic_subject_id"];

    public function clinic_room() {
        return $this->hasMany('App\ClinicRoom', 'clinic_subject_id');
    }
    public function doctor() {
        return $this->hasMany('App\Doctor', 'clinic_subject_id');
    }
    public $timestamps = false;

}
