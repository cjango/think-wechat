<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

use think\Request;
use think\Response;

class Pay extends Init
{
    private $signType = 'HMAC-SHA256';

    /**
     * 接口名称与URL映射
     * @var array
     */
    protected $url = [
        'unified_order'      => 'https://api.mch.weixin.qq.com/pay/unifiedorder', // 统一下单地址
        'order_query'        => 'https://api.mch.weixin.qq.com/pay/orderquery', // 订单状态查询
        'close_order'        => 'https://api.mch.weixin.qq.com/pay/closeorder', // 关闭订单
        'refund_order'       => 'https://api.mch.weixin.qq.com/secapi/pay/refund', // 退款地址
        'refund_query'       => 'https://api.mch.weixin.qq.com/pay/refundquery', // 退款查询
        'pay_transfers'      => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', // 企业付款
        'get_pay_transfers'  => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo', // 企业付款查询
        'download_bill'      => 'https://api.mch.weixin.qq.com/pay/downloadbill', // 下载对账单
        'send_redpack'       => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack', // 发放红包高级接口
        'send_group_redpack' => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack', // 发送裂变红包接口(拼手气)
        'get_redpack_info'   => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo', // 红包查询接口
    ];

    /**
     * 公众号支付
     * @param  string $orderId   [订单号]
     * @param  string $openid    [用户OPENID]
     * @param  string $body      [商品描述]
     * @param  float  $money     [支付金额]
     * @param  string $notifyUrl [通知地址]
     * @param  string $attach    [附加内容]
     * @return [type]
     */
    public function jsapi($orderId, $openid, $body, float $money, $notifyUrl, $attach = 'ATTACH')
    {
        $params = [
            'out_trade_no'     => $orderId,
            'openid'           => $openid,
            'body'             => $body,
            'total_fee'        => $money * 100,
            'notify_url'       => $notifyUrl,
            'attach'           => $attach,
            'appid'            => $this->config['appid'],
            'mch_id'           => $this->config['mch_id'],
            'nonce_str'        => uniqid(),
            'spbill_create_ip' => Request::instance()->ip(),
            'trade_type'       => 'JSAPI',
            'sign_type'        => $this->signType,
        ];
        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::post($this->url['unified_order'], $params);
        $result = self::response($result);

        if ($result) {
            $return['appId']     = $this->config['appid'];
            $return['timeStamp'] = (string) time();
            $return['nonceStr']  = uniqid();
            $return['package']   = 'prepay_id=' . $result['prepay_id'];
            $return['signType']  = $this->signType;
            $return['paySign']   = self::sign($return);
            return json_encode($return);
        } else {
            return false;
        }
    }

    /**
     * 原生扫码支付
     * @param  [type] $orderId   [订单号]
     * @param  [type] $body      [商品描述]
     * @param  float  $money     [支付金额]
     * @param  [type] $notifyUrl [通知地址]
     * @param  string $attach    [附加内容]
     * @return [type]
     */
    public function native($orderId, $body, float $money, $notifyUrl, $attach = 'ATTACH')
    {
        $params = [
            'out_trade_no'     => $orderId,
            'body'             => $body,
            'total_fee'        => $money * 100,
            'notify_url'       => $notifyUrl,
            'attach'           => $attach,
            'appid'            => $this->config['appid'],
            'mch_id'           => $this->config['mch_id'],
            'nonce_str'        => uniqid(),
            'spbill_create_ip' => Request::instance()->ip(),
            'trade_type'       => 'NATIVE',
            'sign_type'        => $this->signType,
        ];
        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::post($this->url['unified_order'], $params);
        $result = self::response($result);

        if ($result) {
            return $result['code_url'];
        } else {
            return false;
        }
    }

    /**
     * 查询订单
     * @param  string  $orderId    订单号
     * @param  boolean $outTradeNo 使用商户订单号
     * @return [type]
     */
    public function query($orderId = '', $outTradeNo = true)
    {
        $params = [
            'appid'     => $this->config['appid'],
            'mch_id'    => $this->config['mch_id'],
            'nonce_str' => uniqid(),
            'sign_type' => $this->signType,
        ];
        if ($outTradeNo === true) {
            $params['out_trade_no'] = $orderId;
        } else {
            $params['transaction_id'] = $orderId;
        }
        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::post($this->url['order_query'], $params);
        return self::response($result);
    }

    /**
     * 关闭订单
     * @param  string  $orderId 商户订单号
     * @return boolean
     */
    public function close($orderId)
    {
        $params = [
            'appid'        => $this->config['appid'],
            'mch_id'       => $this->config['mch_id'],
            'out_trade_no' => $orderId,
            'nonce_str'    => uniqid(),
            'sign_type'    => $this->signType,
        ];
        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::http($this->url['close_order'], $params);
        $result = self::response($result);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 退款申请
     * @param  [type] $orderId     [原始订单号]
     * @param  [type] $refundId    [退款单号]
     * @param  [type] $totalFee    [订单总金额]
     * @param  [type] $refundFee   [退款金额]
     * @param  string $description [退款原因]
     * @return [type]
     */
    public function refund($orderId, $refundId, float $totalFee, float $refundFee, $description = 'REFUND')
    {
        $params = [
            'appid'         => $this->config['appid'],
            'mch_id'        => $this->config['mch_id'],
            'out_trade_no'  => $orderId,
            'out_refund_no' => $refundId,
            'total_fee'     => $totalFee * 100,
            'refund_fee'    => $refundFee * 100,
            'op_user_id'    => $this->config['mch_id'],
            'nonce_str'     => uniqid(),
            'refund_desc'   => $description,
            'sign_type'     => $this->signType,
        ];
        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::postSsl($this->url['refund_order'], $params, $this->config['pem']);
        if ($result) {
            $result = self::response($result);
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            $this->setError('退款申请失败，可能是缺少证书');
            return false;
        }
    }

    /**
     * 查询退款订单
     * @param  [type]  $orderId 123456 / ['transaction_id' => 123456]
     * 微信订单号   transaction_id
     * 商户订单号   out_trade_no
     * 商户退款单号 out_refund_no
     * 微信退款单号 refund_id
     * @param  integer $offset  [description]
     * @return [type]           [description]
     */
    public function refundQuery($orderId, $offset = 0)
    {
        $params = [
            'appid'     => $this->config['appid'],
            'mch_id'    => $this->config['mch_id'],
            'nonce_str' => uniqid(),
            'sign_type' => $this->signType,
        ];
        if (!is_array($orderId)) {
            $params['out_refund_no'] = $orderId;
        } elseif (in_array(key($orderId), ['transaction_id', 'out_trade_no', 'out_refund_no', 'refund_id'])) {
            $params[key($orderId)] = current($orderId);
        } else {
            $this->setError('不合法的参数');
            return false;
        }
        if (is_numeric($offset) && $offset > 0) {
            $params['offset'] = $offset;
        }

        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::post($this->url['refund_query'], $params);
        $result = self::response($result);

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 发放普通红包
     * @param  [type] $orderId  [description]
     * @param  [type] $openid   [description]
     * @param  [type] $money    [description]
     * @param  [type] $sendName [description]
     * @param  [type] $wishing  [description]
     * @param  [type] $remark   [description]
     * @return [type]           [description]
     */
    public function redpack($orderId, $openid, $money, $sendName, $wishing, $actName, $remark)
    {
        $params = [
            'nonce_str'    => uniqid(),
            'mch_id'       => $this->config['mch_id'],
            'mch_billno'   => $orderId,
            'wxappid'      => $this->config['appid'],
            'send_name'    => $sendName,
            're_openid'    => $openid,
            'total_amount' => $money * 100,
            'total_num'    => 1,
            'wishing'      => $wishing,
            'act_name'     => $actName,
            'client_ip'    => Request::instance()->server('SERVER_ADDR'),
            'remark'       => $remark,
        ];

        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::postSsl($this->url['send_redpack'], $params);
        if ($result) {
            $result = self::response($result);
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            $this->setError('红包发送失败，可能是缺少证书');
            return false;
        }
    }

    public function redpackGroup($totalNumber)
    {
        $params = [
            'nonce_str'    => uniqid(),
            'mch_id'       => $this->config['mch_id'],
            'mch_billno'   => $orderId,
            'wxappid'      => $this->config['appid'],
            'send_name'    => $sendName,
            're_openid'    => $openid,
            'total_amount' => $money * 100,
            'total_num'    => $totalNumber,
            'amt_type'     => 'ALL_RAND',
            'wishing'      => $wishing,
            'act_name'     => $actName,
            'client_ip'    => Request::instance()->server('SERVER_ADDR'),
            'remark'       => $remark,
        ];

        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::postSsl($this->url['send_group_redpack'], $params);
        if ($result) {
            $result = self::response($result);
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            $this->setError('红包发送失败，可能是缺少证书');
            return false;
        }
    }

    public function redpackInfo($orderId)
    {
        $params = [
            'nonce_str'  => uniqid(),
            'mch_id'     => $this->config['mch_id'],
            'mch_billno' => $orderId,
            'appid'      => $this->config['appid'],
            'bill_type'  => 'MCHT',
        ];

        $params['sign'] = self::sign($params);

        $params = Util::array2xml($params);
        $result = Util::postSsl($this->url['get_redpack_info'], $params);
        if ($result) {
            $result = self::response($result);
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            $this->setError('红包发送失败，可能是缺少证书');
            return false;
        }
    }

    /**
     * 解析支付接口的返回数据
     * @param  [type] $data [description]
     */
    private function response($data)
    {
        $data = Util::xml2array($data);

        if (!empty($data) && $data['return_code'] == 'SUCCESS') {
            if ($data['result_code'] == 'SUCCESS') {
                $sign = $data['sign'];
                unset($data['sign']);
                if (self::sign($data) == $sign) {
                    return $data;
                } else {
                    $this->setError('签名校验错误');
                    return false;
                }
            } else {
                $this->setError($data['err_code']);
                return false;
            }
        } else {
            $this->setError($data['return_msg']);
            return false;
        }
    }

    /**
     * 数据签名校验
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    private function sign($params)
    {
        ksort($params);
        $params['key'] = $this->config['paykey'];
        return strtoupper(hash_hmac('sha256', urldecode(http_build_query($params)), $this->config['paykey']));
    }

    /**
     * 解析支付接口的返回结果
     * @param  xmlstring $data      接口返回的数据
     * @param  boolean   $checkSign 是否需要签名校验
     * @return boolean|array
     */
    public function request($checkSign = true)
    {
        $post = file_get_contents("php://input");
        if (empty($data)) {
            $this->setError('回调结果解析失败');
            return false;
        }
        $data = Util::xml2array($post);
        if (empty($data)) {
            $this->setError('回调结果解析失败');
            return false;
        }
        if ($checkSign) {
            $sign = $data['sign'];
            unset($data['sign']);
            if (self::sign($data) != $sign) {
                $this->setError('签名校验失败');
                return false;
            }
        }
        $data['time_end'] = strtotime($data['time_end']);
        return $data;
    }

    /**
     * 支付结果通知
     * @param  [type] $msg 支付结果
     * @return xml
     */
    public function notify($msg = true)
    {
        if ($msg === true) {
            $params = [
                'return_code' => 'SUCCESS',
                'return_msg'  => 'OK',
            ];
        } else {
            $params = [
                'return_code' => 'FAIL',
                'return_msg'  => $msg,
            ];
        }
        $params = Util::array2xml($params);
        Response::create($params)->send();
    }
}
