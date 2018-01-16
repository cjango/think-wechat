<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

class User extends Init
{

    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'user_get'        => 'https://api.weixin.qq.com/cgi-bin/user/get',
        'user_info'       => 'https://api.weixin.qq.com/cgi-bin/user/info',
        'user_info_batch' => 'https://api.weixin.qq.com/cgi-bin/user/info/batchget',
        'user_remark'     => 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark',
        'user_in_group'   => 'https://api.weixin.qq.com/cgi-bin/groups/getid',
        'user_to_group'   => 'https://api.weixin.qq.com/cgi-bin/groups/members/update',
        'batch_to_group'  => 'https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate',
    ];

    /**
     * 获取全部关注用户
     * @param  [type] $nextOpenid [description]
     */
    public function all($nextOpenid = '')
    {
        $params = [
            'next_openid'  => $nextOpenid,
            'access_token' => $this->config['access_token'],
        ];
        $result = Util::get($this->url['user_get'], $params);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } else {
            return $result->data->openid;
        }
    }

    /**
     * 获取用户信息
     * @param  [type] $openid [description]
     * @param  [type] $lang   [description]
     * @return [type]         [description]
     */
    public function info($openid, $lang = 'zh_CN')
    {
        $params = [
            'openid'       => $openid,
            'access_token' => $this->config['access_token'],
            'lang'         => $lang,
        ];
        $result = Util::get($this->url['user_info'], $params);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } else {
            return $result;
        }
    }

}
