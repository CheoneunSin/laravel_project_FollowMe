<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class teatFlow extends Model
{
    protected $table = 'teat_flows';

    protected $primaryKey = 'flow_id';
    
    protected $guarded = [];

}
