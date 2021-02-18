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
        $clinic_subject_name = ['마취과', '이빈인후과', '정신과 ', '내과', '외과', '신경외과', '순환기내과', '비뇨기과', '산부인과', '성형외과', '소아과', '병리과'];
        for($i = 0 ; $i < count($clinic_subject_name) ; $i++){
            $record =[
                'clinic_subject_name' => $clinic_subject_name[$i],
            ];
            ClinicSubject::create($record);
        }
    }
}
