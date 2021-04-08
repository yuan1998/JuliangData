<?php

namespace App\Jobs;

use App\Models\AccountDataLog;
use App\Models\JLAccount;
use App\Models\JLAdvertiserPlanData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class pullAccountDataOfHospitalId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $hospitalId;
    private $dateString;
    /**
     * @var bool
     */
    private $sendData;

    /**
     * Create a new job instance.
     *
     * @param      $hospitalId
     * @param      $dateString
     * @param bool $sendData
     */
    public function __construct($hospitalId, $dateString, $sendData = false)
    {
        $this->hospitalId = $hospitalId;
        $this->dateString = $dateString;
        $this->sendData   = $sendData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('执行拉取数据');
        $accountList = JLAccount::getHospitalAccount($this->hospitalId, true);

        JLAdvertiserPlanData::concurrentAccountData($accountList, $this->dateString);
        AccountDataLog::logTodayAccountData();
        AccountDataLog::makeLogData($this->dateString, function ($query) {
            $query->where('hospital_id', $this->hospitalId);
        });

        if ($this->sendData) {
            AccountDataLog::sendAccountToRobot($this->dateString, $this->hospitalId);
        }
    }
}
