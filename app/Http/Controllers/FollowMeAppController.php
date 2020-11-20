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
                            ->where('login_id', $request->login_id)
                            ->where('login_pw', $request->login_pw)
                            ->get();

        return response()->json([
            'patient_info' => $patient_info,
            'patient_token' => Str::random(60),
            'check' => true
        ],200);
    }

    public function app_signup(Request $request){
        return response()->json([
            'message'=>'생성되었습니다',
            'check' => true
        ],200);
    }

    public function app_clinic(Request $request){
        return response()->json([
            'message'=>'정상 접수가 되었습니다.',
            'check' => true
        ],200);
    }

    public function app_flow(Request $request){
        $nodes = testBeacon::select('beacon_id_minor')->where('node_check', 1)->get();
        $distances = teatNodeDistance::select('nodeA', 'distance' ,"nodeB")->get();
        $graph = []; 
        foreach($nodes as $node){
            $graph["$node->beacon_id_minor"] = [];
            foreach($distances as $distance){
                if($distance->nodeA == $node->beacon_id_minor){
                    $graph["$node->beacon_id_minor"]["$distance->nodeB"] = $distance->distance;
                }
            }
        }
        $algorithm = new Dijkstra($graph);
        $path = $algorithm->shortestPaths('2222', '9999'); 
        
        $nodeFlow = [];
        for($i = 0; $i < count($path[0]) ; $i++){
            $node = testBeacon::select('beacon_id_minor', 'major as floor', 'lat', 'lng')
                        ->where('beacon_id_minor', "{$path[0][$i]}")->get();
            $nodeFlow[$i] = $node[0];
        }
        return response()->json([
            'nodeFlow' => $nodeFlow,
            'check' => true
        ],200);
    }

    public function app_navigation(Request $request){
        $nodes = testBeacon::select('beacon_id_minor')->where('node_check', 1)->get();
        $distances = teatNodeDistance::select('nodeA', 'distance' ,"nodeB")->get();
        $graph = []; 
        foreach($nodes as $node){
            $graph["$node->beacon_id_minor"] = [];
            foreach($distances as $distance){
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
        return response()->json([
            'nodeFlow' => $nodeFlow,
            'check' => true
        ],200);
    }

    public function app_storage(Request $request){
        $storage = testClinic::select('clinic_subject_name','storage','clinic_date','clinic_time')
                            ->where('patient_id', $request->patient_id)
                            ->where('storage_check', '0')
                            ->where('storage', '!=' ,null)
                            ->get();
        return response()->json([
            'storage' => $storage,
            'check' => true
        ],200);
    }

    public function app_storage_record(Request $request){
        $storage_record = testClinic::select('clinic_subject_name','storage','clinic_date','clinic_time')
                            ->where('patient_id', $request->patient_id)
                            ->where('storage_check', '1')
                            ->where('storage', '!=' ,null)
                            ->get();
        return response()->json([
            'storage_record' => $storage_record,
            'check' => true
        ],200);
    }

    // public function app_info(Request $request){
    //     $patient_info = testPatient::select('patient_name', 'resident_number', 'postal_code', 'address'
    //                         , 'detail_address', 'phone_number', 'notes')
    //                         ->where('patient_id', $request->patient_id)
    //                         ->get();
    //     return response()->json([
    //         'patient_info' => $patient_info,
    //         'check' => true            
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
            'check' => true            
        ],200);
    }

}
