<?php

namespace Xin6841414\EasySms\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExceptionCustomEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $exception;
    public $remark; //自定义说明

    /**
     * Create a new event instance.
     *
     * @param \Exception $e
     * @param string $remark 自定义说明
     */
    public function __construct(\Exception $e, $remark = '')
    {
        $this->exception = $e;
        $this->remark = $remark;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
