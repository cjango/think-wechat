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
use think\Request;

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
            $AccessToken = Cache::get('cjango_wechat_token');
            if (!$AccessToken) {
                $Token       = new library\Token(self::$config);
                $AccessToken = $Token->access();
                Cache::set('cjango_wechat_token', $AccessToken, 7000);
            }
            self::$config['access_token'] = $AccessToken;
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

    /**
     * 验证URL有效性,校验请求签名
     * @return string|boolean
     */
    public static function valid()
    {
        !self::$inited && self::instance(false);

        $echoStr = Request::instance()->get('echostr');

        if ($echoStr) {
            self::checkSignature() && exit($echoStr);
        } else {
            !self::checkSignature() && exit('Access Denied!');
        }
    }

    /**
     * 检查请求URL签名
     * @return boolean
     */
    private static function checkSignature()
    {
        $signature = Request::instance()->get('signature');
        $timestamp = Request::instance()->get('timestamp');
        $nonce     = Request::instance()->get('nonce');

        if (empty($signature) || empty($timestamp) || empty($nonce)) {
            return false;
        }
        $token  = self::$config['token'];
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        return sha1($tmpStr) == $signature;
    }

    /**
     * 设置、获取 错误信息
     * @param  [type] $error [description]
     * @return [type]        [description]
     */
    public static function error($error = null)
    {
        if (!is_null($error)) {
            self::$error = $error;
        } else {
            return self::$error;
        }
    }
}
