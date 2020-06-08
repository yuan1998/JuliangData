<?php

namespace App\Clients;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

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
                'defaults' => ['verify' => false],
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
     * @param $token string 用户获取新Token的Refresh_token
     * @return array
     */
    public static function refreshToken($token)
    {
        $result = static::getClient()->post(static::$request_url['refresh_token'], [
            'form_params' => [
                "app_id"        => static::$app_id,
                "secret"        => static::$app_secret,
                "grant_type"    => 'refresh_token',
                "refresh_token" => $token,
            ]
        ]);
        return json_decode($result->getBody()->getContents(), true);
    }


    /**
     * 获取 Access_token
     * @param $auth_code String 授权码
     * @return array
     */
    public static function getAccessToken($auth_code)
    {
        $client = static::getClient();

        $result = $client->post(static::$request_url['access_token'], [
            'form_params' => [
                "app_id"     => static::$app_id,
                "secret"     => static::$app_secret,
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

}
