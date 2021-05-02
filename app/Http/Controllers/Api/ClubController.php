<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use GuzzleHttp\Client;
use HeadlessChromium\BrowserFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClubController extends Controller
{
    public function sendKst($message, $dp = '')
    {
        $serve = env('REMOTE_CHROME');
        if (!$serve) {
            Log::error('配置的远程端口有误');
            return;
        }
        $options = new ChromeOptions();

        $options->addArguments([
            '--no-sandbox',
            '--window-size=1920,1080',
            '--ignore-certificate-errors'
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        $driver = RemoteWebDriver::create(
            $serve, $capabilities
        );

        try {
            $driver->get('https://vipz2-hzbk2.kuaishang.cn/bs/im.htm?cas=57284___922518&fi=67975&dp=' . $dp . '&sText=xxl_page&vi=&ism=1&cv=' . urlencode($message));

            sleep(5);
        } catch (\Exception $exception) {
            Log::error('获得错误', [
                'message' => $exception->getMessage()
            ]);
        }
        $driver->quit();
    }

    public function post(Request $request)
    {
        $tel = $request->get('leads_tel');
        Log::info('1. 广点通接受数据 ', [
            $tel
        ]);
        $key = 'gdt_club_' . $tel;
        if (!Cache::get($key)) {
            Cache::put($key, 1, 60 * 60 * 25);

            $data   = [
                '渠道'   => '广点通',
                '位置'   => $request->get('tel_location'),
                '名称'   => $request->get('leads_name'),
                '电话'   => $tel,
                '提交时间' => $request->get('leads_create_time'),
            ];
            $bundle = [];
            try {
                $val = json_decode($request->get('bundle'), true);
                if ($val)
                    $bundle = $val;
            } catch (\Exception $exception) {

            }
            $data = $data + $bundle;
            $msg  = collect($data)
                ->map(function ($value, $key) {
                    return $key . ' : ' . $value;
                })
                ->join('<br>');

            $this->sendKst($msg, $request->get('dp', ''));
        } else {
            Log::info('2. 广点通接受数据 : 数据重复', [
                $tel
            ]);
        }

        return 'OK';


    }

    public function baiduPost(Request $request)
    {
        $tel = $request->get('cluePhoneNumber');
        Log::info('1. 百度接受数据 ', [
            $tel
        ]);

        $key = 'bd_club_' . $tel;

        if (!Cache::get($key)) {
            Cache::put($key, 1, 60 * 60 * 25);

            $data = [
                '渠道'   => '基木鱼',
                '位置'   => $request->get('area'),
                '电话'   => $tel,
                '提交时间' => $request->get('commitTime'),
            ];
            $msg  = collect($data)->map(function ($value, $key) {
                    return $key . ' : ' . $value;
                })->join('<br>') . '<br>' . collect($request->get('formDetail'))->map(function ($val) {
                    return $val['name'] . ':' . $val['value'];
                })->join('<br>');


            $this->sendKst($msg, $request->get('dp', ''));
        } else {
            Log::info('2. 百度接受数据 : 数据重复', [
                $tel
            ]);
        }


        return 'OK';
    }

}
