<?php

namespace App\Http\Controllers;

// 모델
use App\Flow;               
use App\NodeDistance;
use App\RoomLocation;
use App\Beacon;
use App\Clinic;
use App\Patient;
use App\Node;

//파사드
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

//pusher 이벤트
use App\Events\StandbyNumber;

//다익스트라 알고리즘
use App\Services\Dijkstra;                          
use App\Services\ShortestPath;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FollowMeDisplayController extends Controller
{   
    //디스플레이 로그인
    public function display_login(Request $request){
        $patient = Patient::findOrFail($request->patient_id);
        if($patient){
            $token =  Auth::guard('patient')->login($patient);
            return response()->json(['status' =>  Config::get('constants.patient_message.login_ok'),  'patient_info' => $patient, 'token' =>  $token,], 200);
        }else{
            return response()->json(['status' => Config::get('constants.patient_message.login_fail')], 401) ;
        }
    }
    //디스플레이 로그아웃
    public function display_logout(){
        Auth::guard('patient')->logout();
        return response()->json([
            'message' => Config::get('constants.patient_message.logout_ok')
        ], 200);
    }
}
