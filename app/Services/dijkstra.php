<?php

namespace App\Services;

class Dijkstra
{
    // $graph[node1][node2] 연결 된 노드를 2차원 배열 키값으로 사용
    protected $graph;

    //노드에서 다른 노드까지의 거리
    protected $distance;

    //현재 노드의 경로에 있는 이전 노드
    protected $previous;

    //아직 처리되지 않은 노드
    protected $queue;

    /**
     * @param integer[][] $graph
     */
    public function __construct($graph)
    {
        $this->graph = $graph;
    }

    protected function processNextNodeInQueue(array $exclude)
    {
        //가장 가까운 정점 처리
        $closest = array_search(min($this->queue), $this->queue);
        if (!empty($this->graph[$closest]) && !in_array($closest, $exclude)) {
            foreach ($this->graph[$closest] as $neighbor => $cost) {
                if (isset($this->distance[$neighbor])) {
                    if ($this->distance[$closest] + $cost < $this->distance[$neighbor]) {
                        //더 짧은 경로를 찾았을 때
                        $this->distance[$neighbor] = $this->distance[$closest] + $cost;
                        $this->previous[$neighbor] = array($closest);
                        $this->queue[$neighbor] = $this->distance[$neighbor];
                    } elseif ($this->distance[$closest] + $cost === $this->distance[$neighbor]) {
                        // 똑같은 길이의 경로를 찾았을 때
                        $this->previous[$neighbor][] = $closest;
                        $this->queue[$neighbor] = $this->distance[$neighbor];
                    }
                }
            }
        }
        unset($this->queue[$closest]);
    }
    /**
     * $source에서 $target까지의 모든 경로를 노드 배열로 추출
     *
     * @param string $target 시작 노드
     *
     * @return string[][] 각각 노드 목록으로 표시되는 하나 이상의 최단 경로
     */
    protected function extractPaths($target)
    {
        $paths = array(array($target));

        for ($key = 0; isset($paths[$key]); ++$key) {
            $path = $paths[$key];

            if (!empty($this->previous[$path[0]])) {
                foreach ($this->previous[$path[0]] as $previous) {
                    $copy = $path;
                    array_unshift($copy, $previous);
                    $paths[] = $copy;
                }
                unset($paths[$key]);
            }
        }
        return array_values($paths);
    }

    /**
     * $source에서 $target까지 그래프를 통해 최단 경로를 계산
     *
     * @param string   $source  시작 노드
     * @param string   $target  끝 노드
     * @param string[] $exclude 제외 할 노드 목록 - 다음으로 가장 짧은 경로를 계산
     *
     * @return string[][] 0 개 이상의 최단 경로 (각각 노드 목록으로 표시됨)
     */
    public function shortestPaths($source, $target, array $exclude = array())
    {
        // 모든 노드에 대한 최단 거리는 무한대로 초기화
        $this->distance = array_fill_keys(array_keys($this->graph), INF);
        // 시작 노드의 출발점 0 초기화
        $this->distance[$source] = 0;

        // 이전에 방문한 노드 배열 초기화
        $this->previous = array_fill_keys(array_keys($this->graph), array());

        // 모든 노드를 순서대로 처리
        $this->queue = array($source => 0);
        while (!empty($this->queue)) {
            $this->processNextNodeInQueue($exclude);
        }

        if ($source === $target) {
            // A의 경로가 없을 때
            return array(array($source));
        } elseif (empty($this->previous[$target])) {
            //$source와 $target 사이의 경로 없음
            return array();
        } else {
            // $source와 $target 사이에서 하나 이상의 경로가 발견됨
            return $this->extractPaths($target);
        }
    }
}
