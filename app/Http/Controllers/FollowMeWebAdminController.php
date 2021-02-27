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
use Illuminate\Support\Facades\Auth;

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
        $node_info      = Node::all()->map(function ($info) {
            $info['room_id']    = $info->room_location()->get()->pluck('room_location_id')->first();
            return $info;
         })->all();
        $node_distance  = NodeDistance::whereCheck(1)->with('node_a_info')->with('node_b_info')->get();
        return response()->json([
            'node_info'     => $node_info,
            'node_distance' => $node_distance,
            'room_list'     => RoomLocation::all()
        ],200);
    }

    //노드 정보 업데이트 (추가, 삭제)
    public function admin_node_update(Request $request){
        if(!empty($request->node_delete)){
            foreach ($request->node_delete as $node){
                if(!empty(Node::find($node)->node_A_distance())){
                    Node::find($node)->node_A_distance()->delete();
                    Node::find($node)->node_B_distance()->delete();
                }
            }
        }
        Node::query()->delete(); 
        $nodes = [];
        
        foreach($request->node as $node){
            //노드와 연결된 진료실 및 검사실 저장
            if(!empty($node['room_id'])){
                RoomLocation::find($node['room_id'])->update([
                    'room_node'     => $node['node_id']
                ]);
            }
            unset($node['room_id']);
            $newNode = Node::create($node);
            array_push($nodes, $newNode);
        }
        return response()->json([
            'node'      => $nodes
        ],200);
    }

    //노드 정보 가져오기
    public function admin_node_distance_setting_main(){
        $node_info              = Node::all();
        $node_distance          = NodeDistance::orderBy("node_A")->whereCheck(1)->with('node_a_info')->with('node_b_info')->get();
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
                'node_A'    => $node_distance['node_B'],
                'node_B'    => $node_distance['node_A'],
                'distance'  => $node_distance['distance'],
            ]);
        }
        return response()->json([
            'status' => 'success'
        ],200);
    }

    public function admin_room_info(){
        return response()->json([
            'room_list' => RoomLocation::all()
        ],200);
    }

    public function admin_room_update(Request $request){
        RoomLocation::query()->delete();
        foreach($request->room_list as $room){
            RoomLocation::create($room);
        }
        return response()->json([
            'status' => 'success'
        ],200);
    }
}
