<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class JLAdvertiserPlanData extends Model
{

    protected $fillable = [
        'advertiser_id',
        "active",
        "active_cost",
        "interact_per_cost",
        "active_pay_cost",
        "active_pay_rate",
        "active_rate",
        "active_register_cost",
        "active_register_rate",
        "ad_id",
        "ad_name",
        "advanced_creative_counsel_click",
        "advanced_creative_coupon_addition",
        "advanced_creative_form_click",
        "advanced_creative_phone_click",
        "attribution_convert",
        "attribution_convert_cost",
        "attribution_deep_convert",
        "attribution_deep_convert_cost",
        "attribution_next_day_open_cnt",
        "attribution_next_day_open_cost",
        "attribution_next_day_open_rate",
        "average_play_time_per_play",
        "avg_click_cost",
        "avg_show_cost",
        "button",
        "campaign_id",
        "campaign_name",
        "click",
        "click_install",
        "comment",
        "consult",
        "consult_effective",
        "convert",
        "convert_cost",
        "convert_rate",
        "cost",
        "coupon",
        "coupon_single_page",
        "ctr",
        "deep_convert",
        "deep_convert_cost",
        "deep_convert_rate",
        "download",
        "download_finish",
        "download_finish_cost",
        "download_finish_rate",
        "download_start",
        "download_start_cost",
        "download_start_rate",
        "follow",
        "form",
        "game_addiction",
        "game_addiction_cost",
        "game_addiction_rate",
        "home_visited",
        "ies_challenge_click",
        "ies_music_click",
        "in_app_cart",
        "in_app_detail_uv",
        "in_app_order",
        "in_app_pay",
        "in_app_uv",
        "install_finish",
        "install_finish_cost",
        "install_finish_rate",
        "like",
        "loan_completion",
        "loan_completion_cost",
        "loan_completion_rate",
        "loan_credit",
        "loan_credit_cost",
        "loan_credit_rate",
        "location_click",
        "lottery",
        "map_search",
        "message",
        "next_day_open",
        "next_day_open_cost",
        "next_day_open_rate",
        "pay_count",
        "phone",
        "phone_confirm",
        "phone_connect",
        "play_25_feed_break",
        "play_50_feed_break",
        "play_75_feed_break",
        "play_100_feed_break",
        "play_duration_sum",
        "play_over_rate",
        "pre_loan_credit",
        "pre_loan_credit_cost",
        "qq",
        "redirect",
        "register",
        "share",
        "shopping",
        "show",
        "stat_datetime",
        "total_play",
        "valid_play",
        "valid_play_cost",
        "valid_play_rate",
        "view",
        "vote",
        "wechat",
        "wifi_play",
        "wifi_play_rate",
    ];
    public $timestamps = false;

    public static $fields = [
        "active"                            => ["type" => "number", "comment" => "应用下载广告数据-激活数"],
        "active_cost"                       => ["type" => "float", "comment" => "应用下载广告数据-激活成本"],
        "interact_per_cost"                 => ["type" => "float", "comment" => "互动成本"],
        "active_pay_cost"                   => ["type" => "float", "comment" => "应用下载广告数据-付费成本"],
        "active_pay_rate"                   => ["type" => "float", "comment" => "应用下载广告数据-付费率"],
        "active_rate"                       => ["type" => "float", "comment" => "应用下载广告数据-激活率"],
        "active_register_cost"              => ["type" => "float", "comment" => "应用下载广告数据-注册成本"],
        "active_register_rate"              => ["type" => "float", "comment" => "应用下载广告数据-注册率"],
        "ad_id"                             => ["type" => "string", "comment" => "计划id"],
        "ad_name"                           => ["type" => "string", "comment" => "计划name"],
        "advanced_creative_counsel_click"   => ["type" => "number", "comment" => "附加创意-附加创意在线咨询点击"],
        "advanced_creative_coupon_addition" => ["type" => "number", "comment" => "附加创意-附加创意卡券领取"],
        "advanced_creative_form_click"      => ["type" => "number", "comment" => "附加创意-附加创意表单按钮点击"],
        "advanced_creative_phone_click"     => ["type" => "number", "comment" => "附加创意-附加创意电话按钮点击"],
        "attribution_convert"               => ["type" => "number", "comment" => "转化数据（计费时间）-转化数（计费时间）"],
        "attribution_convert_cost"          => ["type" => "float", "comment" => "转化数据（计费时间）-转化成本（计费时间）"],
        "attribution_deep_convert"          => ["type" => "number", "comment" => "转化数据（计费时间）-深度转化数（计费时间）"],
        "attribution_deep_convert_cost"     => ["type" => "float", "comment" => "转化数据（计费时间）-深度转化成本（计费时间）"],
        "attribution_next_day_open_cnt"     => ["type" => "number", "comment" => "应用下载广告数据-次留数"],
        "attribution_next_day_open_cost"    => ["type" => "number", "comment" => "应用下载广告数据-次留成本"],
        "attribution_next_day_open_rate"    => ["type" => "number", "comment" => "应用下载广告数据-次留率"],
        "average_play_time_per_play"        => ["type" => "float", "comment" => "视频数据-平均单次播放时长"],
        "avg_click_cost"                    => ["type" => "float", "comment" => "展现数据-平均点击单价"],
        "avg_show_cost"                     => ["type" => "float", "comment" => "展现数据-平均千次展现费用"],
        "button"                            => ["type" => "number", "comment" => "落地页转化数据-按钮button"],
        "campaign_id"                       => ["type" => "string", "comment" => "广告组id"],
        "campaign_name"                     => ["type" => "string", "comment" => "广告组name"],
        "click"                             => ["type" => "number", "comment" => "展现数据-点击数"],
        "click_install"                     => ["type" => "number", "comment" => "应用下载广告数据-点击安装数"],
        "comment"                           => ["type" => "number", "comment" => "互动数据-评论数"],
        "consult"                           => ["type" => "number", "comment" => "落地页转化数据-在线咨询"],
        "consult_effective"                 => ["type" => "number", "comment" => "落地页转化数据-有效咨询"],
        "convert"                           => ["type" => "number", "comment" => "转化数据-转化数"],
        "convert_cost"                      => ["type" => "float", "comment" => "转化数据-转化成本"],
        "convert_rate"                      => ["type" => "float", "comment" => "转化数据-转化率"],
        "cost"                              => ["type" => "float", "comment" => "展现数据-总花费"],
        "coupon"                            => ["type" => "number", "comment" => "落地页转化数据-建站卡券领取"],
        "coupon_single_page"                => ["type" => "number", "comment" => "落地页转化数据-卡券页领取"],
        "ctr"                               => ["type" => "float", "comment" => "展现数据-点击率"],
        "deep_convert"                      => ["type" => "number", "comment" => "转化数据-深度转化数"],
        "deep_convert_cost"                 => ["type" => "float", "comment" => "转化数据-深度转化成本"],
        "deep_convert_rate"                 => ["type" => "float", "comment" => "转化数据-深度转化率"],
        "download"                          => ["type" => "number", "comment" => "落地页转化数据-下载开始"],
        "download_finish"                   => ["type" => "number", "comment" => "应用下载广告数据-安卓下载完成数"],
        "download_finish_cost"              => ["type" => "float", "comment" => "应用下载广告数据-安卓下载完成成本"],
        "download_finish_rate"              => ["type" => "float", "comment" => "应用下载广告数据-安卓下载完成率"],
        "download_start"                    => ["type" => "number", "comment" => "应用下载广告数据-安卓下载开始数"],
        "download_start_cost"               => ["type" => "float", "comment" => "应用下载广告数据-安卓下载开始成本"],
        "download_start_rate"               => ["type" => "float", "comment" => "应用下载广告数据-安卓下载开始率"],
        "follow"                            => ["type" => "number", "comment" => "互动数据-新增关注数"],
        "form"                              => ["type" => "number", "comment" => "落地页转化数据-表单提交"],
        "game_addiction"                    => ["type" => "number", "comment" => "应用下载广告数据-关键行为数"],
        "game_addiction_cost"               => ["type" => "float", "comment" => "应用下载广告数据-关键行为成本"],
        "game_addiction_rate"               => ["type" => "float", "comment" => "应用下载广告数据-关键行为率"],
        "home_visited"                      => ["type" => "number", "comment" => "互动数据-主页访问量"],
        "ies_challenge_click"               => ["type" => "number", "comment" => "互动数据-挑战赛查看数"],
        "ies_music_click"                   => ["type" => "number", "comment" => "互动数据-音乐查看数"],
        "in_app_cart"                       => ["type" => "number", "comment" => "应用下载广告数据-APP内加入购物车"],
        "in_app_detail_uv"                  => ["type" => "number", "comment" => "应用下载广告数据-APP内访问详情页"],
        "in_app_order"                      => ["type" => "number", "comment" => "应用下载广告数据-APP内下单"],
        "in_app_pay"                        => ["type" => "number", "comment" => "应用下载广告数据-APP内付费"],
        "in_app_uv"                         => ["type" => "number", "comment" => "应用下载广告数据-APP内访问"],
        "install_finish"                    => ["type" => "number", "comment" => "应用下载广告数据-安卓安装完成数"],
        "install_finish_cost"               => ["type" => "float", "comment" => "应用下载广告数据-安卓安装完成成本"],
        "install_finish_rate"               => ["type" => "float", "comment" => "应用下载广告数据-安卓安装完成率"],
        "like"                              => ["type" => "number", "comment" => "互动数据-点赞数"],
        "loan_completion"                   => ["type" => "number", "comment" => "应用下载广告数据-完件数"],
        "loan_completion_cost"              => ["type" => "float", "comment" => "应用下载广告数据-完件成本"],
        "loan_completion_rate"              => ["type" => "float", "comment" => "应用下载广告数据-完件率"],
        "loan_credit"                       => ["type" => "number", "comment" => "应用下载广告数据-授信数"],
        "loan_credit_cost"                  => ["type" => "float", "comment" => "应用下载广告数据-授信成本"],
        "loan_credit_rate"                  => ["type" => "float", "comment" => "应用下载广告数据-授信率"],
        "location_click"                    => ["type" => "number", "comment" => "互动数据-POI点击数"],
        "lottery"                           => ["type" => "number", "comment" => "落地页转化数据-抽奖"],
        "map_search"                        => ["type" => "number", "comment" => "落地页转化数据-地图搜索"],
        "message"                           => ["type" => "number", "comment" => "落地页转化数据-短信咨询"],
        "next_day_open"                     => ["type" => "number", "comment" => "应用下载广告数据-次留（未对回）"],
        "next_day_open_cost"                => ["type" => "float", "comment" => "应用下载广告数据-次留成本（未对回）"],
        "next_day_open_rate"                => ["type" => "float", "comment" => "应用下载广告数据-次留率（未对回)"],
        "pay_count"                         => ["type" => "number", "comment" => "应用下载广告数据-付费次数"],
        "phone"                             => ["type" => "number", "comment" => "落地页转化数据-点击电话按钮"],
        "phone_confirm"                     => ["type" => "number", "comment" => "落地页转化数据-智能电话-确认拨打"],
        "phone_connect"                     => ["type" => "number", "comment" => "落地页转化数据-智能电话-确认接通"],
        "play_25_feed_break"                => ["type" => "number", "comment" => "视频数据-25%进度播放数"],
        "play_50_feed_break"                => ["type" => "number", "comment" => "视频数据-50%进度播放数"],
        "play_75_feed_break"                => ["type" => "number", "comment" => "视频数据-75%进度播放数"],
        "play_100_feed_break"               => ["type" => "number", "comment" => "视频数据-99%进度播放数"],
        "play_duration_sum"                 => ["type" => "number", "comment" => "视频数据-播放时长，单位ms"],
        "play_over_rate"                    => ["type" => "float", "comment" => "视频数据-播完率"],
        "pre_loan_credit"                   => ["type" => "number", "comment" => "应用下载广告数据-预授信数"],
        "pre_loan_credit_cost"              => ["type" => "float", "comment" => "应用下载广告数据-预授信成本"],
        "qq"                                => ["type" => "number", "comment" => "落地页转化数据-QQ咨询"],
        "redirect"                          => ["type" => "number", "comment" => "落地页转化数据-页面跳转"],
        "register"                          => ["type" => "number", "comment" => "应用下载广告数据-注册数"],
        "share"                             => ["type" => "number", "comment" => "互动数据-分享数"],
        "shopping"                          => ["type" => "number", "comment" => "落地页转化数据-商品购买"],
        "show"                              => ["type" => "number", "comment" => "展现数据-展示数"],
        "stat_datetime"                     => ["type" => "datetime", "comment" => "数据起始时间"],
        "total_play"                        => ["type" => "number", "comment" => "视频数据-播放数"],
        "valid_play"                        => ["type" => "number", "comment" => "视频数据-有效播放数"],
        "valid_play_cost"                   => ["type" => "float", "comment" => "视频数据-有效播放成本"],
        "valid_play_rate"                   => ["type" => "float", "comment" => "视频数据-有效播放率"],
        "view"                              => ["type" => "number", "comment" => "落地页转化数据-关键页面浏览"],
        "vote"                              => ["type" => "number", "comment" => "落地页转化数据-投票"],
        "wechat"                            => ["type" => "number", "comment" => "落地页转化数据-微信复制"],
        "wifi_play"                         => ["type" => "number", "comment" => "视频数据-WiFi播放量"],
        "wifi_play_rate"                    => ["type" => "float", "comment" => "视频数据-WiFi播放占比"],
    ];


    public static $displayFields = [
        'stat_datetime'            => '时间',
        'ad_name'                  => '广告计划',
        'show'                     => '展现数',
        'click'                    => '点击数',
        'cost'                     => '消耗(虚)',
        'ctr'                      => '点击率',
        'avg_click_cost'           => '平均点击单价',
        'avg_show_cost'            => '平均千次展现费用',
        'attribution_convert'      => '转化数',
        'attribution_convert_cost' => '转化成本',
        'convert_rate'             => '转化率',
    ];

    public function accountData()
    {
        return $this->belongsTo(JLAccount::class, 'advertiser_id', 'advertiser_id');
    }

    public function getCostOffAttribute()
    {
        $accountData = $this->accountData;
        if (!$accountData) return '无关联账户';

        $val    = $this->cost;
        $rebate = ($accountData['rebate'] + 100) / 100;
        return round($val / $rebate, 3);
    }


    public static function allAccountGetData($hospitalId, $dates)
    {
        $query = JLAccount::query()
            ->where('status', 'enable')
            ->where('hospital_id', $hospitalId);

        $accountData = $query->get();

        $successCount = 0;
        $errorCount   = 0;
        $logs         = [];
        foreach ($accountData as $account) {
            dateRangeForEach($dates, function ($str) use ($account, &$successCount, &$errorCount, &$logs) {
                $dateString = $str->toDateString();
                $result     = $account->getAdvertiserPlanData($dateString, $dateString);

                if (Arr::get($result, 'code') == 0) {
                    $successCount++;
                } else {
                    $errorCount++;
                    array_push($logs, $result);
                }
            });
        }
        return [
            'logs'         => $logs,
            'successCount' => $successCount,
            'errorCount'   => $errorCount,
        ];
    }
}
