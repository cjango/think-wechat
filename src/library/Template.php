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
 * 模板消息
 */
class Template extends Init
{
    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'get_template'  => 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=', // 获取模板列表
        'del_template'  => 'https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=', // 删除模板
        'send_template' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=', // 发送模板消息
    ];

    /**
     * 获取模板列表
     * @return [type] [description]
     */
    public function get()
    {
        $result = Util::get($this->url['get_template'] . $this->config['access_token']);

        return $result->template_list;
    }

    /**
     * 删除模板
     * @param  [type] $templateId 长模板ID 例：Dyvp3-Ff0cnail_CDSzk1fIc6-9lOkxsQE7exTJbwUE
     * @return boolean
     */
    public function delete($templateId)
    {
        $params = [
            'template_id' => $templateId,
        ];
        $params = json_encode($params);
        $result = Util::post($this->url['del_template'] . $this->config['access_token'], $params);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * 发送模板消息
     * @param  [type] $openid     接收用户
     * @param  [type] $templateId 模板ID
     * @param  array  $data       消息体
     * @param  string $url        连接URL
     * @return boolean
     */
    public function send($openid, $templateId, $data = [], $url = '', $miniprogram = '')
    {
        $params = [
            'touser'      => $openid,
            'template_id' => $templateId,
            'url'         => $url,
            'miniprogram' => $miniprogram,
            'data'        => $data,
        ];
        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $result = Util::post($this->url['send_template'] . $this->config['access_token'], $params);

        if (isset($result->errcode) && $result->errcode != 0) {
            $this->setError($result->errmsg);
            return false;
        } else {
            return true;
        }
    }
}
