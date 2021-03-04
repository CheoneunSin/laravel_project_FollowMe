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
use App\ClinicSubject;

//파사드
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

//pusher 이벤트
use App\Events\StandbyNumber;
use Carbon\Carbon;

// Validator
use App\Http\Requests\PatientSearch;
use App\Http\Requests\PatientCreate;

class FollowMeWebMedicalController extends Controller
{
    //환자 데이터 생성
    public function medical_patient_create(PatientCreate $request){
        $request->validated();
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
        return response()->json([
            'status' => 'success', 
        ],200);
    }
    
    //환자 이름 검색 
    public function medical_patient_search(PatientSearch $request){
        $request->validated();
        //동명이인 전체 목록 조회
        $patients = Patient::where("patient_name", $request->patient_name)->get()->map(function ($info) {
            $info['resident_number'] = explode("-", $info->resident_number)[0];
            return $info;
         })->all();
        return response()->json([
            'patient_list' => $patients,
        ],200);
    }
    //검색 된 환자 목록 중에서 선택된 환자
    public function medical_patient_select(Request $request){
        $patient = Patient::findOrFail($request->patient_id);     //환자 데이터
        $patient->resident_number = explode("-", $patient->resident_number)[0];
        $clinic  = $patient->clinic()->whereStandby_status(1)     //환자의 현 진료 데이터
                            ->orderBy("clinic_time", "desc")->first();  
        if($clinic === null) {
            $clinic = [
                'clinic_id' => '',
                'clinic_subject_name' => '',
                'clinit_time'=>'',
                'doctor_name'=>'' ,
            ];
        }
        //진료 정보 목록(진료과, 진료실, 의사)
        $clinic_info = ClinicSubject::all()->map(function ($info) {
           $info['doctor']      = $info->doctor()->get()->pluck('doctor_name')->all();
           $info['clinic_room'] = $info->clinic_room()->get()->pluck('clinic_room_name')->all();
           return $info;
        })->all();

        return response()->json([
            'patient'       => $patient,
            'clinic'        => $clinic,
            'clinic_info'   => $clinic_info,  
        ],200);
    }
    
    //진료 데이터 업데이트(QR코드로 못얻는 정보)
    public function medical_clinic_setting(Request $request){
        //진료 접수 Update
        $first_category = 1;  //초진 환자

        //초진환자가 아닐 경우 
        if(Clinic::where('clinic_subject_name', $request->clinic_subject_name)
                        ->wherePatient_id($request->patient_id)
                        ->count() > 0){
            $first_category = 0;  //재진환자
        }
        //현 진료실 대기 인원 수(대기 순번)   
        $standby_number = Clinic::where('clinic_subject_name', $request->clinic_subject_name )
                                    ->whereStandby_status(1)->count() + 1;
        //진료 접수
        $data                   = $request->all();
        $data['clinic_date']    = Carbon::now('Asia/Seoul');
        $data['first_category'] = $first_category;  //초진, 재진 구분 
        $data['standby_number'] = $standby_number;  //대기 순번
        $clinic =  Patient::findOrFail($request->patient_id)->clinic()->whereStandby_status(1)->count() === 0 
                   ?    Patient::findOrFail($request->patient_id)
                        ->clinic()->create($data)
                  :     Patient::findOrFail($request->patient_id)
                            ->clinic()->whereStandby_status(1)     
                            ->orderBy("clinic_time", "desc")
                            ->first()
                            ->update($request->all()); 
        return response()->json([
            'clinic' => $clinic,            
        ],200);
    }
    //해당 날짜 진료 기록 
    public function medical_clinic_subject(Request $request){
        return response()->json([
            'clinic_subject' => ClinicSubject::all(),   
        ],200);
    }
    //해당 날짜 진료 기록 
    public function medical_clinic_record(Request $request){
        $clinic_record  =  !empty($request->clinic_subject_name) ? 
                              Clinic::with('patient')->whereClinic_date($request->clinic_date)->whereClinic_subject_name($request->clinic_subject_name)->get()
                            : Clinic::with('patient')->whereClinic_date($request->clinic_date)->get();

        return response()->json([
            'clinic_record' => $clinic_record,   
        ],200);
    }
    //환자 진료 동선 설정
    public function medical_flow_setting(Request $request){
        //동선을 다시 수정 시, 삭제 후 다시 생성
        Patient::findOrFail($request->patient_id)->flow()->whereFlow_status_check(1)->delete();
        //동선 저장
        foreach($request->flow as $flow){
            //진료실 및 검사실 이름과 맞는 도착지를 동선에 저장
            $room_location = RoomLocation::findOrFail($flow['room_location_id']);
            $room_location->flow()->create([
                'patient_id'        => $request->patient_id,
                'flow_sequence'     => $flow['flow_sequence'],
                'flow_create_date'  => Carbon::now()   //현 날짜 데이터 저장
            ]);    
        }
        return response()->json([
            'status' => 'success', 
        ],200);
    }

    //환자 진료 동선 목록 
    public function medical_flow_list(Request $request){
         //환자의 현 동선 데이터
        $flow    = Patient::find($request->patient_id)->flow()->with('room_location')
                                                      ->whereFlow_status_check(1)->get();
        return response()->json([
            'flow' => $flow,            
        ],200);
    }
    //환자 진료 완료 시
    public function medical_clinic_end(Request $request){
        //환자 전체 대기순번 -1씩
        Clinic::whereStandby_status(1)->whereClinic_subject_name($request->clinic_subject_name)->decrement("standby_number");
        Clinic::find($request->clinic_id)->update(["standby_status" => 0]);
        //대기순번을 인자값으로 
        $patient_id = Clinic::find($request->clinic_id)->patient->patient_id;
        StandbyNumber::dispatch($patient_id);

        $clinic_record  = Clinic::with('patient')->whereClinic_date($request->clinic_date)->get();
        return response()->json([
            'clinic_record' => $clinic_record,            
        ],200);
    }
    //진료실 검사실 목록(flow)
    public function medical_room_info(){
        $room_list = RoomLocation::all()->filter(function ($info){
            return $info->room_node()->count() !== 0;
        })->all();
        return response()->json([
            'room_list' => $room_list,            
        ],200);
    }
} 
