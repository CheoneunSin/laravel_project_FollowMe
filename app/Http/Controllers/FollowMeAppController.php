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
use Illuminate\Support\Facades\Validator;
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
// validation
use App\Http\Requests\PatientRegister;
use App\Http\Requests\PatientLogin;

class FollowMeAppController extends Controller
{    
    //환자 앱 로그인 
    public function app_login(PatientLogin $request){
        $request->validated();
        if ($token = Auth::guard('patient')->attempt(['login_id' => $request->login_id, 'password' => $request->password])) {
            $patient =  Auth::guard('patient')->user();
            return response()->json(['status' => 'success', 'patient_info' => $patient, 'token' =>  $token,], 200);
        }
        return response()->json(['error' => 'login_error'], 401) ;
    }
    
    //환자 회원 가입 
    public function app_signup(PatientRegister $request){
        $request->validated();
        //환자가 병원에 방문한 적이 있을 경우 체크  
        foreach (Patient::select('resident_number')->cursor() as $resident_number) {
            if($resident_number['resident_number'] === $request->resident_number )
            {
                //방문한 적이 있으면 기존 환자 데이터에 회원가입 정보 업데이트 
                Patient::whereResident_number($request->resident_number)
                            ->update($request->except('password_confirmation'));
                return response()->json(['message'=> "update"],200);
            }
        }
        //환자가 병원에 방문한 적이 없을 경우 환자 데이터 생성 -> 예외 발생 X 수정 필요
        Patient::create($request->except('password_confirmation'));
        // $message = Config::get('constants.patient_message.signup_ok');
        return response()->json(['message'=> "create"],200);
    }

    //환자 앱 로그아웃
    public function app_logout(Request $request){
        Auth::guard('patient')->logout();
        $message = Config::get('constants.patient_message.logout_ok');
        return response()->json([
            'message' => $message
        ], 200);
    }
    
    //의료진 앱에서 QR코드 인식 후 진료 접수
    public function app_clinic(Request $request){
        $first_category = 1;  //초진 환자
        //초진환자가 아닐 경우 
        if(Clinic::where('clinic_subject_name', $request->clinic_subject_name)
                        ->wherePatient_id($request->patient_id)
                        ->count() > 0){
            $first_category = 0;  //재진환자
        }
        //현 진료실 대기 인원 수(대기 순번)   
        $standby_number = Clinic::where('clinic_subject_name', $request->clinic_subject_name )
                                    ->whereStandby_status(1)->count() + 1;
        //진료 접수
        $patient = Patient::findOrFail($request->patient_id)
                    ->clinic()
                    ->create([
                        'clinic_subject_name'   => $request->clinic_subject_name,           //진료실 이름
                        'clinic_date'           => Carbon::now(), //진료 시 날짜
                        'first_category'        => $first_category,                         //초진, 재진 구분      
                        'standby_number'        => $standby_number                          //대기 순번
                    ]);
                    
        StandbyNumber::dispatch(true);  //대기 순번 이벤트

        $message = Config::get('constants.patient_message.clinic_ok');
        return response()->json([
            'message'=>$message,
        ],200);
    }
    //현 진료실의 대기 순번 가져오기 (pusher 이벤트가 발생할 때) 
    public function standby_number(Request $request){
        return response()->json([
            'standby_number'=>Auth::guard('patient')->user()->clinic()->whereStandby_status(1)->first()->standby_number,
        ],200);
    }

    //진료 동선 안내를 위한 셋팅(전체 비콘 정보, 현 층의 노드 정보) 
    public function app_node_beacon_get(Request $request){ 
        $node   = Node::whereFloor($request->major)->get();   
        $beacon = Beacon::all();
        return response()->json([
            'beacon'=>$beacon,
            'node'  =>$node
        ],200);
    }
    //진료 동선 안내 ( + 다익스트라 알고리즘)
    public function app_flow(Request $request){
         //환자가 가야하는 동선 가져오기  (flow_status_check : 1 -> 아직 완료되지 않은 동선 , 0 -> 완료된 동선)
        $flow_list = Auth::guard('patient')->user()->flow()->with('room_location')->whereFlow_status_check(1)->get();
        $nodeFlow  = null;
        //진료동선이 하나 이상 있을 때 (출발지와 목적지가 필요)
        if(count($flow_list) >= 1){
            //가장 가까운 거리 
            $node = DB::select(DB::raw("SELECT *, 111.045 * DEGREES(ACOS(COS(RADIANS({$request->lat})) 
                                * COS(RADIANS(`lat`)) * COS(RADIANS(`lng`) - RADIANS({$request->lat})) 
                                + SIN(RADIANS({$request->lng})) * SIN(RADIANS(`lat`)))) AS distance 
                                FROM nodes Where floor = {$request->major} ORDER BY distance ASC LIMIT 0,1"));             
            $shortes_path = new ShortestPath(); // 최단경로
            $shortes_path->node_flow_shortest_path_set($node[0]->node_id, $flow_list[0]->flow_id); //동선 설정 
            $nodeFlow     = $shortes_path->shortest_path_node(); //최단 경로 노드 
        }
    return response()->json([
            'flow_list' => $flow_list,
            'nodeFlow'  => $nodeFlow,            
        ],200);
    }

    public function app_flow_node(Request $request){
        $shortes_path = new ShortestPath(); // 최단경로 
        $shortes_path->flow_shortest_path_set($request->start_flow_id, $request->end_flow_id); //동선 설정 
        $nodeFlow     = $shortes_path->shortest_path_node(); //최단 경로 노드 
        return response()->json([
            'nodeFlow' => $nodeFlow,            
        ],200);
    }

    // 도착지점에 도착했을 때 도착한 진료 동선 제거 
    public function app_flow_end(){
        Auth::guard('patient')->user()->flow()
                        ->whereFlow_status_check(1)
                        ->whereFlow_sequence(1)
                        ->update([ "flow_status_check" => 0 ]);   //동선 종료 
        //진료 동선 순서 -1씩
        Flow::whereFlow_status_check(1)->decrement("flow_sequence");
        return response()->json([
            'message' => "ok",            
        ],200);
    }

    //검색을 통한 실내 내비게이션 
    public function app_navigation(Request $request){
        //현 위치를 출발지로 지정했을 시
        if($request->has('current_location')){
            $start_room_loaction = $request->current_node;
        }
        //검색으로 출발지를 지정했을 시 
        else {
            $start_room_loaction = RoomLocation::select('room_node')
                                            ->whereRoom_name($request->start_room)->first();
        }
        //도착지
        $end_room_location = RoomLocation::select('room_node')
                                        ->whereRoom_name($request->end_room)->first();
        $shortes_path = new ShortestPath(); // 최단경로 
        $shortes_path->node_shortest_path_set($start_room_loaction->room_node, $end_room_location->room_node);  //동선 설정
        $nodeFlow     = $shortes_path->shortest_path_node();  //최단 경로 노드 
 
        //최단 경로 반환         
        return response()->json([
            'nodeFlow' => $nodeFlow,
        ],200);
    }

    //미결제 진료비 내역 
    public function app_storage(Request $request){
        $storage = Clinic::storage(Auth::guard('patient')->user()->patient_id, '0')->get();
        return response()->json([
            'storage' => $storage,
        ],200);
    }
    //결제 내역
    public function app_storage_record(Request $request){
        $storage_record = Clinic::storage(Auth::guard('patient')->user()->patient_id, '1')
                            ->whereBetween(
                                'clinic_date', [ $request->input('start_date', Carbon::now()->subMonth()), 
                                $request->input('end_date', Carbon::now())]
                            )->get();
        return response()->json([
            'storage_record' => $storage_record,
        ],200);
    }
    //종료 된 진료 동선 내역
    public function app_flow_record(Request $request){
        $flows = Auth::guard('patient')->user()->flow()->with('room_location')
                        ->whereFlow_status_check(0)
                        ->whereBetween(
                            'flow_create_date', [ $request->input('start_date', Carbon::now()->subMonth()), 
                            $request->input('end_date', Carbon::now())]
                        )->get();
        return response()->json([
            'flow_record' => $flows,            
        ],200);
    }
}