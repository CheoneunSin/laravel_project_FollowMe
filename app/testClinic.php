<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class testClinic extends Model
{

    protected $table = 'test_clinics';

    protected $primaryKey = 'clinic_id';
    
    protected $guarded = [];

    public function scopeStorage($query, $patient_id,$check)  //$check -> 1 : 수납 완료, 0 -> 미수납 
    {
        return $query->select('clinic_subject_name','storage','clinic_date','clinic_time')
                        ->where('patient_id', $patient_id)
                        ->where('storage_check', $check)
                        ->where('storage', '!=' ,null);
    }

    public function patient()
    {
        return $this->belongsTo('App\testPatient', 'patient_id');
    } 
}
