<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
// start penjualan
use App\Events\PenjualanCreate;
use App\Listeners\UpdateStokPenjualan;
use App\Events\PenjualanRetur;
use App\Listeners\UpdateStokPenjualanRetur;
use App\Events\PenjualanReturBatal;
use App\Listeners\UpdateStokPenjualanReturBatal;

// start pembelian
use App\Events\PembelianCreate;
use App\Listeners\UpdateStokPembelian;
use App\Events\PembelianRetur;
use App\Listeners\UpdateStokPembelianRetur;

use App\Events\ObatCreate;
use App\Listeners\AddObatOutlet;
use App\Events\ObatDelete;
use App\Listeners\NotifyObatDelete;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PenjualanCreate::class => [
            UpdateStokPenjualan::class,
        ],
        PenjualanRetur::class => [
            UpdateStokPenjualanRetur::class,
        ],
        PenjualanReturBatal::class => [
            UpdateStokPenjualanReturBatal::class,
        ],
        PembelianCreate::class => [
            UpdateStokPembelian::class,
        ],
        PembelianRetur::class => [
            UpdateStokPembelianRetur::class,
        ],
        ObatCreate::class => [
            AddObatOutlet::class,
        ],
        ObatDelete::class => [
            NotifyObatDelete::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
