<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Histori\HistoriCronBKL::class, #1
        Commands\Histori\HistoriCronLV::class, #2
        Commands\Histori\HistoriCronPJM::class, #3
        Commands\Histori\HistoriCronPG::class, #4
        //Commands\Histori\HistoriCronTL::class, #5
        Commands\Histori\HistoriCronSG::class, #6
        Commands\Histori\HistoriCronHW::class, #7
        Commands\Histori\HistoriCronCK::class, #8
        Commands\Histori\HistoriCronHO::class, #9
        Commands\Histori\HistoriCronSRJ::class, #10
        Commands\Histori\HistoriCronMG::class, #11
        Commands\Histori\HistoriCronPNT::class, #12
        Commands\Histori\HistoriCronSBG::class, #13
        /*Commands\ReloadHistoriOld::class,*/

        Commands\Defecta\DefectaCronBKL::class, #1
        Commands\Defecta\DefectaCronLV::class, #2
        Commands\Defecta\DefectaCronPJM::class, #3
        Commands\Defecta\DefectaCronPG::class, #4
        //Commands\Defecta\DefectaCronTL::class, #5
        Commands\Defecta\DefectaCronSG::class, #6
        Commands\Defecta\DefectaCronHW::class, #7
        Commands\Defecta\DefectaCronCK::class, #8
        Commands\Defecta\DefectaCronHO::class, #9
        Commands\Defecta\DefectaCronSRJ::class, #10
        Commands\Defecta\DefectaCronMG::class, #11
        Commands\Defecta\DefectaCronPNT::class, #12
        Commands\Defecta\DefectaCronSBG::class, #13
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('historibkl:cron')
                 ->everyMinute();

        $schedule->command('historilv:cron')
                 ->everyMinute();

        $schedule->command('historipjm:cron')
                 ->everyMinute();

        $schedule->command('historipg:cron')
                 ->everyMinute();

        /*$schedule->command('historitl:cron')
                 ->everyMinute();*/

        $schedule->command('historisg:cron')
                 ->everyMinute();

        $schedule->command('historihw:cron')
                 ->everyMinute();

        $schedule->command('historick:cron')
                 ->everyMinute();

        $schedule->command('historiho:cron')
                 ->everyMinute();

        $schedule->command('historisrj:cron')
                 ->everyMinute();

        $schedule->command('historimg:cron')
                 ->everyMinute();

        $schedule->command('historipnt:cron')
                 ->everyMinute();

        $schedule->command('historisbg:cron')
                 ->everyMinute();

        /*$schedule->command('histori_old:cron')
                 ->everyMinute();*/

        /*===============================================*/


        $schedule->command('defectabkl:cron')
                 ->everyMinute();

        $schedule->command('defectalv:cron')
                 ->everyMinute();

        $schedule->command('defectapjm:cron')
                 ->everyMinute();

        $schedule->command('defectapg:cron')
                 ->everyMinute();

        /*$schedule->command('defectatl:cron')
                 ->everyMinute();*/

        $schedule->command('defectasg:cron')
                 ->everyMinute();

        $schedule->command('defectahw:cron')
                 ->everyMinute();

        $schedule->command('defectack:cron')
                 ->everyMinute();

        $schedule->command('defectaho:cron')
                 ->everyMinute();

        $schedule->command('defectasrj:cron')
                 ->everyMinute();

        $schedule->command('defectamg:cron')
                 ->everyMinute();
        
        $schedule->command('defectapnt:cron')
                 ->everyMinute();

        $schedule->command('defectasbg:cron')
                 ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
