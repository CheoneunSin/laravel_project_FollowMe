<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class testNode extends Model
{
    protected $table = 'test_node';

    protected $primaryKey = 'node_id';
    
    protected $guarded = []; 

    public function room_location()
    {
        return $this->hasMany('App\teatRoom_location', 'room_node');
    } 
}
