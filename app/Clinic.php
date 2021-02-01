<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{

    protected $table = 'clinics';

    protected $primaryKey = 'clinic_id';
    
    protected $guarded = ["clinic_id"];

    public function scopeStorage($query, $patient_id, $check)  //$check -> 1 : 수납 완료, 0 -> 미수납 
    {
        return $query->select('clinic_subject_name','storage','clinic_date','clinic_time')
                        ->where('patient_id', $patient_id)
                        ->where('storage_check', $check)
                        ->where('storage', '!=' , null);
    }

    public function patient()
    {
        return $this->belongsTo('App\Patient', 'patient_id');
    }
    public $timestamps = false;

    
}
