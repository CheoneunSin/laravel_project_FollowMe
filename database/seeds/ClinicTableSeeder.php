<?php

use Illuminate\Database\Seeder;
use App\testPatient;
use App\testClinic;

class ClinicTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        // $room_name = teatRoom_location::inRandomOrder()->first()->room_name;

        $faker = Faker\Factory::create('ko_kr');

        $clinic_subject_name = ['신경과', '정신과', '이비인후과 ', '호흡기내과', '내과', '성형외과', '피부과', '안과', '방사선종양학과'];
        $room_name = ['진료실1', '진료실2', '진료실3','진료실4'];
        for($i = 0 ; $i < 10 ; $i++){
            $min = testPatient::min('patient_id');
            $max = testPatient::max('patient_id');
            $record =[
                'patient_id' => $faker->numberBetween($min, $max),
                'clinic_subject_name' => $clinic_subject_name[array_rand($clinic_subject_name)],
                'room_name' => $room_name[array_rand($room_name)],
                'doctor_name' => $faker->name,
                'clinic_date' => $faker->date($format = "Y-m_d", $max = 'now'),
                'clinic_time' => $faker->time($format = 'H:i:s', $max = 'now'),
                'storage' => $faker->randomDigit * 1000,
            ];
            testClinic::create($record);
        }
    }
}
