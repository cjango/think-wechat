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

abstract class Init
{
    protected $config;

    protected $result;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function result()
    {
        return $this->result;
    }

    public function setError($msg)
    {
        Wechat::error($msg);
    }
}
