<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\TransaksiPenjualanDetail;

class PenjualanRetur
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $detail_penjualan;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TransaksiPenjualanDetail $detail_penjualan)
    {
        $this->detail_penjualan = $detail_penjualan;  
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
