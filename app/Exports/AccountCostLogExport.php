<?php

namespace App\Exports;

use App\Models\HospitalType;
use App\Models\JLAccount;
use App\Models\JLAdvertiserPlanData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AccountCostLogExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    protected $headings = [
        '账号名称',
        '消费',
        '表单',
        '表单价',
    ];

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
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data)->map(function ($item) {
            return [
                data_get($item , 'name'),
                data_get($item , 'cost'),
                data_get($item , 'convert'),
                data_get($item , 'convert_cost'),
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

}
