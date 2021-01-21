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
    
    public function admin_login(Request $request){
        $auth_info = testAuth::select("auth_id", "auth_name")
                                ->where('login_id', $request->input('login_id'))
                                ->where('login_pw', $request->input('login_pw'))
                                ->where('auth_id' , '1')
                                ->first();
        
        // $request->session()->put('login_id', $request->login_id);
        // $request->session()->get('login_id');
        $message = Config::get('constants.admin_message.login_ok');
        
        return response()->json([
            'auth' => $auth_info,  //범수한테 말하기
            'message' => $message,
        ],200);
    }

    public function admin_beacon_setting_main(){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng')->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }

    public function admin_beacon_create(Request $request){
        
        foreach($request->input('beacon') as $beacon){   //beacon : []
            $newBeaconm = testBeacon::create($beacon);  //minor -> beacon_id_minor (범수한테 말하기)
        } 
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
            'beacon' => $newBeaconm,
        ],200);
    }
    public function admin_beacon_delete(Request $request){
        testBeacon::destroy($request->input('beacon'));
        $message = Config::get('constants.admin_message.delete_ok');
        return response()->json([
            'message' => $message,
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

    public function admin_node_create(Request $request){
        foreach($request->input('node') as $node){   //node : []
            if($node['room'] != null){
                $room_location = new teatRoom_location;
                $room_location->room_node = $node['id'];
                $room_location->room_name = $node['room']; 
                $room_location->save();
            }
            unset($node['room']);
            $newNode = testNode::create($node);  //minor -> beacon_id_minor (범수한테 말하기)
        } 
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }

    public function admin_node_delete(Request $request){
        testNode::destroy($request->input('node'));
        $message = Config::get('constants.admin_message.delete_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }

    public function admin_node_link(Request $request){
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }
}
