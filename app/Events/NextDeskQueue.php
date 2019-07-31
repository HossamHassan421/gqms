<?php

namespace App\Events;

use App\Desk;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NextDeskQueue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $desk_uuid;
    public $queue_number;
    public $area_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($desk_uuid, $queue_number)
    {
        $this->desk_uuid = $desk_uuid;
        $this->floor_id = Desk::getBy('uuid', $desk_uuid)->area->area_id;
        $this->queue_number = $queue_number;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('desk-queue-screen-'. $this->floor_id);
    }

    /**
     * Custom the payload
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'desk' => $this->desk_uuid,
            'queue' => $this->queue_number
        ];
    }
}
