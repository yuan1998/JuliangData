<?php

namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;


function request_by_curl($remote_server, $post_string)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

class DingDingRobotClient
{

    public $config;
    public $client;

    public function __construct($name)
    {
        $config = config('dingDingRobot.' . $name);

        if (!$config) throw new Exception('错误: 机器人配置名称不存在');


        $this->config = $config;
    }

    public static function validateRobotName($name)
    {
        $config = config('dingDingRobot.' . $name);

        return !!$config;
    }

    public function getClient()
    {

        if (!$this->client) {
            $headers = [
                'Content-Type' => 'application/json;charset=utf-8'
            ];

            $this->client = new Client([
                'defaults' => [
                    'verify'      => false,
                    'http_errors' => false,
                ],
                'headers'  => $headers,
            ]);


        }


        return $this->client;
    }

    public function signToken($timestamp)
    {
        $secret       = $this->config['sign_token'];
        $string       = $timestamp . "\n" . $secret;
        $sha256String = hash_hmac('sha256', $string, $secret, true);
        $signData     = base64_encode($sha256String);
        return urlencode($signData);
    }

    public function postMessage($data)
    {
        list($msec, $sec) = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $url       = $this->config['uri'];
        //. "?access_token={$this->config['access_token']}&timestamp={$timestamp}&sign={$this->signToken($timestamp)}";

//        $result = request_by_curl($url , json_encode($data));
//        dd($result);


        $client = $this->getClient();

        $response = $client->request('POST', $url, [
            'query' => [
                'access_token' => $this->config['access_token'],
                'timestamp'    => $timestamp,
                'sign'         => $this->signToken($timestamp),
            ],
            'body'  => json_encode($data),
        ]);

        return $response->getBody()->getContents();
    }

    public function sendText($text, $at = [])
    {
        return $this->postMessage([
            "msgtype" => "text",
            "text"    => [
                "content" => $text,
            ],
            'at'      => $at,
        ]);
    }

    public static function test()
    {
        $robot = new static('北京机器人');
        $robot->postMessage([
            "msgtype" => "text",
            "text"    => [
                "content" => "我就是我, 是不一样的烟火@156xxxx8827"
            ],
            "at"      => [
                "isAtAll" => false,
            ]
        ]);

    }

}
