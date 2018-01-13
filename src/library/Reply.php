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

class Reply extends Init
{
    /**
     * 接收到的消息内容
     * @var array
     */
    private $request = [];

    /**
     * 回复消息结构体
     * @var array
     */
    private $response = [];

    protected $inited;

    /**
     * 接受消息,通用,接受到的消息
     * 用户自己处理消息类型就可以
     * 暂时不处理加密问题
     * @return array|boolean
     */
    public function instance()
    {
        $postStr = file_get_contents("php://input");

        $this->request = Util::xml2array($postStr);
        $this->inited == true;
    }

    public function request()
    {
        if (!$this->inited) {
            self::instance();
        }

        return $this->request;
    }

    /**
     * 回复消息
     * @param  array|string $content 回复的消息内容
     * @param  string $type 回复的消息类型
     * @return xml
     */
    public function response($content, $type = 'text')
    {
        if (!$this->inited) {
            self::instance();
        }

        $this->response = [
            'ToUserName'   => $this->request['FromUserName'],
            'FromUserName' => $this->request['ToUserName'],
            'CreateTime'   => time(),
            'MsgType'      => $type,
        ];

        $this->$type($content);

        $response = Util::array2xml($this->response);
        exit($response);
    }

    /**
     * 回复文本类型消息
     * @param  string $content
     */
    private function text($content)
    {
        $this->response['Content'] = $content;
    }

    /**
     * 回复图片类型消息
     * @param  string $mediaId
     */
    private function image($mediaId)
    {
        $this->response['Image']['MediaId'] = $mediaId;
    }

    /**
     * 回复语音类型消息
     * @param  string $mediaId
     */
    private function voice($mediaId)
    {
        $this->response['Voice']['MediaId'] = $mediaId;
    }

    /**
     * 回复视频类型消息
     * @param  array $media
     */
    private function video($video)
    {
        list(
            $video['MediaId'],
            $video['Title'],
            $video['Description']
        ) = $video;

        $this->response['Video'] = $video;
    }

    /**
     * 回复音乐信息
     * @param  string $content 要回复的音乐
     */
    private function music($music)
    {
        list(
            $music['Title'],
            $music['Description'],
            $music['MusicUrl'],
            $music['HQMusicUrl'],
            $music['ThumbMediaId']
        ) = $music;

        $this->response['Music'] = $music;
    }

    /**
     * 回复图文列表消息
     * @param  [type] $content [description]
     */
    private function news($news)
    {
        $articles = [];

        foreach ($news as $key => $value) {
            list(
                $articles[$key]['Title'],
                $articles[$key]['Description'],
                $articles[$key]['PicUrl'],
                $articles[$key]['Url']
            ) = $value;
            if ($key >= 9) {
                break;
            }
        }

        $this->response['ArticleCount'] = count($articles);
        $this->response['Articles']     = $articles;
    }

    /**
     * 将消息转发至客服
     * @param  string $account 指定的客服账号
     * @return xml
     */
    public function transfer($account = '')
    {
        $this->response = [
            'ToUserName'   => $this->request['fromusername'],
            'FromUserName' => $this->request['tousername'],
            'CreateTime'   => time(),
            'MsgType'      => 'transfer_customer_service',
        ];

        if (!empty($account)) {
            $this->response['TransInfo']['KfAccount'] = $account;
        }

        $response = Util::array2xml($this->response);
        exit($response);
    }
}
