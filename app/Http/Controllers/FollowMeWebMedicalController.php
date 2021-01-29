<?php

namespace App\Http\Controllers;
use App\teatFlow;
use App\teatNodeDistance;
use App\teatRoom_location;
use App\testAuth;
use App\testBeacon;
use App\testClinic;
use App\testPatient;
use App\testNode;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Events\StandbyNumber;


class FollowMeWebMedicalController extends Controller
{
    public function medical_patient_create(Request $request){
        foreach (testPatient::select('resident_number')->cursor() as $resident_number) {
            if($resident_number['resident_number'] === $request->resident_number )
            {
                testPatient::where('resident_number',$request->resident_number)
                ->update($request->all());
                return response()->json(['message'=> "Already Exists"],200);
            }
        }

        testPatient::create($request->all());
        $message = Config::get('constants.medical_message.patient_create_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }

    public function medical_patient_search(Request $request){
        $patient_list = testPatient::select('patient_id','patient_name', 'resident_number', 'postal_code', 
                            'address', 'detail_address', 'phone_number', 'notes')
                            ->where('patient_name', $request->input('patient_name'))->get();
        return response()->json([
            'patient_list' => $patient_list,
        ],200);
    }

    public function medical_patient_select(Request $request){
        $patient = testPatient::find($request->input('patient_id'));
        $clinic  = $patient->clinic()->where('standby_status' , '1')->orderBy("clinic_time", "DESC")->first();
        $flows    = testPatient::find($request->input('patient_id'))->flow()->where("flow_status_check", 0)->get();
        $flows_record = [];
        foreach($flows as $flow){
            $flow_record = [];
            $flow_record["flow_id"] = $flow->flow_id;
            $flow_record["room_name"] = teatFlow::find($flow->flow_id)->room_location->room_name;
            array_push($flows_record, $flow_record);
        }
        return response()->json([
            'patient' => $patient,
            'clinic' => $clinic,
            'flow'   => $flows_record
        ],200);
    }
    
    public function medical_clinic_setting(Request $request){
        $clinic_info = testPatient::find($request->input('clinic_id'))
                                ->update([
                                    'room_name'     => $request->input('room_name'),
                                    'doctor_name'   => $request->input('doctor_name'),
                                    'storage'       => $request->input('storage'),
                                ]);    
        $message = Config::get('constants.medical_message.clinic_setting_ok');

        return response()->json([
            'message' => $message,            
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
                                    ->where('test_clinics.clinic_date',$request->input('clinic_date'))
                                    ->get();
        return response()->json([
            'clinic_record' => $clinic_record,            
        ],200);
    }
    
    public function medical_flow_setting(Request $request){
        foreach($request->input('flow') as $flow){
            // return testPatient::find($flow['patient_id'])->flow()->create();
            $room_location = teatRoom_location::where('room_name', $flow['room_name'])->firstOrFail();
            $room_location->flow()->create([
                'patient_id'        => $flow['patient_id'],
                'flow_sequence'     => $flow['flow_sequence'],
                'flow_create_date'  => \Carbon\Carbon::now()
            ]);    
        }
        $message = Config::get('constants.medical_message.flow_setting');
        return response()->json([
            'message' => $message,            
        ],200);
    }
    
    public function medical_clinic_end(Request $request){
        // $patient = testPatient::find($request->input('patient_id'))->flow()->where('flow_status_check', 1)
        //                     ->orderBy('flow_sequence')
        //                     ->firstOrFail();
        // $patient->flow_status_check = 0;
        // $patient->save();

        // $clinic = testPatient::find($request->input('patient_id'))->clinic()->where('stabdby_status', 1)
        //                     ->firstOrFail();
        // $clinic->stabdby_status = 0;
        // $clinic->save();
        
        //대기순번을 인자값으로 
        StandbyNumber::dispatch(testPatient::find($request->input('patient_id')));

        $message = Config::get('constants.medical_message.clinic_end');
        return response()->json([
            'message' => $message,            
        ],200);
    }
}
