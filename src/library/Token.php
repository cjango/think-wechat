<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

use think\Cache;
use think\Request;

class Token extends Init
{
    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'access_token' => 'https://api.weixin.qq.com/cgi-bin/token',
        'jsapi_ticket' => 'https://api.weixin.qq.com/cgi-bin/ticket/getticket',
        'js_file'      => 'http://res.wx.qq.com/open/js/jweixin-1.2.0.js',
    ];

    /**
     * 获取ACCESS_TOKEN
     * @return [type] [description]
     */
    public function access()
    {
        $params = [
            'appid'      => $this->config['appid'],
            'secret'     => $this->config['secret'],
            'grant_type' => 'client_credential',
        ];
        $result = Util::get($this->url['access_token'], $params);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } elseif (isset($result->access_token)) {
            Cache::set('cjango_wechat_token', $result->access_token, 7000);
            return $result->access_token;
        } else {
            $this->setError($result->errmsg);
            return false;
        }
    }

    /**
     * 引入JS文件
     * @return [type] [description]
     */
    public function jsfile()
    {
        return '<script type="text/javascript" src="' . $this->url['js_file'] . '"></script>';
    }

    /**
     * 获取JSAPI_TICKET
     * @return [type] [description]
     */
    public function ticket()
    {
        $ticket = Cache::get('cjango_jsapi_ticket');

        if ($ticket) {
            return $ticket;
        }

        $params = [
            'access_token' => $this->config['access_token'],
            'type'         => 'jsapi',
        ];
        $result = Util::get($this->url['jsapi_ticket'], $params);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } elseif (isset($result->ticket)) {
            Cache::set('cjango_jsapi_ticket', $result->ticket, 7000);
            return $result->ticket;
        } else {
            $this->setError($result->errmsg);
            return false;
        }
    }

    /**
     * 获取权限验证配置
     * @return [type] [description]
     */
    public function share()
    {
        $signArr = [
            'timestamp'    => time(),
            'noncestr'     => uniqid(),
            'jsapi_ticket' => $this->ticket(),
            'url'          => Request::instance()->url(true),
        ];

        ksort($signArr);
        $signature = sha1(urldecode(http_build_query($signArr)));

        return [
            'appId'     => $this->config['appid'],
            'timestamp' => $signArr['timestamp'],
            'nonceStr'  => $signArr['noncestr'],
            'signature' => $signature,
        ];
    }
}
