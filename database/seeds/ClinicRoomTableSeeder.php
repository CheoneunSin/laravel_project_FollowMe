<?php

use Illuminate\Database\Seeder;
use App\ClinicSubject;
use App\ClinicRoom;

class ClinicRoomTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clinic_room_name = ['診療室１', '診療室２', '診療室３', '診療室４', '診療室５'];
        $clinic_room_count =  count($clinic_room_name);
        for($i = 0 ; $i < ClinicSubject::count() ; $i++){
            if($i == 2)
                $clinic_room_count--;
            else if($i == 4)
                $clinic_room_count--;
            else if($i == 7)
                $clinic_room_count--;

            for($j = 0 ; $j < $clinic_room_count; $j++){
                $record =[
                    'clinic_subject_id' => $i + 1,    
                    'clinic_room_name' => $clinic_room_name[$j],
                ];
                ClinicRoom::create($record);
            }
        }
    }
}
