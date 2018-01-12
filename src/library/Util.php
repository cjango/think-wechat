<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\wechat\library;

class Util
{
    protected static $error;

    protected $result;

    public static function get($url, $params = '')
    {
        $opts = [
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        $getQuerys         = !empty($params) ? '?' . urldecode(http_build_query($params)) : '';
        $opts[CURLOPT_URL] = $url . $getQuerys;
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data   = curl_exec($ch);
        $err    = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        if ($err > 0) {
            return false;
        } else {
            return $data;
        }
    }

    /**
     * 解析返回的json数据
     * @param  [type] $json
     * @return
     */
    // {"errcode":40164,"errmsg":"invalid ip 139.129.108.141, not in whitelist hint: [1QPF704341466]"}
    public static function parse($json)
    {
        $result = json_decode($json);
        if (isset($result->errcode) && $result->errcode == 0) {
            return $result;
        } elseif ($result->errcode != 0) {
            self::$error = $result->errmsg;
            return false;
        } else {
            return $result;
        }
    }

    /**
     * XML文档解析成数组，并将键值转成小写
     * @param  xml   $xml
     * @return array
     */
    public static function xml2array($xml)
    {
        $data = (array) simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return array_change_key_case($data, CASE_LOWER);
    }

    /**
     * 将数组转换成XML
     * @param  array $array
     * @return xml
     */
    public static function array2xml($array = [])
    {
        $xml = new \SimpleXMLElement('<xml></xml>');
        self::_data2xml($xml, $array);
        return $xml->asXML();
    }

    /**
     * 数据XML编码
     * @param  xml    $xml  XML对象
     * @param  mixed  $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string xml
     */
    private static function _data2xml($xml, $data, $item = 'item')
    {
        foreach ($data as $key => $value) {
            is_numeric($key) && $key = $item;
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                self::_data2xml($child, $value, $item);
            } else {
                if (is_numeric($value)) {
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node  = dom_import_simplexml($child);
                    $node->appendChild($node->ownerDocument->createCDATASection($value));
                }
            }
        }
    }

    public static function getError()
    {
        return self::$error;
    }
}
