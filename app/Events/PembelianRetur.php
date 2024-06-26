<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\TransaksiPembelianDetail;

class PembelianRetur
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $detail_pembelian;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TransaksiPembelianDetail $detail_pembelian)
    {
        $this->detail_pembelian = $detail_pembelian;   
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
