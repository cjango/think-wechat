<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

class Menu extends Init
{

    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'menu_get'    => 'https://api.weixin.qq.com/cgi-bin/menu/get', // 获取菜单
        'menu_create' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=', // 创建菜单
        'menu_delete' => 'https://api.weixin.qq.com/cgi-bin/menu/delete', // 删除菜单
    ];

    /**
     * 获取自定义菜单
     * @return [type] [description]
     */
    public function get()
    {
        $params = [
            'access_token' => $this->config['access_token'],
        ];
        $result = Util::get($this->url['menu_get'], $params);
        $result = json_decode($result);
        if ($result) {
            return $result->menu->button;
        } else {
            return false;
        }
    }

    /**
     * 创建菜单
     * @param  [type] $menu [description]
     * @return [type]        [description]
     */
    public function create($menu)
    {
        $params = [
            'button' => $menu,
        ];
        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $result = Util::post($this->url['menu_create'] . $this->config['access_token'], $params);
        $result = json_decode($result);
        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * 删除自定义菜单
     * @return [type] [description]
     */
    public function delete()
    {
        $params = [
            'access_token' => $this->config['access_token'],
        ];
        $result = Util::get($this->url['menu_delete'], $params);
        $result = json_decode($result);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } else {
            return true;
        }
    }
}
