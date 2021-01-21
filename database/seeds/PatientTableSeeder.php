<?php

use Illuminate\Database\Seeder;
use App\testPatient;

class PatientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    
    public function run() {
        $rrm = [
            '950316-1585439',
            '720615-2400336',
            '850821-6159121',
            '790505-5694540',
            '760825-1852507',
            '890724-2143895',
            '790922-1869870',
            '860321-5613505',
            '720201-1534434',
            '830619-1735051',
            '941203-6189432',
            '951103-2662568',
            '760102-1153267',
            '930730-5659857',
            '800604-6944814',
            '821006-2365240',
            '940123-1597715',
            '810619-6858979',
            '921001-5743398',
            '971010-5665191',
        ];
        $faker = Faker\Factory::create('ko_kr');
        for($i = 0; $i < 5; $i++){
            testPatient::create([
                'patient_name' => $faker->name,
                'login_id' => $faker->email,
                'login_pw' => 1234,
                'resident_number' => $rrm[$i],
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
            ]);
            
        }
    }
}
