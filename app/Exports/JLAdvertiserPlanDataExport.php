<?php

namespace App\Exports;

use App\Models\JLAccount;
use App\Models\JLAdvertiserPlanData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class JLAdvertiserPlanDataExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    protected $headings = [
        '时间',
        '广告计划',
        '展现数',
        '点击数',
        '点击率',
        '平均点击单价',
        '平均千次展现费用',
        '消耗(虚)',
        '消耗(实)',
        '转化数',
        '转化成本',
        '转化率',
    ];

    protected $selects = [
        'stat_datetime',
        'ad_name',
        'show',
        'click',
        'ctr',
        'avg_click_cost',
        'avg_show_cost',
        'cost',
        'attribution_convert',
        'attribution_convert_cost',
        'convert_rate',
        'advertiser_id',
    ];
    /**
     * @var \Illuminate\Database\Eloquent\Builder|static
     */
    protected $query;
    /**
     * @var array
     */
    protected $data;

    /**
     * JLAdvertiserPlanDataExport constructor.
     * @param $data array
     */
    public function __construct($data)
    {
        $this->data   = $data;
        $dates        = $data['dates'];
        $accountType  = $data['account_type'];
        $hospitalType = $data['hospital_type'];

        $this->query = JLAdvertiserPlanData::query()
            ->with(['accountData'])
            ->select($this->selects)
            ->whereBetween('stat_datetime', $dates)
            ->whereHas('accountData', function ($query) use ($accountType, $hospitalType) {
                if ($accountType) {
                    $query->where('account_type', $accountType);
                }
                if ($hospitalType) {
                    $query->where('hospital_type', $hospitalType);
                }
            });


    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->query->get()->map(function ($item) {
            return [
                data_get($item, 'stat_datetime'),
                data_get($item, 'ad_name'),
                data_get($item, 'show'),
                data_get($item, 'click'),
                data_get($item, 'ctr'),
                data_get($item, 'avg_click_cost'),
                data_get($item, 'avg_show_cost'),
                data_get($item, 'cost'),
                data_get($item, 'cost_off'),
                data_get($item, 'attribution_convert'),
                data_get($item, 'attribution_convert_cost'),
                data_get($item, 'convert_rate'),
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

    public function makeName(): string
    {
        $dates        = $this->data['dates'];
        $accountType  = JLAccount::$accountTypeList[$this->data['account_type']];
        $hospitalType = JLAccount::$hospitalTypeList[$this->data['hospital_type']];

        return "{$dates[0]}_{$dates[1]}_[{$accountType}_{$hospitalType}].xlsx";

    }
}
