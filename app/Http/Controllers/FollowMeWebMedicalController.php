<?php

namespace App\Http\Controllers;
use App\testFlow;
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

class FollowMeWebMedicalController extends Controller
{
    public function medical_patient_create(Request $request){
        testPatient::create($request->all());
        $message = Config::get('constants.medical_message.patient_create_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }

    public function medical_patient_search(Request $request){
        try {
            $patient_info = testPatient::select('patient_id','patient_name', 'resident_number', 'postal_code', 
                            'address', 'detail_address', 'phone_number', 'notes')
                            ->where('patient_name', $request->input('patient_name'))
                            ->firstOrFail();
            $patient_clinic_info = testPatient::find($patient_info->patient_id)
                                            ->clinic->last()
                                            ->select(   'clinic_id',
                                                        'clinic_subject_name',
                                                        'clinic_date',
                                                        'first_category')
                                            ->where(   'standby_status' , '1')
                                            ->firstOrFail();
        } catch (\Throwable $th) {
            $patient_info = null;
            $patient_clinic_info = null;
        }                    
                                       
        $flow_record  = DB::table('teat_flows')->join('test_patients', 
                                'test_patients.patient_id' , 
                                'teat_flows.patient_id')
                                            ->join('teat_room_locations', 
                                'teat_room_locations.room_location_id',
                                'teat_flows.room_location_id')
                                ->select(   'teat_room_locations.room_name',
                                            'teat_flows.flow_sequence')
                                ->where('teat_flows.flow_status_check',"1")
                                ->where('test_patients.patient_name', $request->input('patient_name'))
                                ->get();

        return response()->json([
            'patient_info' => $patient_info,
            'patient_clinic_info' =>  $patient_clinic_info,
            'flow_record' => $flow_record->first(),            
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
        $patient = testPatient::find($request->input('patient_id'))->flow()->where('flow_status_check', 1)
                            ->orderBy('flow_sequence')
                            ->firstOrFail();
        $patient->flow_status_check = 0;
        $patient->save();

        $clinic = testPatient::find($request->input('patient_id'))->clinic()->where('stabdby_status', 1)
                            ->firstOrFail();
        $clinic->stabdby_status = 0;
        $clinic->save();

        $message = Config::get('constants.medical_message.clinic_end');
        return response()->json([
            'message' => $message,            
        ],200);
    }
}
