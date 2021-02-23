<?php

namespace App\Http\Controllers;
//모델
use App\Flow;
use App\Beacon;
use App\Clinic;
use App\Patient;
use App\Node;
use App\NodeDistance;
use App\RoomLocation;
//파사드
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class FollowMeWebAdminController extends Controller
{   
    //비콘 정보 가져오기
    public function admin_beacon_setting_main(Request $request){
        $beacon_info = Beacon::all();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    //비콘 정보 업데이트 (추가 , 삭제)
    public function admin_beacon_update(Request $request){ 
        Beacon::query()->delete();   
        foreach($request->beacon as $beacon){
            Beacon::create($beacon);
        }
        return response()->json([
            'status' => 'success', 
        ],200);
    }

    //minor값으로 비콘 검색
    public function admin_beacon_search(Request $request){
        $beacon_info = Beacon::select("beacon_id_minor", "uuid", "major", "lat", "lng")
                                ->where('beacon_id_minor', "LIKE" ,"%{$request->beacon_id_minor}%")->get();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    public function admin_beacon_defect_check_main(){
        $beacon_info = Beacon::all();
        return response()->json([
            'beacon_info' => $beacon_info,
        ],200);
    }
    //노드 정보 가져오기
    public function admin_node_setting_main(){
        // $node_info      = Node::all();
        // $node_info = Patient::all();
        $node_distance  = NodeDistance::whereCheck(1)->with('node_a_info')->with('node_b_info')->get();
        // $node_distance  = NodeDistance::with('node_a_info')->with('node_b_info')->get(); 
        return response()->json([
            'node_info'     => $node_info,
            'node_distance' => $node_distance
        ],200);
    }

    //노드 정보 업데이트 (추가, 삭제)
    public function admin_node_update(Request $request){
        if(!empty($request->node_delete)){
            foreach ($request->node_delete as $node){
                Node::findOrFail($node)->node_A_distance()->delete();
                Node::findOrFail($node)->node_B_distance()->delete();
            }
        }
        Node::query()->delete(); 
        $nodes = [];
        foreach($request->node as $node){
            //노드와 연결된 진료실 및 검사실 저장
            if(!empty($node['room_id']) && !empty($node['room']) && !empty($node['room_info']))
                RoomLocation::findOrFail($node['room_id'])->update(['room_name' =>  $node['room'], 'room_node' => $node['node_id'], 'room_info' => $node['room_info']]);
            unset($node['room_id']);
            unset($node['room']);
            unset($node['room_info']);
            $newNode = Node::create($node);
            array_push($nodes, $newNode);
        }
        // Node::findOrFail($request->delete_node)-> ()->delete();
        return response()->json([
            'node'      => $nodes
        ],200);
    }

    //노드 정보 가져오기
    public function admin_node_distance_setting_main(){
        // $node_info = Node::all();
        // $node_info = Patient::all();
        $node_info = Node::all();
        $node_distance_array = [];
        // $node_distances  = NodeDistance::with('node_a_info')->with('node_b_info')->get(); 
        $node_distance  = NodeDistance::whereCheck(1)->with('node_a_info')->with('node_b_info')->get();
        return response()->json([
            'node_info'     => $node_info,
            'node_distance' => $node_distance
        ],200);
    }

    public function admin_node_distance_update(Request $request){
        NodeDistance::query()->delete();  //노드 거리 정보 삭제
        // //노드 간 거리 데이터 생성
        foreach($request->node_distance as $node_distance){
            $node_distance['check'] = 1;
            NodeDistance::create($node_distance);
            NodeDistance::create([
                'node_A' => $node_distance['node_B'],
                'node_B' => $node_distance['node_A'],
                'distance' => $node_distance['distance'],
            ]);
        }
        return response()->json([
            'status' => 'success'
        ],200);
    }
}
