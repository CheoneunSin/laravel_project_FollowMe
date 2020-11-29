<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class testAuth extends Model
{
    protected $table = 'test_auths';

    protected $primaryKey = 'auth_id';
    
    protected $guarded = [];

}
