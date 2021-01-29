<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Dijkstra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\teatFlow;
use App\teatNodeDistance;
use App\teatRoom_location;
use App\testBeacon;
use App\testClinic;
use App\testPatient;
use App\testNode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FollowMeAppController extends Controller
{    
    public function app_login(Request $request){
        if ($token = Auth::guard('patient')->attempt(['login_id' => $request->login_id, 'password' => $request->password])) {
            $patient =  Auth::guard('patient')->user();
            return response()->json(['status' => 'success', 'patient_info' => $patient, 'token' =>  $token,], 200);
        }
        return response()->json(['error' => 'login_error'], 401) ;
    }
    public function app_signup(Request $request){
        $v = Validator::make($request->all(), [
            'patient_name' => 'required',
        ]);
        if ($v->fails())
        {
            return response()->json([
                'message' => 'error',
                'errors' => $v->errors()
            ], 422);
        }
        foreach (testPatient::select('resident_number')->cursor() as $resident_number) {
            if($resident_number['resident_number'] === $request->resident_number )
            {
                testPatient::where('resident_number',$request->resident_number)
                ->update($request->except('password_confirmation'));
                return response()->json(['message'=> "update"],200);
            }
        }
        testPatient::create($request->except('password_confirmation'));
        // $message = Config::get('constants.patient_message.signup_ok');
        return response()->json(['message'=> "create"],200);
    }
    public function app_logout(Request $request){
        Auth::guard('patient')->logout();
        $message = Config::get('constants.patient_message.logout_ok');
        return response()->json([
            'message' => $message
        ], 200);
    }
    public function app_clinic(Request $request){
        $first_category = 1;  //초진 환자
        if(testClinic::where('clinic_subject_name', $request->clinic_subject_name )
                        ->where('patient_id',$request->patient_id )
                        ->count() > 0){
            $first_category = 0;  //
        }
        $patient = testPatient::find($request->patient_id)
                                    ->clinic()
                                    ->create([
                                        'clinic_subject_name' => $request->clinic_subject_name,
                                        'clinic_date' => \Carbon\Carbon::today()->toDateString(),
                                        'first_category' => $first_category,
                                    ]);
        $message = Config::get('constants.patient_message.clinic_ok');
        return response()->json([
            'message'=>$message,
        ],200);
    }

    public function standby_number(Request $request){
        $clinic_subject_name = Auth::guard('patient')->user()->clinic()->where("standby_status", 1)->first()->clinic_subject_name;
        return testClinic::where("clinic_subject_name", $clinic_subject_name)->where("standby_status", 1)->count();
    }
    //major 값 한 자리 수 로 변경? 
    public function app_node_beacon_get(Request $request){ 
        $node = testNode::where("floor", (int)($request->major / 1000))->get();
        $beacon = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng')->get();
        return response()->json([
            'beacon'=>$beacon,
            'node'  =>$node
        ],200);
    }

    public function app_flow(Request $request){
        $graph = []; 
        foreach (testNode::select('node_id')->cursor() as $node) {
            $graph["$node->node_id"] = [];
            foreach (teatNodeDistance::select('node_A', 'distance' ,"node_B")->cursor() as $distance) {
                if($distance->node_A == $node->node_id){
                    $graph["$node->node_id"]["$distance->node_B"] = $distance->distance;
                }
            }
        }
        $algorithm = new Dijkstra($graph);
        
        $flows = Auth::guard('patient')->user()->flow()->with('room_location')->where("flow_status_check", 1)->get();
        $start_node = [];
        $end_node = [];
        $i = 0;
        foreach($flows as $flow){
            array_push($start_node, $flow->room_location->room_node);
            if($i === 0){
                $i = 1;
                continue;
            }
            array_push($end_node, $flow->room_location->room_node);
        }
        $paths = [];
        for($i = 0 ; $i < count($end_node) ; $i++){
            $path = $algorithm->shortestPaths($start_node[$i], $end_node[$i]); 
            array_push($paths, $path);
        }
        // $path = $algorithm->shortestPaths('2001', '2020'); 
        $nodeFlows = [];
        for($i = 0 ; $i < count($paths) ; $i++){
            $nodeFlow = testNode::select('node_id', 'floor', 'lat', 'lng', 'stair_check')
                            ->whereIn('node_id', $paths[$i][0])->get();
            array_push($nodeFlows, $nodeFlow);
        }
        // $nodeFlow = testNode::select('node_id', 'floor', 'lat', 'lng', 'stair_check')
        //                     ->whereIn('node_id', $path[0])->get();
                        
        return response()->json([
            'nodeFlow' => $nodeFlows,
        ],200);
        // [], JSON_PRETTY_PRINT
    }
    public function app_flow_end(){
        Auth::guard('patient')->user()->flow()
                        ->where('flow_status_check', 1)
                        ->where('flow_sequence', 1)
                        ->update([
                            "flow_status_check" => 0
                        ]); 
        teatFlow::where('flow_status_check', 1)->decrement("flow_sequence");
        return response()->json([
            'message' => "ok",            
        ],200);

    }
    public function app_navigation(Request $request){
        if($request->has('current_location')){
            $start_loaction = $request->input('current_node');
        }else{
            $start_loaction = teatRoom_location::select('room_node')
                                        ->where('room_name', $request->input('start_room'))->first();
        }
        $end_room_location = teatRoom_location::select('room_node')
                                        ->where('room_name',$request->input('end_room'))->first();
        $graph = []; 
        foreach (testNode::select('node_id')->cursor() as $node) {
            $graph["$node->node_id"] = [];
            foreach (teatNodeDistance::select('node_A', 'distance' ,"node_B")->cursor() as $distance) {
                if($distance->node_A == $node->node_id){
                    $graph["$node->node_id"]["$distance->node_B"] = $distance->distance;
                }
            } 
        }
        $algorithm = new Dijkstra($graph);
        $path = $algorithm->shortestPaths($start_loaction->room_node, $end_room_location->room_node); 
        $nodeFlow = testNode::select('node_id', 'floor', 'lat', 'lng', 'stair_check')
                        ->whereIn('node_id', $path[0])->get();

        return response()->json([
            'nodeFlow' => $nodeFlow,
        ],200);
    }

    public function app_storage(Request $request){
        $storage = testClinic::storage(Auth::guard('patient')->user()->patient_id, '0')->get();
        return response()->json([
            'storage' => $storage,
        ],200);
    }

    public function app_storage_record(Request $request){
        $storage_record = testClinic::storage(Auth::guard('patient')->user()->patient_id, '1')->get();
        return response()->json([
            'storage_record' => $storage_record,
        ],200);
    }

    public function app_flow_record(Request $request){
        $flows_record = [];
        $flows = Auth::guard('patient')->user()->flow()->with('room_location')->where('flow_status_check','0')->get();
        return response()->json([
            'flow_record' => $flows,            
        ],200);
    }
}