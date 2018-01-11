<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat;

use think\Config;

class Wechat
{
    protected $config;

    public function __construct()
    {
        $this->config = Config::get('wechat');
    }

    public function instance()
    {

    }
}
