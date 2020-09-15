<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\PullAdvertiserPlanDataAction;
use App\Admin\Extensions\Export\AdvertiserPlanDataExport;
use App\Models\HospitalType;
use App\Models\JLAccount;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\JLAdvertiserPlanData;
use Illuminate\Support\Arr;

class JLAdvertiserPlanDataController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '巨量广告计划数据';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        initVue();
        disableAutocomplete();
        $grid      = new Grid(new JLAdvertiserPlanData());
        $keys      = array_keys(JLAdvertiserPlanData::$displayFields);
        $yesterday = Carbon::today()->addDays(-1)->toDateString();


        $model = $grid->model();
        $model->select(array_merge($keys, ['advertiser_id', 'account_id', 'rebate_cost', 'id']))
            ->with(['accountData'])
            ->adminUserHospital()
            ->orderBy('stat_datetime', 'desc');
        $grid->disableCreateButton();
        $grid->exporter(new AdvertiserPlanDataExport());

        if (!request()->get('stat_datetime')) {
            $model->whereIn('stat_datetime', [$yesterday, $yesterday]);
        }

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new PullAdvertiserPlanDataAction());
        });

        $grid->filter(function ($filter) use ($yesterday) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->column(1 / 2, function (Grid\Filter $filter) use ($yesterday) {
                $filter->between('stat_datetime', '时间')
                    ->date()
                    ->default(['start' => $yesterday, 'end' => $yesterday]);
            });
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $options = \Encore\Admin\Facades\Admin::user()->hospital_list->pluck('hospital_name', 'id');

                $filter->equal('hospital_id', '医院类型')
                    ->select($options);

                $accounts = JLAccount::query()
                    ->adminUserHospital()
                    ->get()
                    ->pluck('comment_name', 'id');

                $filter->in('account_id', '账户列表')->multipleSelect($accounts);

            });
        });

        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableActions();

        $grid->fixColumns(2);
        foreach (JLAdvertiserPlanData::$displayFields as $field => $fieldValue) {
            $fieldText = $fieldValue['title'];

            $column = $grid->column($field, $fieldText);
            if (Arr::get($fieldValue, 'total', false)) {
                $column->totalRow(Arr::get($fieldValue, 'totalRaw', null));
            }

            if ($field == 'cost') {
                $grid->column('cost_off', '消耗(实)')->display(function () {
                    return $this->cost_off;
                });
            }

        }

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(JLAdvertiserPlanData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('active', __('Active'));
        $show->field('active_cost', __('Active cost'));
        $show->field('interact_per_cost', __('Interact per cost'));
        $show->field('active_pay_cost', __('Active pay cost'));
        $show->field('active_pay_rate', __('Active pay rate'));
        $show->field('active_rate', __('Active rate'));
        $show->field('active_register_cost', __('Active register cost'));
        $show->field('active_register_rate', __('Active register rate'));
        $show->field('ad_id', __('Ad id'));
        $show->field('ad_name', __('Ad name'));
        $show->field('advanced_creative_counsel_click', __('Advanced creative counsel click'));
        $show->field('advanced_creative_coupon_addition', __('Advanced creative coupon addition'));
        $show->field('advanced_creative_form_click', __('Advanced creative form click'));
        $show->field('advanced_creative_phone_click', __('Advanced creative phone click'));
        $show->field('attribution_convert', __('Attribution convert'));
        $show->field('attribution_convert_cost', __('Attribution convert cost'));
        $show->field('attribution_deep_convert', __('Attribution deep convert'));
        $show->field('attribution_deep_convert_cost', __('Attribution deep convert cost'));
        $show->field('attribution_next_day_open_cnt', __('Attribution next day open cnt'));
        $show->field('attribution_next_day_open_cost', __('Attribution next day open cost'));
        $show->field('attribution_next_day_open_rate', __('Attribution next day open rate'));
        $show->field('average_play_time_per_play', __('Average play time per play'));
        $show->field('avg_click_cost', __('Avg click cost'));
        $show->field('avg_show_cost', __('Avg show cost'));
        $show->field('button', __('Button'));
        $show->field('campaign_id', __('Campaign id'));
        $show->field('campaign_name', __('Campaign name'));
        $show->field('click', __('Click'));
        $show->field('click_install', __('Click install'));
        $show->field('comment', __('Comment'));
        $show->field('consult', __('Consult'));
        $show->field('consult_effective', __('Consult effective'));
        $show->field('convert', __('Convert'));
        $show->field('convert_cost', __('Convert cost'));
        $show->field('convert_rate', __('Convert rate'));
        $show->field('cost', __('Cost'));
        $show->field('coupon', __('Coupon'));
        $show->field('coupon_single_page', __('Coupon single page'));
        $show->field('ctr', __('Ctr'));
        $show->field('deep_convert', __('Deep convert'));
        $show->field('deep_convert_cost', __('Deep convert cost'));
        $show->field('deep_convert_rate', __('Deep convert rate'));
        $show->field('download', __('Download'));
        $show->field('download_finish', __('Download finish'));
        $show->field('download_finish_cost', __('Download finish cost'));
        $show->field('download_finish_rate', __('Download finish rate'));
        $show->field('download_start', __('Download start'));
        $show->field('download_start_cost', __('Download start cost'));
        $show->field('download_start_rate', __('Download start rate'));
        $show->field('follow', __('Follow'));
        $show->field('form', __('Form'));
        $show->field('game_addiction', __('Game addiction'));
        $show->field('game_addiction_cost', __('Game addiction cost'));
        $show->field('game_addiction_rate', __('Game addiction rate'));
        $show->field('home_visited', __('Home visited'));
        $show->field('ies_challenge_click', __('Ies challenge click'));
        $show->field('ies_music_click', __('Ies music click'));
        $show->field('in_app_cart', __('In app cart'));
        $show->field('in_app_detail_uv', __('In app detail uv'));
        $show->field('in_app_order', __('In app order'));
        $show->field('in_app_pay', __('In app pay'));
        $show->field('in_app_uv', __('In app uv'));
        $show->field('install_finish', __('Install finish'));
        $show->field('install_finish_cost', __('Install finish cost'));
        $show->field('install_finish_rate', __('Install finish rate'));
        $show->field('like', __('Like'));
        $show->field('loan_completion', __('Loan completion'));
        $show->field('loan_completion_cost', __('Loan completion cost'));
        $show->field('loan_completion_rate', __('Loan completion rate'));
        $show->field('loan_credit', __('Loan credit'));
        $show->field('loan_credit_cost', __('Loan credit cost'));
        $show->field('loan_credit_rate', __('Loan credit rate'));
        $show->field('location_click', __('Location click'));
        $show->field('lottery', __('Lottery'));
        $show->field('map_search', __('Map search'));
        $show->field('message', __('Message'));
        $show->field('next_day_open', __('Next day open'));
        $show->field('next_day_open_cost', __('Next day open cost'));
        $show->field('next_day_open_rate', __('Next day open rate'));
        $show->field('pay_count', __('Pay count'));
        $show->field('phone', __('Phone'));
        $show->field('phone_confirm', __('Phone confirm'));
        $show->field('phone_connect', __('Phone connect'));
        $show->field('play_25_feed_break', __('Play 25 feed break'));
        $show->field('play_50_feed_break', __('Play 50 feed break'));
        $show->field('play_75_feed_break', __('Play 75 feed break'));
        $show->field('play_100_feed_break', __('Play 100 feed break'));
        $show->field('play_duration_sum', __('Play duration sum'));
        $show->field('play_over_rate', __('Play over rate'));
        $show->field('pre_loan_credit', __('Pre loan credit'));
        $show->field('pre_loan_credit_cost', __('Pre loan credit cost'));
        $show->field('qq', __('Qq'));
        $show->field('redirect', __('Redirect'));
        $show->field('register', __('Register'));
        $show->field('share', __('Share'));
        $show->field('shopping', __('Shopping'));
        $show->field('show', __('Show'));
        $show->field('stat_datetime', __('Stat datetime'));
        $show->field('total_play', __('Total play'));
        $show->field('valid_play', __('Valid play'));
        $show->field('valid_play_cost', __('Valid play cost'));
        $show->field('valid_play_rate', __('Valid play rate'));
        $show->field('view', __('View'));
        $show->field('vote', __('Vote'));
        $show->field('wechat', __('Wechat'));
        $show->field('wifi_play', __('Wifi play'));
        $show->field('wifi_play_rate', __('Wifi play rate'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new JLAdvertiserPlanData());

        $form->number('active', __('Active'));
        $form->decimal('active_cost', __('Active cost'));
        $form->decimal('interact_per_cost', __('Interact per cost'));
        $form->decimal('active_pay_cost', __('Active pay cost'));
        $form->decimal('active_pay_rate', __('Active pay rate'));
        $form->decimal('active_rate', __('Active rate'));
        $form->decimal('active_register_cost', __('Active register cost'));
        $form->decimal('active_register_rate', __('Active register rate'));
        $form->text('ad_id', __('Ad id'));
        $form->text('ad_name', __('Ad name'));
        $form->number('advanced_creative_counsel_click', __('Advanced creative counsel click'));
        $form->number('advanced_creative_coupon_addition', __('Advanced creative coupon addition'));
        $form->number('advanced_creative_form_click', __('Advanced creative form click'));
        $form->number('advanced_creative_phone_click', __('Advanced creative phone click'));
        $form->number('attribution_convert', __('Attribution convert'));
        $form->decimal('attribution_convert_cost', __('Attribution convert cost'));
        $form->number('attribution_deep_convert', __('Attribution deep convert'));
        $form->decimal('attribution_deep_convert_cost', __('Attribution deep convert cost'));
        $form->number('attribution_next_day_open_cnt', __('Attribution next day open cnt'));
        $form->number('attribution_next_day_open_cost', __('Attribution next day open cost'));
        $form->number('attribution_next_day_open_rate', __('Attribution next day open rate'));
        $form->decimal('average_play_time_per_play', __('Average play time per play'));
        $form->decimal('avg_click_cost', __('Avg click cost'));
        $form->decimal('avg_show_cost', __('Avg show cost'));
        $form->number('button', __('Button'));
        $form->text('campaign_id', __('Campaign id'));
        $form->text('campaign_name', __('Campaign name'));
        $form->number('click', __('Click'));
        $form->number('click_install', __('Click install'));
        $form->number('comment', __('Comment'));
        $form->number('consult', __('Consult'));
        $form->number('consult_effective', __('Consult effective'));
        $form->number('convert', __('Convert'));
        $form->decimal('convert_cost', __('Convert cost'));
        $form->decimal('convert_rate', __('Convert rate'));
        $form->decimal('cost', __('Cost'));
        $form->number('coupon', __('Coupon'));
        $form->number('coupon_single_page', __('Coupon single page'));
        $form->decimal('ctr', __('Ctr'));
        $form->number('deep_convert', __('Deep convert'));
        $form->decimal('deep_convert_cost', __('Deep convert cost'));
        $form->decimal('deep_convert_rate', __('Deep convert rate'));
        $form->number('download', __('Download'));
        $form->number('download_finish', __('Download finish'));
        $form->decimal('download_finish_cost', __('Download finish cost'));
        $form->decimal('download_finish_rate', __('Download finish rate'));
        $form->number('download_start', __('Download start'));
        $form->decimal('download_start_cost', __('Download start cost'));
        $form->decimal('download_start_rate', __('Download start rate'));
        $form->number('follow', __('Follow'));
        $form->number('form', __('Form'));
        $form->number('game_addiction', __('Game addiction'));
        $form->decimal('game_addiction_cost', __('Game addiction cost'));
        $form->decimal('game_addiction_rate', __('Game addiction rate'));
        $form->number('home_visited', __('Home visited'));
        $form->number('ies_challenge_click', __('Ies challenge click'));
        $form->number('ies_music_click', __('Ies music click'));
        $form->number('in_app_cart', __('In app cart'));
        $form->number('in_app_detail_uv', __('In app detail uv'));
        $form->number('in_app_order', __('In app order'));
        $form->number('in_app_pay', __('In app pay'));
        $form->number('in_app_uv', __('In app uv'));
        $form->number('install_finish', __('Install finish'));
        $form->decimal('install_finish_cost', __('Install finish cost'));
        $form->decimal('install_finish_rate', __('Install finish rate'));
        $form->number('like', __('Like'));
        $form->number('loan_completion', __('Loan completion'));
        $form->decimal('loan_completion_cost', __('Loan completion cost'));
        $form->decimal('loan_completion_rate', __('Loan completion rate'));
        $form->number('loan_credit', __('Loan credit'));
        $form->decimal('loan_credit_cost', __('Loan credit cost'));
        $form->decimal('loan_credit_rate', __('Loan credit rate'));
        $form->number('location_click', __('Location click'));
        $form->number('lottery', __('Lottery'));
        $form->number('map_search', __('Map search'));
        $form->number('message', __('Message'));
        $form->number('next_day_open', __('Next day open'));
        $form->decimal('next_day_open_cost', __('Next day open cost'));
        $form->decimal('next_day_open_rate', __('Next day open rate'));
        $form->number('pay_count', __('Pay count'));
        $form->number('phone', __('Phone'));
        $form->number('phone_confirm', __('Phone confirm'));
        $form->number('phone_connect', __('Phone connect'));
        $form->number('play_25_feed_break', __('Play 25 feed break'));
        $form->number('play_50_feed_break', __('Play 50 feed break'));
        $form->number('play_75_feed_break', __('Play 75 feed break'));
        $form->number('play_100_feed_break', __('Play 100 feed break'));
        $form->number('play_duration_sum', __('Play duration sum'));
        $form->decimal('play_over_rate', __('Play over rate'));
        $form->number('pre_loan_credit', __('Pre loan credit'));
        $form->decimal('pre_loan_credit_cost', __('Pre loan credit cost'));
        $form->number('qq', __('Qq'));
        $form->number('redirect', __('Redirect'));
        $form->number('register', __('Register'));
        $form->number('share', __('Share'));
        $form->number('shopping', __('Shopping'));
        $form->number('show', __('Show'));
        $form->datetime('stat_datetime', __('Stat datetime'))->default(date('Y-m-d H:i:s'));
        $form->number('total_play', __('Total play'));
        $form->number('valid_play', __('Valid play'));
        $form->decimal('valid_play_cost', __('Valid play cost'));
        $form->decimal('valid_play_rate', __('Valid play rate'));
        $form->number('view', __('View'));
        $form->number('vote', __('Vote'));
        $form->number('wechat', __('Wechat'));
        $form->number('wifi_play', __('Wifi play'));
        $form->decimal('wifi_play_rate', __('Wifi play rate'));

        return $form;
    }
}
