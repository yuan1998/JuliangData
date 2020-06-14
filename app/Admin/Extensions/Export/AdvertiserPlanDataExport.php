<?php

namespace App\Admin\Extensions\Export;

use App\Models\ExportLog;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdvertiserPlanDataExport extends ExcelExporter implements WithMapping, WithTitle, WithHeadings, WithStrictNullComparison
{
    protected $fileName = '广告计划数据.xlsx';

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

    public function query()
    {
        $this->getQuery()->select($this->selects)->with(['accountData']);
        return parent::query();
    }

    public function map($user): array
    {

        return [
            data_get($user, 'stat_datetime'),
            data_get($user, 'ad_id'),
            data_get($user, 'ad_name'),
            data_get($user, 'show'),
            data_get($user, 'click'),
            data_get($user, 'ctr'),
            data_get($user, 'avg_click_cost'),
            data_get($user, 'avg_show_cost'),
            data_get($user, 'cost'),
            data_get($user, 'cost_off'),
            data_get($user, 'attribution_convert'),
            data_get($user, 'attribution_convert_cost'),
            data_get($user, 'convert_rate'),
            data_get($user, 'deep_convert'),
            data_get($user, 'deep_convert_cost'),
            data_get($user, 'deep_convert_rate'),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Say Hi';
    }
}
