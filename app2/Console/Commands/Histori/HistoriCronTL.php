<?php

namespace App\Console\Commands\Histori;

use Illuminate\Console\Command;
use App\MasterObat;
use App\MasterGolonganObat;
use App\MasterPenandaanObat;
use App\MasterProdusen;
use App\MasterSatuan;
use App\MasterApotek;
use App\TransaksiPembelian;
use App\TransaksiPembelianDetail;
use App\TransaksiTO;
use App\TransaksiTODetail;
use App\PenyesuaianStok;
use App\MasterJenisTransaksi;
use App\MasterSuplier;
use App\TransaksiPenjualan;
use App\TransaksiPenjualanDetail;
use App\TransaksiPODetail;
use App\TransaksiPO;
use App\TransaksiTD;
use App\TransaksiTDDetail;
use DB;

class HistoriCronTL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'historitl:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
    }
}