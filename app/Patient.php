<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Patient as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Patient extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'patients';
    protected $guard = 'patient';
    protected $primaryKey = 'patient_id';
    
    protected $guarded = []; 
    protected $hidden = [
        'password', 'patient_token',
    ];
    public function clinic()
    {
        return $this->hasMany('App\Clinic', 'patient_id');
    }
    public function flow()
    {
        return $this->hasMany('App\Flow', 'patient_id');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public $timestamps = false;


}
