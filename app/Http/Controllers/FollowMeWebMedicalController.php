<?php

namespace App\Http\Controllers;
//모델
use App\Flow;
use App\NodeDistance;
use App\RoomLocation;
use App\Beacon;
use App\Clinic;
use App\Patient;
use App\Node;

//파사드
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

//pusher 이벤트
use App\Events\StandbyNumber;


class FollowMeWebMedicalController extends Controller
{
    //환자 데이터 생성
    public function medical_patient_create(Request $request){
        foreach (Patient::select('resident_number')->cursor() as $resident_number) {
            //앱으로 회원 정보가 있을 경우 Update (주민등록번호로 비교)
            if($resident_number['resident_number'] === $request->resident_number ){
                Patient::where('resident_number',$request->resident_number)
                        ->update($request->all());
                return response()->json(['message'=> "Already Exists"],200);
            }
        }
        //환자 데이터 생성
        Patient::create($request->all());
        $message = Config::get('constants.medical_message.patient_create_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }
    
    //환자 이름 검색 
    public function medical_patient_search(Request $request){
        //동명이인 전체 목록 조회
        $patient_list = Patient::wherePatient_name($request->input('patient_name'))->get();
        return response()->json([
            'patient_list' => $patient_list,
        ],200);
    }
    //검색 된 환자 목록 중에서 선택된 환자
    public function medical_patient_select(Request $request){
        $patient = Patient::findOrFail($request->input('patient_id'));     //환자 데이터
        $clinic  = $patient->clinic()->whereStandby_status(1)     //환자의 현 진료 데이터
                            ->orderBy("clinic_time", "desc")->first();   
        $flows    = Patient::findOrFail($request->input('patient_id'))     //환자의 현 동선 데이터
                            ->flow()->with('room_location')
                            ->where("flow_status_check", 0)->get();
        return response()->json([
            'patient' => $patient,
            'clinic' => $clinic,
            'flow'   => $flows
        ],200);
    }
    
    //진료 데이터 업데이트(QR코드로 못얻는 정보)
    public function medical_clinic_setting(Request $request){
        $clinic_info = Patient::findOrFail($request->input('clinic_id'))
                                ->update([
                                    'room_name'     => $request->input('room_name'),     //진료실 이름(진료과 이름X)
                                    'doctor_name'   => $request->input('doctor_name'),   //의사 이름
                                    'storage'       => $request->input('storage'),       //진료비
                                ]);    
        $message = Config::get('constants.medical_message.clinic_setting_ok');

        return response()->json([
            'message' => $message,            
        ],200);
    }
    //해당 날짜 진료 기록 
    public function medical_clinic_record(Request $request){
        $clinic_record  = Clinic::with('patient')->whereClinic_date($request->input('clinic_date'))->get();
        return response()->json([
            'clinic_record' => $clinic_record,            
        ],200);
    }
    //환자 진료 동선 설정
    public function medical_flow_setting(Request $request){
        //동선을 다시 수정 시, 삭제 후 다시 생성
        if($request->has("update")){
            //아직 가지 않은 동선 전체 삭제
            Patient::findOrFail($request->flow[0]['patient_id'])->flow()->whereFlow_status_check(1)->delete();
        }
        //동선 저장
        foreach($request->input('flow') as $flow){
            //진료실 및 검사실 이름과 맞는 도착지를 동선에 저장
            $room_location = RoomLocation::whereRoom_name($flow['room_name'])->firstOrFail();
            $room_location->flow()->create([
                'patient_id'        => $flow['patient_id'],
                'flow_sequence'     => $flow['flow_sequence'],
                'flow_create_date'  => \Carbon\Carbon::now()   //현 날짜 데이터 저장
            ]);    
        }
        $message = Config::get('constants.medical_message.flow_setting');
        return response()->json([
            'message' => $message,            
        ],200);
    }
    //환자 진료 완료 시
    public function medical_clinic_end(Request $request){
        //환자 전체 대기순번 -1씩
        Clinic::whereStandby_status(1)->decrement("standby_number");
        
        //진료가 완료된 환자 정보 처리
        $clinic = Patient::findOrFail($request->input('patient_id'))->clinic()->whereStandby_status(1)
                            ->update(["standby_status" => 0]);
        
        //대기순번을 인자값으로 
        StandbyNumber::dispatch(true);

        $message = Config::get('constants.medical_message.clinic_end');
        return response()->json([
            'message' => $message,            
        ],200);
    }
}
