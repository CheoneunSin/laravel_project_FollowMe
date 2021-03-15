<?php

namespace App\Services;
use App\Node;
use App\NodeDistance;
use App\Flow;
use App\Services\Dijkstra;                          
use Illuminate\Support\Facades\DB;

class ShortestPath
{   
    protected $start_node;  
    protected $end_node; 
    public function node_flow_shortest_path_set($start_node, $end_flow){
        $this->start_node   = $start_node;
        $this->end_node     = Flow::findOrFail($end_flow)->room_location->room_node;
    }
    
    //진료 동선 안내 API 출발지 도착지 설정 
    public function flow_shortest_path_set($start_flow, $end_flow)
    {
        $this->start_node = Flow::findOrFail($start_flow)->room_location->room_node;
        $this->end_node   = Flow::findOrFail($end_flow)->room_location->room_node;
    }
    //검색을 통한 길안내 API 출발지 도착지 설정
    public function node_shortest_path_set($start_node, $end_node){
        $this->start_node   = $start_node;
        $this->end_node     = $end_node;
    }

    public function node_graph_get(){
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
         return $graph;
    }
    
    public function shortest_path_node(){
        $graph                  = $this->node_graph_get();
        $algorithm              = new Dijkstra($graph);  //다익스타라 알고리즘 적용 
        //출발점 노드와 도착점 노드 사이의 최단 경로 가져오기 
        $start_loaction_node    = $this->start_node;
        $end_loaction_node      = $this->end_node;  

        $path       = $algorithm->shortestPaths($start_loaction_node, $end_loaction_node);    
        //최단 경로에 있는 노드들에 대한 정보를 DB에서 가져와서 nodeFlow 배열에 저장 
        $pathStr    = implode(',', $path[0]);      //WhereIn은 자동 sort되므로 implode 후 FIELD 해야함
        $nodeFlow   = Node::whereIn('node_id', $path[0])->orderByRaw(DB::raw("FIELD(node_id, $pathStr)"))->get();
        return $nodeFlow;
    }
}
