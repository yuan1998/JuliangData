<?php

namespace App\Models;

use App\Clients\JuliangClient;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TokenList extends Model
{
    protected $table = 'token_list';

    public $timestamps = false;

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_in',
        'refresh_token_expires_in',
        'status',
    ];


    public static function makeToken($data)
    {
        $expires_in               = Carbon::now()->addSeconds($data['expires_in']);
        $refresh_token_expires_in = Carbon::now()->addSeconds($data['refresh_token_expires_in']);

        return TokenList::create([
            'access_token'             => $data['access_token'],
            'refresh_token'            => $data['refresh_token'],
            'expires_in'               => $expires_in,
            'refresh_token_expires_in' => $refresh_token_expires_in,
            'status'                   => 1,
        ]);
    }

    public function checkToken($app)
    {
        if ($this->status !== 1 || $this->tokenIsExpires()) {
            $res  = $this->refreshToken($app);
            $code = data_get($res, 'code');
            Log::info('Code debug 2 : ', [
                'code' => $code
            ]);
            return !!data_get($res, 'data.access_token');
        }

        return true;
    }


    /**
     * 判断方法 : 判断 广告主 Token是否过期
     * @return bool
     */
    public function tokenIsExpires()
    {

        $time = Carbon::parse($this->expires_in)->addHours(-2);
        $now  = Carbon::now();

        return $now->gte($time);
    }


    public function refreshToken($app)
    {
        $response = JuliangClient::refreshToken($this->refresh_token, $app);
        Log::info('刷新请求结果', $response);
        $code = data_get($response, 'code');
        Log::info('Code debug 1 : ', [
            'code' => $code
        ]);
        switch ($code) {
            case 0:
                $data                     = $response['data'];
                $expires_in               = Carbon::now()->addSeconds($data['expires_in']);
                $refresh_token_expires_in = Carbon::now()->addSeconds($data['refresh_token_expires_in']);

                $this->fill(array_merge($data, [
                    'expires_in'               => $expires_in,
                    'refresh_token_expires_in' => $refresh_token_expires_in,
                    'status'                   => 1,
                ]));

                break;
            case 40103:
                $this->fill(['status' => 2]);
                break;
            default:
                $this->fill(['status' => 0]);
                break;
        }
        $this->save();

        return $response;
    }


    public static function getInfoByAdvertiserId($id)
    {


    }
}
