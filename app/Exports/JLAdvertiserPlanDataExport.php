<?php

namespace App\Exports;

use App\Models\HospitalType;
use App\Models\JLAccount;
use App\Models\JLAdvertiserPlanData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class JLAdvertiserPlanDataExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    protected $headings = [
        '时间',
        '广告组id',
        '广告组',
        '展现数',
        '点击数',
        '点击率(%)',
        '平均点击单价(元)',
        '平均千次展现费用(元)',
        '消耗',
        '消耗(实)',
        '转化数',
        '转化成本',
        '转化率',
        '深度转化次数',
        '深度转化成本',
        '深度转化率',
    ];

    protected $selects = [
        'stat_datetime',
        'ad_id',
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
        'deep_convert',
        'deep_convert_cost',
        'deep_convert_rate',
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
        $this->data = $data;
        $dates      = $data['dates'];
        $hospitalId = $data['hospital_id'];

        $this->query = JLAdvertiserPlanData::query()
            ->with(['accountData'])
            ->select($this->selects)
            ->whereBetween('stat_datetime', $dates)
            ->whereHas('accountData', function ($query) use ($hospitalId) {
                if ($hospitalId) {
                    $query->where('hospital_id', $hospitalId);
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
                data_get($item, 'ad_id'),
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
                data_get($item, 'deep_convert'),
                data_get($item, 'deep_convert_cost'),
                data_get($item, 'deep_convert_rate'),
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
        $id           = $this->data['hospital_id'];
        $hospitalType = HospitalType::find($id);
        $name         = $hospitalType->hospital_name;

        return "{$dates[0]}_{$dates[1]}_[{$name}].xlsx";

    }
}
