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
            ['採血室', '3006', '検査室'],               //301
            ['薬剤室', '3004', '診療室'],               //320
            ['CT検査室', '3017', '検査室'],             //372    
            ['診療室１', '3014', '診療室'],             //302-3
            ['尿検査室', '3019', '検査室'],             //303
            ['耳鼻咽喉科診療室１', '3023','診療室'],    //324
            ['血球計数検査室', '3024', '検査室'],       //323
            ['MRI検査室', '3027','検査室'],             // 322
            ['生化学検査室', '3028', '検査室'],         //325
            ['皮膚科診療室１', '3025','診療室'],        //326
            ['血液ガス分析室', '3029', '検査室'],       //304
            ['産婦人科診療室１', '3031', '診療室'],     //305

            ['外科診療室１', '2001', '診療室'],         //200
            ['内科診療室１', '2006', '診療室'],         //201
            ['超音波検査', '2012', '検査室'],           //220
            ['小児科診療室１', '2014', '診療室'],       //202
            ['心電図検査室', '2016', '検査室'],         //221
        ];
        $i = 1;
        foreach($roomLocations as $roomLocation){
            RoomLocation::create([
                'room_location_id' => $i,
                'room_name' => $roomLocation[0],
                'room_node' => $roomLocation[1],
                'room_info' => $roomLocation[2],
            ]);
            $i += 1;
        }
    }
}
