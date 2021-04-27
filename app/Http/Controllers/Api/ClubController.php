<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use GuzzleHttp\Client;
use HeadlessChromium\BrowserFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClubController extends Controller
{

    public function sendKst($message)
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
        $driver->get('https://vipz2-hzbk2.kuaishang.cn/bs/im.htm?cas=57284___922518&fi=67975&dp=http%3A%2F%2F47.104.65.130%3A8028%2Fzt%2Fxxl_jz_test_04%2Findex.html&sText=xxl_page&vi=&ref=http://47.104.65.130:8028/test/admin.html&ism=1&cv=' . urlencode($message));
    }

    public function post(Request $request)
    {

        $data = [
                '位置'   => $request->get('tel_location'),
                '名称'   => $request->get('leads_name'),
                '电话'   => $request->get('leads_tel'),
                '提交时间' => $request->get('leads_create_time'),
            ] + $request->get('bundle', []);


        $this->sendKst(implode('<br>', $data));
        Log::info('测试 post 接受数据', $data);
    }
}
