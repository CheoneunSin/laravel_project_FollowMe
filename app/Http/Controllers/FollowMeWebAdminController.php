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

class FollowMeWebAdminController extends Controller
{   

    public function admin_beacon_setting_main(){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng')->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    public function admin_beacon_update(Request $request){
        $beacon_delete_data = [];
        foreach($request->input('beacon') as $beacon){
            if($beacon['check'] === "delete")
                array_push($beacon_delete_data, $beacon['beacon_id_minor']);
            else if($beacon['check'] === "create"){
                unset($beacon['check']);
                testBeacon::create($beacon);
            }
        }
        testBeacon::destroy($beacon_delete_data);
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
            // 'beacon' => $newBeaconm,
        ],200);
    }
    //범수랑 주용이랑 상의
    public function admin_beacon_defect_check(){
        $beacon_defect = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng', 'node_defect_datetime')
                                    ->where('node_defect_check', 1)
                                    ->get();
        return response()->json([
            'beacon_defect' => $beacon_defect,
        ],200);
    }

    public function admin_beacon_search(Request $request){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng', 'node_defect_datetime')
                                    ->where('beacon_id_minor', $request->input('beacon_id_minor'))
                                    ->first();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    public function admin_node_setting_main(){
        $beacon_info = testNode::select('node_id', 'lat', 'lng','floor')->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    public function admin_node_update(Request $request){
        $node_delete_data = [];
        foreach($request->input('node') as $node){
            if($node['room'] != null){
                testNode::find($node['node_id'])->room_location()->create([
                    'room_name' =>  $node['room']
                ]);
            }
            unset($node['room']);
            if($node['check'] === "delete")
                array_push($node_delete_data, $node['node_id']);
            else if($node['check'] === "create"){
                unset($node['check']);
                unset($node['check']);
                testNode::create($node);
            }
        }
        testNode::destroy($node_delete_data);
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
            // 'beacon' => $newBeaconm,
        ],200);
    }

    public function admin_node_link(Request $request){
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }
}
