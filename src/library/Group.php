<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

/**
 * 分组管理
 */
class Group extends Init
{

    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'group_get'    => 'https://api.weixin.qq.com/cgi-bin/groups/get',
        'group_create' => 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token=',
        'group_update' => 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token=',
        'group_delete' => 'https://api.weixin.qq.com/cgi-bin/groups/delete?access_token=',
    ];

    /**
     * 获取全部分组
     */
    public function get()
    {
        $params = [
            'access_token' => $this->config['access_token'],
        ];
        $result = Util::get($this->url['group_get'], $params);
        $result = json_decode($result);
        if ($result) {
            return $result->groups;
        } else {
            return false;
        }
    }

    /**
     * 新增分组
     * @param  string $name
     * @return array ['id' => ID, 'name' => NAME]
     */
    public function create($name)
    {
        $params = [
            'group' => [
                'name' => $name,
            ],
        ];
        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $result = Util::post($this->url['group_create'] . $this->config['access_token'], $params);
        $result = json_decode($result);
        if ($result) {
            return $result->group;
        } else {
            return false;
        }
    }

    /**
     * 修改分组名
     * @param  [type] $id   [description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function update($id, $name)
    {
        $params = [
            'group' => [
                'id'   => $id,
                'name' => $name,
            ],
        ];
        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $result = Util::api($this->url['group_update'] . $this->config['access_token'], $params);
        $result = json_decode($result);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除分组
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete($id)
    {
        $params = [
            'group' => [
                'id' => $id,
            ],
        ];
        $params = json_encode($params);
        $result = Util::api($this->url['group_delete'] . $this->config['access_token'], $params);
        $result = json_decode($result);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}
