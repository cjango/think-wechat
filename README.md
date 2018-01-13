# think-wechat
===============

[![Latest Stable Version](https://poser.pugx.org/cjango/think-wechat/version)](https://packagist.org/packages/cjango/think-wechat)

#### 微信SDK For thinkphp5

> 该项目依赖于thinkphp5.0.*，省去了access_token的获取与暂存，系统内部自动集成，需要什么功能直接调用即可，设计模式参考了thinkphp的容器模式，目前暂时只有部分基础功能，待完善。

## 安装
> composer require cjango/think-wechat

## 配置
> 配置文件位于 `application/extra/wechat.php`

```
return [
    'token'  => '',  // TOKEN
    'appid'  => '',  // APPID
    'secret' => '',  // 密钥
    'AESKey' => '',  // 数据传输加密密钥
    'mch_id' => '',  // 商户ID
    'paykey' => '',  // 支付密钥
    // PEM 文件路径
    'pem'    => [
        'cert' => '../certfiles/private_cert.pem',
        'key'  => '../certfiles/private_key.pem',
    ],
];
```

> 证书文件不要放在public目录下，以防被扫描下载，不使用微信红包和线上退款功能可以不用配置支付证书

## 使用方法
```
// 引用命名空间
use cjango\wechat\Wechat;

// 获取全部关注用户
Wechat::get('user')->all();

// 网页用户直接跳转授权
Wechat::get('oauth')->url($callback);

// 回调页面中直接调用
Wechat::get('oauth')->token();

// 获取错误信息
Wechat::error()
```
