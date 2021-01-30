<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NodeDistance extends Model
{
    protected $table = 'node_distances';
    protected $primaryKey = 'distance_id';
    protected $guarded = [];

    public $timestamps = false;

}
