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
    //비콘 정보 가져오기
    public function admin_beacon_setting_main(){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng', "check")->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    //비콘 정보 업데이트 (추가 , 삭제)
    public function admin_beacon_update(Request $request){ 
        testBeacon::query()->delete();   
        foreach($request->input('beacon') as $beacon){
            unset($beacon['check']);    //변경 필요
            testBeacon::create($beacon);
        }
        return response()->json([
            'message' => "ok",
        ],200);
    }
    
    //비콘 불량 체크
    //존재유무 확인(범수, 주용에게 물어보기)
    public function admin_beacon_defect_check(){
        $beacon_defect = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng', 'beacon_defect_datetime')
                                    ->where('node_defect_check', 1)
                                    ->get();
        return response()->json([
            'beacon_defect' => $beacon_defect,
        ],200);
    }
    
    //minor값으로 비콘 검색
    public function admin_beacon_search(Request $request){
        $beacon_info = testBeacon::select('beacon_id_minor', 'uuid', 'major', 'lat', 'lng', 'node_defect_datetime')
                                    ->where('beacon_id_minor', "LIKE" ,"%{$request->input('beacon_id_minor')}%")
                                    ->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }

    //노드 정보 가져오기
    public function admin_node_setting_main(){
        $node_info = testNode::select('node_id', 'lat', 'lng','floor')->get();
        return response()->json([
            'beacon_info' => $node_info,
        ],200);
    }

    //노드 정보 업데이트 (추가, 삭제)
    public function admin_node_update(Request $request){
        $node_delete_data = []; //삭제할 노드들 id 값 저장
        foreach($request->input('node') as $node){
            //노드와 연결된 진료실 및 검사실 저장
            if($node['room'] != null){
                testNode::find($node['node_id'])->room_location()->create([
                    'room_name' =>  $node['room']
                ]);
            }
            unset($node['room']);
             //삭제할 노드일 경우 배열 저장
            if($node['check'] === "delete")
                array_push($node_delete_data, $node['node_id']);
            //추가할 비콘일 경우 DB저장
            else if($node['check'] === "create"){
                unset($node['check']);
                testNode::create($node);
            }
        }
        //노드 id값으로 삭제
        testNode::destroy($node_delete_data);
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }
    //노드 연결(미구현)
    public function admin_node_link(Request $request){
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }
}
