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
use App\ClinicSubject;

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
            return response()->json(['status' =>  Config::get('constants.patient_message.login_ok'),  'patient_info' => $patient, 'token' =>  $token,], 200);
        }
        return response()->json(['status' => Config::get('constants.patient_message.login_fail')], 401) ;
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
                return response()->json(['message'=> Config::get('constants.patient_message.singup_update')],200);
            }
        }
        //환자가 병원에 방문한 적이 없을 경우 환자 데이터 생성 -> 예외 발생 X 수정 필요
        Patient::create($request->except('password_confirmation'));
        // $message = Config::get('constants.patient_message.signup_ok');
        return response()->json(['message'=> Config::get('constants.patient_message.signup_create')],200);
    }

    //환자 앱 로그아웃
    public function app_logout(Request $request){
        Auth::guard('patient')->logout();
        return response()->json([
            'message' => Config::get('constants.patient_message.logout_ok')
        ], 200);
    }
    //QR코드 스캐너 진료과 목록 반환
    public function app_clinic_info(){
        return response()->json([
            'clinic_subject_list' => ClinicSubject::all()
        ], 200);
    }
    
    //의료진 앱에서 QR코드 인식 후 진료 접수
    public function app_clinic(Request $request){
        $first_category = 1;  //초진 환자
        //초진환자가 아닐 경우 
        if(Auth::guard('patient')->user()->clinic()
                ->where('clinic_subject_name', $request->clinic_subject_name)
                ->count() > 0){
            $first_category = 0;  //재진환자
        }
        //현 진료실 대기 인원 수(대기 순번)   
        $standby_number = Clinic::where('clinic_subject_name', $request->clinic_subject_name )
                                    ->whereStandby_status(1)->count() + 1;
        //진료 접수
        $clinic = Patient::findOrFail(Auth::guard('patient')->user()->patient_id)
                    ->clinic()
                    ->create([
                        'clinic_subject_name'   => $request->clinic_subject_name,           //진료실 이름
                        'clinic_date'           => Carbon::now('Asia/Seoul'),               //진료 시 날짜
                        'clinic_time'           => Carbon::now('Asia/Seoul'),               //진료 시 날짜
                        'first_category'        => $first_category,                         //초진, 재진 구분      
                        'standby_number'        => $standby_number                          //대기 순번
                    ]);
        
        StandbyNumber::dispatch(0);  //대기 순번 이벤트
        $message = Config::get('constants.patient_message.clinic_ok');
        return response()->json([
            'message'=>$message,
        ],200);
    }
    //현 진료실의 대기 순번 가져오기 (pusher 이벤트가 발생할 때) 
    public function standby_number(){
        $standby_number = Auth::guard('patient')->user()->clinic()->whereStandby_status(1)->count() !== 0   
                          ? Auth::guard('patient')->user()->clinic()->whereStandby_status(1)->latest('standby_number')->firstOrFail()->standby_number
                          : 0;
        $clinic_info    = Auth::guard('patient')->user()->clinic()->select('clinic_subject_name','clinic_time')->whereStandby_status(1)->latest('standby_number')->first();   
        return response()->json([
            'standby_number'=> $standby_number,
            'message'       => $standby_number === 1 ? Config::get('constants.patient_message.standby_number_one') : null,
            'clinic_info'   => $clinic_info ? $clinic_info : (object)['clinic_subject_name' => '', 'clinic_time' => ''] ,
        ], 200);
    }

    //진료 동선 안내를 위한 셋팅(전체 비콘 정보) 
    public function app_beacon_list(){
        $beacon_list      = Beacon::all()->map(function ($info) {
            if($info->group == 2){
                $node            = $this->current_location_node($info->lat, $info->lng, $info->major);
                $info['lat']     = $node[0]->lat;
                $info['lng']     = $node[0]->lng;
            }
            return $info;
         })->all();
        // $beacon_list =  Beacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng', 'group')->get();
        return response()->json([
            'beacon_list'   => $beacon_list
        ],200);
    }
    //진료 동선 안내 ( + 다익스트라 알고리즘)
    public function app_flow(Request $request){
        //환자가 가야하는 동선 가져오기  (flow_status_check : 1 -> 아직 완료되지 않은 동선 , 0 -> 완료된 동선)
        $flow_list  = Auth::guard('patient')->user()->flow()->with('room_location')
                                            ->whereFlow_status_check(1)
                                            ->orderBy("flow_sequence")
                                            ->get();
        $node = $this->current_location_node($request->lat, $request->lng, $request->major);
        //진료동선이 하나 이상 있을 때 (출발지와 목적지가 필요)
        if(count($flow_list) >= 1){
            //가장 가까운 거리 
            $current        = [ "room_location" => Node::find($node[0]->node_id)];  //현위치
            $shortes_path   = new ShortestPath(); // 최단경로
            $shortes_path->node_flow_shortest_path_set($node[0]->node_id, $flow_list[0]->flow_id); //동선 설정 
            $nodeFlow       = $shortes_path->shortest_path_node(); //최단 경로 노드 
            $flow_array     = $flow_list->toArray();
            //설정된 동선 + 현 위치
            array_unshift($flow_array, $current);
        }
        return response()->json([
            'flow_list'     => !empty($flow_array) ? $flow_array : null,
            'nodeFlow'      => !empty($nodeFlow)   ? $nodeFlow   : null,
        ],200);
    }
    //동선 목록에서 현 위치와 다음 목적지의 최단 경로 반환 
    public function app_current_flow(Request $request){
        $node           = $this->current_location_node($request->lat, $request->lng, $request->major);
        $shortes_path   = new ShortestPath(); // 최단경로
        $shortes_path->node_shortest_path_set($node[0]->node_id, $request->end_room_node); //동선 설정 
        $nodeFlow       = $shortes_path->shortest_path_node(); //최단 경로 노드 
        return response()->json([
            'nodeFlow' => $nodeFlow,            
        ],200);
    }

    //현위치와 가장 가까운 노드
    public function current_location_node($lat, $lng, $major){
        return DB::select(DB::raw("SELECT *, SQRT(
            POW(69.1 * (lat - {$lat}), 2) +
            POW(69.1 * ({$lng} - lng) * COS(lat / 57.3), 2)) AS distance
            FROM nodes Where floor = {$major} HAVING distance < 25 ORDER BY distance ASC LIMIT 0,1"));
    }
    //동선 목록에서 선택시 반환
    public function app_flow_node(Request $request){
        $shortes_path = new ShortestPath(); // 최단경로 
        $shortes_path->node_shortest_path_set($request->start_room_node, $request->end_room_node); //동선 설정 
        $nodeFlow     = $shortes_path->shortest_path_node(); //최단 경로 노드 
        return response()->json([
            'nodeFlow' => $nodeFlow,            
        ],200);
    }

    // 도착지점에 도착했을 때 도착한 진료 동선 제거 
    public function app_flow_end(){
        Auth::guard('patient')->user()->flow()->whereFlow_status_check(1)->decrement("flow_sequence");
        Auth::guard('patient')->user()->flow()
                        ->whereFlow_status_check(1)
                        ->whereFlow_sequence(0)
                        ->update([ "flow_status_check" => 0 ]);   //동선 종료 
        // //진료 동선 순서 -1씩
        return response()->json([
            'message' => Config::get('constants.patient_message.flow_end'),            
        ],200);
    }
    public function app_navigation_room_list(){

        $room_list = RoomLocation::all()->filter(function ($info){
            return $info->room_node()->count() !== 0;
        })->all();
        return response()->json([
            'room_list' => $room_list,
        ],200);
    }
    //검색을 통한 실내 내비게이션 
    public function app_navigation(Request $request){
        
        //검색으로 출발지를 지정했을 시 
        $start_room_loaction = RoomLocation::room($request->start_room);
        //도착지
        $end_room_location   = RoomLocation::room($request->end_room);
        $shortes_path        = new ShortestPath(); // 최단경로 
        $shortes_path->node_shortest_path_set($start_room_loaction->room_node, $end_room_location->room_node);  //동선 설정
        $nodeFlow            = $shortes_path->shortest_path_node();  //최단 경로 노드 
 
        //최단 경로 반환         
        return response()->json([
            'nodeFlow' => $nodeFlow,
        ],200);  
    }
    public function app_navigation_current(Request $request){
        //현 위치 출발지
        $start_node         = $this->current_location_node($request->lat, $request->lng, $request->major);
        //도착지
        $end_room_location  = RoomLocation::room($request->end_room);
        $shortes_path       = new ShortestPath(); // 최단경로 
        $shortes_path->node_shortest_path_set($start_node[0]->node_id, $end_room_location->room_node);  //동선 설정
        $nodeFlow           = $shortes_path->shortest_path_node();  //최단 경로 노드 
        //최단 경로 반환         
        return response()->json([
            'nodeFlow' => $nodeFlow,
        ],200);
    }

    //미결제 진료비 내역 
    public function app_storage(Request $request){
        $storage = Clinic::storage(Auth::guard('patient')->user()->patient_id, 0)->get();
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
        //날짜별 기록 묶기
        $date = array_fill_keys(array_unique(array_column($storage_record->toArray(), 'clinic_date')), []);
        foreach($date as $key => $value){
            foreach ($storage_record as $data) {
                if ($data['clinic_date'] == $key){
                    array_push($date[$key], $data);
                    break;
                }
            }
        }    
        return response()->json([
            'storage_record' => $date,
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
                        //날짜별 기록 묶기
        $date = array_fill_keys(array_unique(array_column($flows->toArray(), 'flow_create_date')), []);
        foreach($date as $key => $value){
            foreach ($storage_record as $data) {
                if ($data['flow_create_date'] == $key){
                    array_push($date[$key], $data);
                    break;
                }
            }
        }   
        return response()->json([
            'flow_record' => $flows, 
        ],200);
    }
    //결제 화면 
    public function iamport($patient_id){
        $patient            = Patient::findOrFail($patient_id);
        $patient_id_encrypt = encrypt($patient_id);
        $storage            = Clinic::storage($patient_id, 0)->sum('storage');
        return view('iamport', ['patient' => $patient, 'patient_id' => $patient_id_encrypt,'storage' => $storage]);
    }
    //결제 완료 화면 
    public function iamport_end($patient_id){
        $patient_id_decrypt = decrypt($patient_id);
        $storage = Clinic::storage($patient_id_decrypt, 0)->sum('storage');
        $patient = Patient::findOrFail($patient_id_decrypt);
        Patient::findOrFail($patient_id_decrypt)->clinic()->where('storage_check', 0)->update(['storage_check' => 1]);
        return view('iamport_end',  ['patient' => $patient, 'storage' => $storage]);
    }

}