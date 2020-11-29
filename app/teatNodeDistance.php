<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class teatNodeDistance extends Model
{
    protected $table = 'test_node_distances';

    protected $primaryKey = 'distance_id';
    
    protected $guarded = [];

}
