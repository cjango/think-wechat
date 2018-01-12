<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

use cjango\wechat\Wechat;

class Token
{
    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'access_token' => 'https://api.weixin.qq.com/cgi-bin/token',
        'jsapi_ticket' => 'https://api.weixin.qq.com/cgi-bin/ticket/getticket',
    ];

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get()
    {
        $params = [
            'appid'      => $this->config['appid'],
            'secret'     => $this->config['secret'],
            'grant_type' => 'client_credential',
        ];
        $result = Util::get($this->url['access_token'], $params);
        $result = json_decode($result);
        if (isset($result->errcode) && $result->errcode == 0) {
            return $result->access_token;
        } else {
            Wechat::error($result->errmsg);
            return false;
        }
    }
}
