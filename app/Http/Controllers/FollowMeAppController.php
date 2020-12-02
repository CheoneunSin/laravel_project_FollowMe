<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\testFlow;
use App\teatNodeDistance;
use App\teatRoom_location;
use App\testBeacon;
use App\testClinic;
use App\testPatient;
use App\testNode;

use Illuminate\Support\Facades\DB;
use App\Services\Dijkstra;
use Illuminate\Support\Facades\Config;


class FollowMeAppController extends Controller
{
    public function app_login(Request $request){
        $patient_info = testPatient::select('patient_id','patient_name', 'resident_number', 'postal_code', 'address'
                                , 'detail_address', 'phone_number', 'notes')
                            ->where('login_id', $request->input('login_id'))
                            ->where('login_pw', $request->input('login_pw'))
                            ->get();
        $message = Config::get('constants.patient_message.login_ok');

        return response()->json([
            'patient_info' => $patient_info,
            'patient_token' => Str::random(60),
            'message'   =>$message
        ],200);
    }
    //병원을 다닌 환자와 병원을 한번도 가지 않은 환자 분류
    public function app_signup(Request $request){

        $newPatient = new testPatient;
        $newPatient->patient_name = $request->input('patient_name');;
        $newPatient->phone_number = $request->input('phone_number');;
        $newPatient->login_id = $request->input('login_id');;
        $newPatient->login_pw = $request->input('login_pw');;
        $newPatient->resident_number = $request->input('resident_number');;  //범수한테 말하기 resudent X
        $newPatient->patient_token = Str::random(60);
        $newPatient->save();
            
        $message = Config::get('constants.patient_message.signup_ok');
        return response()->json([
            'message'=>$message,
        ],200);
    }

    public function app_clinic(Request $request){
        $message = Config::get('constants.patient_message.clinic_ok');
        return response()->json([
            'message'=>$message,
        ],200);
    }
                 
    public function app_flow(Request $request){
        $graph = []; 
        foreach (testNode::select('node_id')->where('node_check', 1)->cursor() as $node) {
            $graph["$node->node_id"] = [];
            foreach (teatNodeDistance::select('node_A', 'distance' ,"node_B")->cursor() as $distance) {
                if($distance->node_A == $node->node_id){
                    $graph["$node->node_id"]["$distance->node_B"] = $distance->distance;
                }
            }
        }

        $algorithm = new Dijkstra($graph);
        $path = $algorithm->shortestPaths('15001', '15009'); 

        $nodeFlow = testNode::select('node_id', 'floor', 'lat', 'lng')
                            ->whereIn('node_id', $path[0])->get();
        return response()->json([
            'nodeFlow' => $nodeFlow,
        ],200);
    }

    public function app_navigation(Request $request){
        $start_room_loaction = teatRoom_location::select('room_node')->where('room_name','320')->first();
        $end_room_location = teatRoom_location::select('room_node')->where('room_name','화장실')->first();

        $graph = []; 
        foreach (testNode::select('node_id')->where('node_check', 1)->cursor() as $node) {
            $graph["$node->node_id"] = [];
            foreach (teatNodeDistance::select('node_A', 'distance' ,"node_B")->cursor() as $distance) {
                if($distance->node_A == $node->node_id){
                    $graph["$node->node_id"]["$distance->node_B"] = $distance->distance;
                }
            }
        }

        $algorithm = new Dijkstra($graph);
        $path = $algorithm->shortestPaths($start_room_loaction->room_node, $end_room_location->room_node); 
        $nodeFlow = testNode::select('node_id', 'floor', 'lat', 'lng')
                        ->whereIn('node_id', $path[0])->get();

        return response()->json([
            'nodeFlow' => $nodeFlow,
        ],200);
    }

    public function app_storage(Request $request){
        $storage = testClinic::storage($request->input('patient_id'), '0')->get();
        return response()->json([
            'storage' => $storage,
        ],200);
    }

    public function app_storage_record(Request $request){
        $storage_record = testClinic::storage($request->input('patient_id'), '1')->get();
        return response()->json([
            'storage_record' => $storage_record,
        ],200);
    }

    public function app_flow_record(Request $request){
        $flow_record  = DB::table('teat_flows')->join('teat_room_locations', 
                                        'teat_room_locations.room_location_id' , 
                                        'teat_flows.patient_id')
                                        ->select('room_name','flow_create_date' )
                                        ->where('flow_status_check','1')
                                        ->get();
        return response()->json([
            'flow_record' => $flow_record,            
        ],200);
    }

}