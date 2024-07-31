<?php

namespace App\Listeners;

use App\Events\ObatDelete;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyObatDelete
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ObatDelete  $event
     * @return void
     */
    public function handle(ObatDelete $event)
    {
        //
    }
}
