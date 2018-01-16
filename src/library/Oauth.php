<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

use think\Request;
use think\Response;

class Oauth extends Init
{

    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'oauth_authorize'    => 'https://open.weixin.qq.com/connect/oauth2/authorize',
        'oauth_user_token'   => 'https://api.weixin.qq.com/sns/oauth2/access_token',
        'oauth_get_userinfo' => 'https://api.weixin.qq.com/sns/userinfo',
    ];

    /**
     * OAuth 用户同意授权，获取code
     * @param  [type] $callback 回调URI，填写完整地址，带http://
     * @param  string $state    重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param  string $scope    snsapi_userinfo 获取用户授权信息，snsapi_base直接返回openid
     * @return string
     */
    public function url($callback, $state = '', $scope = 'snsapi_userinfo')
    {
        $params = [
            'appid'         => $this->config['appid'],
            'redirect_uri'  => $callback,
            'response_type' => 'code',
            'scope'         => $scope,
            'state'         => $state,
        ];
        $url = $this->url['oauth_authorize'] . '?' . http_build_query($params) . '#wechat_redirect';
        Response::create($url, 'Redirect', 302)->send();
    }

    /**
     * 通过code获取openid或直接获取用户信息
     * @return array|boolean
     */
    public function token()
    {
        $code = Request::instance()->get('code');
        if (!$code) {
            $this->setError('未获取到CODE信息');
            return false;
        }
        $params = [
            'appid'      => $this->config['appid'],
            'secret'     => $this->config['secret'],
            'code'       => $code,
            'grant_type' => 'authorization_code',
        ];
        $result = Util::get($this->url['oauth_user_token'], $params);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } elseif ($result->scope == 'snsapi_base') {
            return $result->openid;
        } elseif ($result->scope == 'snsapi_userinfo') {
            return $this->info($result->access_token, $result->openid);
        } else {
            return $result;
        }
    }

    /**
     * 网页获取用户信息
     * @param  string $access_token  通过getOauthAccessToken方法获取到的token
     * @param  string $openid        用户的OPENID
     * @return array
     */
    public function info($token, $openid)
    {
        $params = [
            'access_token' => $token,
            'openid'       => $openid,
            'lang'         => 'zh_CN',
        ];
        $result = Util::get($this->url['oauth_get_userinfo'], $params);
        return json_decode($result);
    }
}
