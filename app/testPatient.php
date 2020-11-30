<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class testPatient extends Model
{
    protected $table = 'test_patients';

    protected $primaryKey = 'patient_id';
    
    protected $guarded = []; 

}
