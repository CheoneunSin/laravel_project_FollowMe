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
use Illuminate\Support\Facades\DB;
use App\Services\Dijkstra;


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

    public function app_signup(Request $request){
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
        foreach (testBeacon::select('beacon_id_minor')->where('node_check', 1)->cursor() as $node) {
            $graph["$node->beacon_id_minor"] = [];
            foreach (teatNodeDistance::select('nodeA', 'distance' ,"nodeB")->cursor() as $distance) {
                if($distance->nodeA == $node->beacon_id_minor){
                    $graph["$node->beacon_id_minor"]["$distance->nodeB"] = $distance->distance;
                }
            }
        }

        $algorithm = new Dijkstra($graph);
        $path = $algorithm->shortestPaths('1111', '666666'); 
        
        $nodeFlow = [];
        for($i = 0; $i < count($path[0]) ; $i++){
            $node = testBeacon::select('beacon_id_minor', 'major as floor', 'lat', 'lng')
                        ->where('beacon_id_minor', "{$path[0][$i]}")->get();
            $nodeFlow[$i] = $node[0];
        }
        // $request->session()->put('id', '1111');

        // session(['id' => '1111']);
        // dd($request);
        // $value = $request->session()->get('key');
        // session(['id' => '1111']);
        // dd($request->session()->get('id'));
        // $request->session()->get('id');
        return response()->json([
            'nodeFlow' => $nodeFlow,
        ],200);
    }

    public function app_navigation(Request $request){
        $graph = []; 
        foreach (testBeacon::select('beacon_id_minor')->where('node_check', 1)->cursor() as $node) {
            $graph["$node->beacon_id_minor"] = [];
            foreach (teatNodeDistance::select('nodeA', 'distance' ,"nodeB")->cursor() as $distance) {
                if($distance->nodeA == $node->beacon_id_minor){
                    $graph["$node->beacon_id_minor"]["$distance->nodeB"] = $distance->distance;
                }
            }
        }

        $algorithm = new Dijkstra($graph);
        $path = $algorithm->shortestPaths('1111', '22222'); 
        
        $nodeFlow = [];
        for($i = 0; $i < count($path[0]) ; $i++){
            $node = testBeacon::select('beacon_id_minor', 'major as floor', 'lat', 'lng')
                        ->where('beacon_id_minor', "{$path[0][$i]}")
                        ->get();
            $nodeFlow[$i] = $node[0];
        }
        //in 
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

    // public function app_info(Request $request){
    //     $patient_info = testPatient::select('patient_name', 'resident_number', 'postal_code', 'address'
    //                         , 'detail_address', 'phone_number', 'notes')
    //                         ->where('patient_id', $request->input('patient_id'))
    //                         ->get();
    //     return response()->json([
    //         'patient_info' => $patient_info,
    //                 
    //     ],200);
    // }

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
