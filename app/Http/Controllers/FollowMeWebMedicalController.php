<?php

namespace App\Http\Controllers;
use App\testFlow;
use App\teatNodeDistance;
use App\teatRoom_location;
use App\testAuth;
use App\testBeacon;
use App\testClinic;
use App\testPatient;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FollowMeWebMedicalController extends Controller
{
    public function medical_login(Request $request){
        $auth_info = testAuth::select("auth_id", "auth_name")
                                ->where('login_id', $request->login_id)
                                ->where('login_pw', $request->login_pw)
                                ->where('auth_id' , '1')
                                ->get();                        
        // $request->session()->put('login_id', $request->login_id);
        // $request->session()->get('login_id');p;
        return response()->json([
            'auth' => $auth_info[0],
            'message' => "로그인에 성공했습니다",
            'check' => true
        ],200);
    }

    public function medical_patient_create(Request $request){
        return response()->json([
            'message' => "생성 되었습니다.",
            'check' => true
        ],200);
    }

    public function medical_patient_search(Request $request){
        $patient_info = testPatient::select('patient_id','patient_name', 'resident_number', 'postal_code', 
                            'address', 'detail_address', 'phone_number', 'notes')
                            ->where('patient_name', $request->patient_name)
                            ->get();
        $patient_clinic_info = DB::table('test_clinics')->join('test_patients', 
                                        'test_patients.patient_id' , 
                                        'test_clinics.patient_id')
                                        ->select(   'test_clinics.clinic_id',
                                                    'test_clinics.clinic_subject_name', 
                                                    'test_clinics.clinic_date',
                                                    'test_clinics.first_category')
                                        ->where('test_clinics.standby_status',"1")
                                        ->where('patient_name', $request->patient_name)
                                        ->get();
        $flow_record  = DB::table('teat_flows')->join('test_patients', 
                                'test_patients.patient_id' , 
                                'teat_flows.patient_id')
                                            ->join('teat_room_locations', 
                                'teat_room_locations.room_location_id',
                                'teat_flows.room_location_id')
                                ->select(   'teat_room_locations.room_name',
                                            'teat_flows.flow_sequence')
                                ->where('teat_flows.flow_status_check',"1")
                                ->where('test_patients.patient_name', $request->patient_name)
                                ->get();
        return response()->json([
            'patient_info' => $patient_info,
            'patient_clinic_info' =>  $patient_clinic_info,
            'flow_record' => $flow_record,
            'check' => true            
        ],200);
    }
    public function medical_clinic_setting(Request $request){

        return response()->json([
            'message' => '진료 정보가 설정되었습니다.',
            'check' => true            
        ],200);
    }
    public function medical_clinic_record(Request $request){
        $clinic_record  = DB::table('test_clinics')->join('test_patients', 
                                    'test_patients.patient_id' , 
                                    'test_clinics.patient_id')
                                    ->select(   'test_clinics.clinic_id',
                                                'test_patients.patient_id','test_patients.patient_name',
                                                'test_clinics.clinic_subject_name', 
                                                'test_clinics.room_name',
                                                'test_clinics.doctor_name',
                                                'test_clinics.clinic_time',
                                                'test_clinics.standby_status')
                                    ->where('test_clinics.clinic_date',$request->clinic_date)
                                    ->get();
        return response()->json([
            'clinic_record' => $clinic_record,
            'check' => true            
        ],200);
    }
    public function medical_clinic_end(Request $request){
        return response()->json([
            'message' => '진료가 종료되었습니다.',
            'check' => true            
        ],200);
    }

    public function medical_flow_setting(Request $request){
        return response()->json([
            'message' => '동선이 설정되었습니다.',
            'check' => true            
        ],200);
    }
}
