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

//pusher 이벤트
use App\Events\StandbyNumber;

//다익스트라 알고리즘
use App\Services\Dijkstra;                          

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class FollowMeAppController extends Controller
{    
    //환자 앱 로그인 
    public function app_login(Request $request){
        if ($token = Auth::guard('patient')->attempt(['login_id' => $request->login_id, 'password' => $request->password])) {
            $patient =  Auth::guard('patient')->user();
            return response()->json(['status' => 'success', 'patient_info' => $patient, 'token' =>  $token,], 200);
        }
        return response()->json(['error' => 'login_error'], 401) ;
    }
    //환자 회원 가입 
    public function app_signup(Request $request){
        $v = Validator::make($request->all(), [
            'patient_name' => 'required',
        ]);
        if ($v->fails())
        {
            return response()->json([
                'message'   => 'error',
                'errors'    => $v->errors()
            ], 422);
        }
        //환자가 병원에 방문한 적이 있을 경우 체크  
        foreach (Patient::select('resident_number')->cursor() as $resident_number) {
            if($resident_number['resident_number'] === $request->resident_number )
            {
                //방문한 적이 있으면 기존 환자 데이터에 회원가입 정보 업데이트 
                Patient::where('resident_number',$request->resident_number)
                            ->update($request->except('password_confirmation'));
                return response()->json(['message'=> "update"],200);
            }
        }
        //환자가 병원에 방문한 적이 없을 경우 환자 데이터 생성
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
        if(Clinic::where('clinic_subject_name', $request->clinic_subject_name )
                        ->where('patient_id',$request->patient_id )
                        ->count() > 0){
            $first_category = 0;  //
        }
        //현 진료실 대기 인원 수(대기 순번)   
        $standby_number = Clinic::where('clinic_subject_name', $request->clinic_subject_name )
                                    ->where("standby_status", 1)->count() + 1;
        //진료 접수
        Patient::find($request->patient_id)
                    ->clinic()
                    ->create([
                        'clinic_subject_name'   => $request->clinic_subject_name,           //진료실 이름
                        'clinic_date'           => \Carbon\Carbon::today()->toDateString(), //진료 시 날짜
                        'first_category'        => $first_category,                         //초진, 재진 구분      
                        'standby_number'        => $standby_number                          //대기 순번
                    ]);
        StandbyNumber::dispatch(true);
        $message = Config::get('constants.patient_message.clinic_ok');
        return response()->json([
            'message'=>$message,
        ],200);
    }
    //현 진료실의 대기 순번 가져오기 (pusher 이벤트가 발생할 때) 
    public function standby_number(Request $request){
        return response()->json([
            'standby_number'=>Auth::guard('patient')->user()->clinic()->where("standby_status", 1)->latest()->first()->standby_number,
        ],200);
    }

    //진료 동선 안내를 위한 셋팅(전체 비콘 정보, 현 층의 노드 정보) 
    public function app_node_beacon_get(Request $request){ 
        $node = Node::where("floor", (int)($request->major / 1000))->get();  
        $beacon = Beacon::all();
        return response()->json([
            'beacon'=>$beacon,
            'node'  =>$node
        ],200);
    }

    //진료 동선 안내 ( + 다익스트라 알고리즘)
    public function app_flow(Request $request){
        //DB에 저장된 노드 정보(노드 간 연결 거리)를 배열에 저장
        $graph = []; 
        foreach (Node::select('node_id')->cursor() as $node) {
            $graph["$node->node_id"] = [];
            foreach (NodeDistance::cursor() as $distance) {
                //노드 간 거리 저장
                if($distance->node_A == $node->node_id){
                    $graph["$node->node_id"]["$distance->node_B"] = $distance->distance;
                }
            }
        }
        $algorithm = new Dijkstra($graph);  //다익스타라 알고리즘 적용 
        //환자가 가야하는 동선 가져오기 
        $flows      = Auth::guard('patient')->user()->flow()->with('room_location')->where("flow_status_check", 1)->get();
        $start_node = [];
        $end_node   = [];
        $i          = 0;
        //전체 동선의 출발점 , 도착점 저장 
        foreach($flows as $flow){
            array_push($start_node, $flow->room_location->room_node);
            if($i === 0){
                $i = 1;
                continue;
            }
            array_push($end_node, $flow->room_location->room_node);
        }
        //출발점 노드와 도착점 노드 사이의 최단 경로 가져오기 
        $paths = [];
        for($i = 0 ; $i < count($end_node) ; $i++){
            $path = $algorithm->shortestPaths($start_node[$i], $end_node[$i]); 
            array_push($paths, $path);
        }

        //최단 경로에 있는 노드들에 대한 정보를 DB에서 가져와서 nodeFlow 배열에 저장 
        $nodeFlows = [];
        for($i = 0 ; $i < count($paths) ; $i++){
            $nodeFlow = Node::whereIn('node_id', $paths[$i][0])->get();
            array_push($nodeFlows, $nodeFlow);
        }
        //전체 진료 동선의 최단 경로 반환         
        return response()->json([
            'nodeFlow' => $nodeFlows,
        ],200);
        // [], JSON_PRETTY_PRINT
    }
    // 도착지점에 도착했을 때 도착한 진료 동선 제거 
    public function app_flow_end(){
        Auth::guard('patient')->user()->flow()
                        ->where('flow_status_check', 1)
                        ->where('flow_sequence', 1)
                        ->update([ "flow_status_check" => 0 ]); 
        //진료 동선 순서 -1씩
        Flow::where('flow_status_check', 1)->decrement("flow_sequence");
        return response()->json([
            'message' => "ok",            
        ],200);
    }

    //검색을 통한 실내 내비게이션 
    public function app_navigation(Request $request){
        //현 위치를 출발지로 지정했을 시
        if($request->has('current_location')){
            $start_loaction = $request->input('current_node');
        }
        //검색으로 출발지를 지정했을 시 
        else{
            $start_loaction = RoomLocation::select('room_node')
                                        ->where('room_name', $request->input('start_room'))->first();
        }
        //도착지
        $end_room_location = RoomLocation::select('room_node')
                                        ->where('room_name',$request->input('end_room'))->first();
        
        //DB에 저장된 노드 정보(노드 간 연결 거리)를 배열에 저장                                  
        $graph = []; 
        foreach (Node::select('node_id')->cursor() as $node) {
            $graph["$node->node_id"] = [];
            foreach (NodeDistance::cursor() as $distance) {
                //노드 간 거리 저장
                if($distance->node_A == $node->node_id){
                    $graph["$node->node_id"]["$distance->node_B"] = $distance->distance;
                }
            } 
        }
        $algorithm = new Dijkstra($graph);    //다익스타라 알고리즘 적용

        //출발점 노드와 도착점 노드 사이의 최단 경로 가져오기 
        $path = $algorithm->shortestPaths($start_loaction->room_node, $end_room_location->room_node); 
        //최단 경로에 있는 노드들에 대한 정보를 DB에서 가져와서 nodeFlow 배열에 저장 
        $nodeFlow = Node::whereIn('node_id', $path[0])->get();

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
        $storage_record = Clinic::storage(Auth::guard('patient')->user()->patient_id, '1')->get();
        return response()->json([
            'storage_record' => $storage_record,
        ],200);
    }
    //종료 된 진료 동선 내역
    public function app_flow_record(Request $request){
        $flows = Auth::guard('patient')->user()->flow()->with('room_location')->where('flow_status_check','0')->get();
        return response()->json([
            'flow_record' => $flows,            
        ],200);
    }
}