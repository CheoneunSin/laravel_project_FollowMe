<?php

namespace App\Http\Controllers;
use App\testFlow;
use App\teatNodeDistance;
use App\teatRoom_location;
use App\testAuth;
use App\testBeacon;
use App\testClinic;
use App\testPatient;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FollowMeWebAdminController extends Controller
{
    public function admin_login(Request $request){
        $auth_info = testAuth::select("auth_id", "auth_name")
                                ->where('login_id', $request->login_id)
                                ->where('login_pw', $request->login_pw)
                                ->where('auth_id' , '1')
                                ->get();
        // $request->session()->put('login_id', $request->login_id);
        // $request->session()->get('login_id');
        return response()->json([
            'auth' => $auth_info[0],
            'message' => "로그인에 성공했습니다",
        ],200);
    }

    public function admin_beacon_setting_main(){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lon')->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }

    public function admin_beacon_create(Request $request){
        foreach($request->beacon as $beacon){
            $newBeacon = new testBeacon;
            $newBeacon->beacon_id_minor = $beacon['minor'];
            $newBeacon->uuid = $beacon['uuid'];
            $newBeacon->major = $beacon['major'];
            $newBeacon->lat = $beacon['lat'];
            $newBeacon->lng = $beacon['lng'];
            $newBeacon->save();
        }
    
        return response()->json([
            'message' => "설정되었습니다.",
            // 'newBeacon' => $newBeacon , 
        ],200);
    }

    public function admin_beacon_delete(Request $request){
        return response()->json([
            'message' => "삭제되었습니다.",
        ],200);
    }

    public function admin_beacon_defect_check(){
        $beacon_defect = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lon', 'node_defect_datetime')
                                    ->where('node_defect_check', 1)
                                    ->get();
        return response()->json([
            'beacon_defect' => $beacon_defect,
        ],200);
    }

    public function admin_beacon_search(Request $request){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lon', 'node_defect_datetime')
                                    ->where('beacon_id_minor', $request->beacon_id_minor)
                                    ->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }


    public function admin_node_setting_main(){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lon','node_check')
                                    ->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }

    public function admin_node_create(Request $request){
        return response()->json([
            'message' => "설정되었습니다.",
        ],200);
    }

    public function admin_node_delete(Request $request){

        return response()->json([
            'message' => "삭제되었습니다.",
        ],200);
    }

    public function admin_node_link(Request $request){
        
        return response()->json([
            'message' => "설정되었습니다.",
        ],200);
    }
}
