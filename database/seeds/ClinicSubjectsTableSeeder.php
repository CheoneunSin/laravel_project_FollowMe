<?php

use Illuminate\Database\Seeder;
use App\ClinicSubject;

class ClinicSubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clinic_subject_name = ['内科', '循環器内科', '外科','精神科', '産婦人科', '麻酔科', '整形外科', '泌尿器科', '皮膚科', '耳鼻咽喉科', '眼科', '小児科'];
        for($i = 0 ; $i < count($clinic_subject_name) ; $i++){
            $record =[
                'clinic_subject_id' => $i + 1,
                'clinic_subject_name' => $clinic_subject_name[$i],
            ];
            ClinicSubject::create($record);
        }
    }
}
