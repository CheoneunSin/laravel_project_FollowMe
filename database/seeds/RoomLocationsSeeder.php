<?php

use Illuminate\Database\Seeder;
use App\RoomLocation;

class RoomLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roomLocations = [
            ['200', '2001'],
            ['201', '2003'],
            ['201', '2007'],
            ['220', '2005'],
            ['220', '2013'],
            ['202', '2009'],
            ['202', '2015'],
            ['221', '2017'],
            
            ['301', '3007'],
            ['320', '3005'],
            ['302-1', '3010'],
            ['302-2', '3011'],
            ['321', '3016'],
            ['372', '3018'],
            ['302-3', '3015'],
            ['303', '3020'],
            ['324', '3023'],
            ['323', '3024'],
            ['322', '3027'],
            ['325', '3028'],
            ['326', '3025'],
            ['304', '3030'],
            ['305', '3032'],
            ['300', '3001'],
            ['301', '3003'],

        ];
        $i = 1;
        foreach($roomLocations as $roomLocation){
            RoomLocation::create([
                'room_location_id' => $i,
                'room_name' => $roomLocation[0],
                'room_node' => $roomLocation[1],
            ]);
            $i += 1;
        }
    }
}
