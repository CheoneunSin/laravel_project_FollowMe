<?php

namespace App\Http\Controllers;
//모델
use App\Flow;
use App\Beacon;
use App\Clinic;
use App\Patient;
use App\Node;

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
        $node_info = Node::all();
        return response()->json([
            'beacon_info' => $node_info,
        ],200);
    }

    //노드 정보 업데이트 (추가, 삭제)
    public function admin_node_update(Request $request){
        Node::query()->delete();   
        foreach($request->input('node') as $node){
            //노드와 연결된 진료실 및 검사실 저장
            if($node['room'] != null){
                Node::find($node['node_id'])->room_location()->create([
                    'room_name' =>  $node['room']
                ]);
            }
            unset($node['room']);
            Node::create($node);
        }
        
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
