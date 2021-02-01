<?php

namespace App\Http\Controllers;
//모델
use App\Flow;
use App\Beacon;
use App\Clinic;
use App\Patient;
use App\Node;
use App\NodeDistance;

//파사드
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use Illuminate\Http\Request;

class FollowMeWebAdminController extends Controller
{   
    //비콘 정보 가져오기
    public function admin_beacon_setting_main(){
        $beacon_info = Beacon::all();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    //비콘 정보 업데이트 (추가 , 삭제)
    public function admin_beacon_update(Request $request){ 
        Beacon::query()->delete();   
        foreach($request->input('beacon') as $beacon){
            Beacon::create($beacon);
        }
        return response()->json([
            'message' => "ok",
        ],200);
    }

    //minor값으로 비콘 검색
    public function admin_beacon_search(Request $request){
        $beacon_info = Beacon::where('beacon_id_minor', "LIKE" ,"%{$request->input('beacon_id_minor')}%")->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }

    //노드 정보 가져오기
    public function admin_node_setting_main(){
        $node_info      = Node::all();
        $node_distance  = NodeDistance::with('node_a_info')->with('node_b_info')->get(); 
        return response()->json([
            'beacon_info'   => $node_info,
            'node_distance' => $node_distance
        ],200);
    }

    //노드 정보 업데이트 (추가, 삭제)
    public function admin_node_update(Request $request){
        // NodeDistance::query()->delete();  //노드 거리 정보 삭제
        Node::query()->delete();   
        foreach($request->input('node') as $node){
            //노드와 연결된 진료실 및 검사실 저장
            if($node['room'] != null)
                Node::findOrFail($node['node_id'])->room_location()->create(['room_name' =>  $node['room'] ]);
            unset($node['room']);
            Node::create($node);
        }
        // //노드 간 거리 데이터 생성
        // foreach($request->input('node_distance') as $node_distance){
        //     Node::create($node);
        // }
        $message = Config::get('constants.admin_message.setting_ok');
        return response()->json([
            'message' => $message,
        ],200);
    }
}
