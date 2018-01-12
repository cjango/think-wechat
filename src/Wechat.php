<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat;

use ReflectionClass;
use think\Cache;
use think\Config;
use think\exception\ClassNotFoundException;

class Wechat
{
    protected static $inited;

    protected static $config;

    protected static $container;

    protected static $error;

    /**
     * 初始化微信SDK
     * @param  boolean $Token 是否获取TOKEN
     * @return void
     */
    public static function instance($Token = true)
    {
        self::$config = Config::get('wechat');

        if ($Token === true) {
            $token = Cache::get('cjango_wechat_token');
            if (!$token) {
                $Token = new library\Token(self::$config);
                $token = $Token->get();
                Cache::set('cjango_wechat_token', $token, 1);
            }
            self::$config['access_token'] = $token;
        }

        self::$inited = true;
    }

    /**
     * 调用反射执行类的实例化
     * @access public
     * @param  string $abstract 类名
     * @return new class
     */
    public static function get($abstract)
    {
        if (isset(self::$container[$abstract])) {
            return self::$container[$abstract];
        } else {
            !self::$inited && self::instance();

            $class = 'cjango\\wechat\\library\\' . ucfirst($abstract);
            if (!class_exists($class)) {
                throw new ClassNotFoundException('class not exists:' . $class, $class);
            }

            $Reflecter   = new ReflectionClass($class);
            $constructor = $Reflecter->getConstructor();

            if ($constructor) {
                $Reflect = $Reflecter->newInstance(self::$config);
            } else {
                $Reflect = $Reflecter->newInstance();
            }

            return self::$container[$abstract] = $Reflect;
        }
    }

    public static function error($error = null)
    {
        if (!is_null($error)) {
            self::$error = $error;
        } else {
            return self::$error;
        }
    }
}
