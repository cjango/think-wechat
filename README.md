# think-wechat

微信SDK

```
For thinkphp5.0
```


use cjango\wechat\Wechat;

// 扫码支付
$payUrl = Wechat::get('pay')->native(ORDERID, '商品名称', 100.00, url('pay/index'));


获取错误信息
Wechat::error()
