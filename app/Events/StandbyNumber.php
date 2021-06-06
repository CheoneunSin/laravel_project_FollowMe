<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class StandbyNumber implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 대기 순번 변경 이벤트 발생 
     *
     * @return void
     */
    public $event;
    public function __construct($event)
    {
        $this->event  = $event;
    }

    /**
     * event 이름 : FollowMe_standby_number
     * event가 브로드캐스트 할 채널을 가져옴
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    // 
    public function broadcastOn()
    {
        return ['FollowMe_standby_number'];
    }

    public function broadcastAs()
  {
      return 'FollowMe_standby_number';
  }
}
