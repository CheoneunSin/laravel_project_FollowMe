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
        $clinic_room_name = ['진료실1', '진료실2', '진료실3', '진료실4', '진료실5'];
        $clinic_room_count =  count($clinic_room_name);
        for($i = 0 ; $i < ClinicSubject::count() ; $i++){
            if($i == 2)
                $clinic_room_count--;
            if($i == 4)
                $clinic_room_count--;
            if($i == 7)
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
