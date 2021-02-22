<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Node extends Model {
    protected $table = 'nodes';

    protected $primaryKey = 'node_id';
    
    protected $guarded = []; 

    public function room_location() {
        return $this->hasMany('App\RoomLocation', 'room_node');
    } 
    
    public function node_A_distance() {
        return $this->hasMany('App\NodeDistance', 'node_A');
    }
    public function node_B_distance() {
        return $this->hasMany('App\NodeDistance', 'node_B');
    }

    public $timestamps = false;

}
