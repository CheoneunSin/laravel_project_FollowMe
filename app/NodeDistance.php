<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NodeDistance extends Model
{
    protected $table = 'node_distances';
    protected $primaryKey = 'distance_id';
    protected $guarded = [];

    public function node_distance() {
        return $this->belongsTo('App\Node', 'node_id');
    }
    
    public $timestamps = false;

}
