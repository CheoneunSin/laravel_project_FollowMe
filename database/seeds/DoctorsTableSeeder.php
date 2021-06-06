<?php

use Illuminate\Database\Seeder;
use App\ClinicSubject;
use App\Doctor;

class DoctorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $doctor_count = 10;
        $faker = \Faker\Factory::create('ja_JP');

        // $doctor_name =  ['신서준' ,'김예준' ,'김도윤' ,'김시우' ,'김주원' ,'김하준' ,'김지호' ,'김지후' ,'김준서' ,'정준우' ,'정현우' ,'정도현' ,'정지훈' ,'정건우' ,'정우진' ,'도선우' ,'도서진' ,'도민재' ,'도현준' ,'도연우' ,'도유준' ,'박정우' ,'박승우' ,'박승현' ,'박시윤' ,'박준혁' ,'차은우' ,'차지환' ];
        for($i = 0 ; $i < ClinicSubject::count() ; $i++){
            if($i == 2)
                $doctor_count = 4;
            if($i == 4)
                $doctor_count = 3;
            if($i == 7)
                $doctor_count = 1;
            if($i == 9)
                $doctor_count = 2;
            for($j = 0 ; $j < $doctor_count ; $j ++){
                $record =[
                    'clinic_subject_id' => $i + 1,
                    'doctor_name' => $faker->name
                    // 'doctor_name' => $doctor_name[array_rand($doctor_name)],
                ];
                Doctor::create($record);
            }
        }
    }
}
