<?php

namespace App\Console;

use App\Jobs\pullAccountDataOfHospitalId;
use App\Models\HospitalType;
use App\Models\JLAccount;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {
            JLAccount::pullYesterdayAdvertiserPlanData();
        })->dailyAt('00:50');

        $schedule->call(function () {
            $date  = Carbon::today()->toDateString();
            $types = HospitalType::query()
                ->select([
                    'id',
                    'robot'
                ])
                ->get();
            foreach ($types as $type) {
                pullAccountDataOfHospitalId::dispatch($type['id'], $date, !!$type['robot'])
                    ->onQueue('test');
            }
        })->everyThirtyMinutes();

    }

    /**2020_09_20_213334_change_account_table_add_limit_cost_field
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
