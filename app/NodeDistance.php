<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NodeDistance extends Model
{
    protected $table = 'node_distances';
    protected $primaryKey = 'distance_id';
    protected $guarded = ["distance_id"];
    protected $hidden = [
        'check',
    ];
    public function node_a_info() {
        return $this->belongsTo('App\Node', 'node_A');
    }
    public function node_b_info() {
        return $this->belongsTo('App\Node', 'node_B');
    }
    public $timestamps = false;

}
