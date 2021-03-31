<?php

namespace App\Clients;

use App\Models\JLAccount;
use App\Models\JLApp;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HttpException;
use HttpRequest;

class JuliangClient
{

    /**
     * Request Client.
     * @var Client;
     */
    public static $client;

    public static $app_id = '1668736156326939';
    public static $app_secret = '4cecbd2951c03e32f62312638063f386e9d63c26';

    public static $request_url = [
        'access_token'         => 'https://ad.oceanengine.com/open_api/oauth2/access_token/',
        'refresh_token'        => 'https://ad.oceanengine.com/open_api/oauth2/refresh_token/',
        'account_info'         => 'https://ad.oceanengine.com/open_api/2/user/info/',
        'advertiser_plan_data' => 'https://ad.oceanengine.com/open_api/2/report/ad/get/',
        'account_auth'         => 'https://ad.oceanengine.com/open_api/oauth2/advertiser/get/',
        'majordomo_account'    => 'https://ad.oceanengine.com/open_api/2/majordomo/advertiser/select/',

        'feiyu_clue' => 'https://ad.oceanengine.com/open_api/2/tools/clue/get/',

    ];

    /**
     * 获取Request Client
     * @param array $headers
     * @return Client
     */
    public static function getClient($headers = [])
    {
        if (!static::$client) {
            $headers = array_merge([
                'Content-Type' => 'application/json',
            ], $headers);

            static::$client = new Client([
                'defaults' => [
                    'verify'      => false,
                    'http_errors' => false,
                ],
                'headers'  => $headers,
            ]);

        }

        return static::$client;
    }

    /**
     * 获取广告主计划数据
     * @param $data  array Body参数
     * @param $token string access_token
     * @return mixed
     */
    public static function getAdvertiserPlanData($data, $token)
    {
        $client = static::getClient();

        $response = $client->get(static::$request_url['advertiser_plan_data'], [
            'form_params' => $data,
            'headers'     => [
                "Access-Token" => $token,
            ]
        ]);
        $content  = $response->getBody()->getContents();

        return json_decode($content, true);
    }

    /**
     * 刷新账户token.
     * @param     $token
     * @param     $appConfig
     * @param int $retry
     * @return array|boolean
     */
    public static function refreshToken($token, $appConfig, $retry = 5)
    {
        try {
            $result = static::getClient()
                ->post(static::$request_url['refresh_token'], [
                    'form_params' => [
                        "app_id"        => $appConfig['app_id'],
                        "secret"        => $appConfig['app_secret'],
                        "grant_type"    => 'refresh_token',
                        "refresh_token" => $token,
                    ]
                ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (RequestException $requestException) {
            if ($retry > 0) {
                return static::refreshToken($token, $appConfig, --$retry);
            }
            return false;
        }

    }


    /**
     * 获取 Access_token
     * @param $auth_code String 授权码
     * @param $appModel  JLApp App配置
     * @return array
     */
    public static function getAccessToken($auth_code, $appModel)
    {
        $client = static::getClient();

        $result = $client->post(static::$request_url['access_token'], [
            'form_params' => [
                "app_id"     => $appModel['app_id'],
                "secret"     => $appModel['app_secret'],
                "grant_type" => "auth_code",
                "auth_code"  => $auth_code,
            ]
        ]);
        $body   = $result->getBody()->getContents();

        return json_decode($body, true);
    }


    /**
     * @param $access_token String
     * @return string
     */
    public static function getAccountInfo($access_token)
    {
        $result = static::getClient()->get(static::$request_url['account_info'], [
            'headers' => [
                "Access-Token" => $access_token
            ]
        ]);

        $conents = $result->getBody()->getContents();

        return json_decode($conents, true);

    }

    public static function getAccountAuth($token, $appId, $appSecret)
    {

        $result = static::getClient()
            ->get(static::$request_url['account_auth'], [
                'form_params' => [
                    'access_token' => $token,
                    "app_id"       => $appId,
                    "secret"       => $appSecret,
                ]
            ]);
        $body   = $result->getBody()->getContents();

        return json_decode($body, true);
    }

    public static function getFeiyuClueData($data, $accessToken)
    {

        $client = static::getClient();

        // 测试 Query :
        $data = [
            'advertiser_ids' => '["1667928143039496"]',
            "start_time"     => '2020-06-25',
            "end_time"       => '2020-06-25',
            "page_size"      => 100,
            "page"           => 1,
        ];

        $response = $client->get(static::$request_url['feiyu_clue'], [
            'form_params' => $data,
            'headers'     => [
                "Access-Token" => $accessToken,
            ]
        ]);
        $content  = $response->getBody()->getContents();

        return json_decode($content, true);
    }

    public static function getMajordomoAccount($id, $accessToken)
    {
        $response = static::getClient()->get(static::$request_url['majordomo_account'], [
            'form_params' => [
                'advertiser_id' => $id,
            ],
            'headers'     => [
                "Access-Token" => $accessToken,
            ]
        ]);
        $content  = $response->getBody()->getContents();

        return json_decode($content, true);
    }

    public static function testGetFeiyuClueData()
    {
        $request = new HttpRequest();
        $request->setUrl('https://ad.oceanengine.com/open_api/2/tools/clue/get/');
        $request->setMethod(HTTP_METH_GET);


        $request->setQueryData(array(
            'advertiser_ids' => '["1667928143039496"]',
            'start_time'     => '2020-06-25',
            'end_time'       => '2020-06-25'
        ));

        $request->setHeaders(array(
            'Content-Type' => 'application/json',
            'access_token' => 'b2a598fd16eb7f758c1a7b17a4b689355468e2e1'
        ));

        try {
            $response = $request->send();
            dd($response->getBody());
            echo $response->getBody();
        } catch (HttpException $ex) {
            echo $ex;
        }

    }

}
