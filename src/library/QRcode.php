<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

class QRcode extends Init
{
    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'qrcode_create' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=',
        'qrcode_show'   => 'https://mp.weixin.qq.com/cgi-bin/showqrcode',
        'short_url'     => 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=',
    ];

    /**
     * 临时二维码
     * @param  [type]  $sceneId 场景值
     * @param  integer $expire  有效期
     * @param  boolean $master  返回场景URL
     * @return [type]
     */
    public function temp($sceneId, $expire = 604800, $master = false)
    {
        $params['expire_seconds'] = $expire;

        if (is_integer($sceneId)) {
            $params['action_name'] = 'QR_SCENE';
            $params['action_info'] = ['scene' => ['scene_id' => $sceneId]];
        } elseif (is_string($sceneId) || is_float($sceneId)) {
            $params['action_name'] = 'QR_STR_SCENE';
            $params['action_info'] = ['scene' => ['scene_str' => $sceneId]];
        } else {
            return false;
        }

        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $result = Util::post($this->url['qrcode_create'] . $this->config['access_token'], $params);
        $result = json_decode($result);
        if ($master) {
            return $result->url;
        } else {
            return $this->url['qrcode_show'] . '?ticket=' . urlencode($result->ticket);
        }
    }

    /**
     * 永久二维码
     * @param  [type]  $sceneId 场景值
     * @param  boolean $master  返回场景URL
     * @return [type]
     */
    public function limit($sceneId, $master = false)
    {
        if (is_integer($sceneId)) {
            $params['action_name'] = 'QR_LIMIT_SCENE';
            $params['action_info'] = ['scene' => ['scene_id' => $sceneId]];
        } elseif (is_string($sceneId) || is_float($sceneId)) {
            $params['action_name'] = 'QR_LIMIT_STR_SCENE';
            $params['action_info'] = ['scene' => ['scene_str' => $sceneId]];
        } else {
            return false;
        }

        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $result = Util::post($this->url['qrcode_create'] . $this->config['access_token'], $params);
        $result = json_decode($result);
        if ($master) {
            return $result->url;
        } else {
            return $this->url['qrcode_show'] . '?ticket=' . urlencode($result->ticket);
        }
    }

    /**
     * 转换短链接
     * @param  [type] $longUrl
     * @return [type]
     */
    public function short($longUrl)
    {
        $params = [
            'action'   => 'long2short',
            'long_url' => $longUrl,
        ];

        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $result = Util::post($this->url['short_url'] . $this->config['access_token'], $params);
        $result = json_decode($result);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } else {
            return $result->short_url;
        }
    }
}
