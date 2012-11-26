<?php

/*
    File: wechat.php
    Author: felix021@gmail.com
    Date: 2012.11.26
    Usage: api封装
    Comment: 
        1. 验证api的时候，调用$w->valid()

        2. 处理用户请求，调用$w->valid(callback)

        2.1 callback函数的参数
            (1) 数组，包含解析xml后得到的所有key, 同接口api文档，或参考后面的$req_keys
            (2) Wechat对象$w（用于分类星标）

        2.2 分类为星标
            在callback函数中调用 $w->set_funcflag();

        2.3 返回值（2种）
            (1) 字符串: 表示回复文本消息
                  [例]
                    function callback($request) {
                        return "echo: " + $request['Content'];
                    }
            (2) 数组: 表示回复图文消息
                每个图文消息是一个item，包含 title, description, pic, url 共4个key
                若只需要一个item，返回一个一维数组
                  [例]
                    return array("title" => "hello", "description" => "world",
                                 "pic" => "http://host/x.jpg", "url" => "http://host/");
                若需要多个item，返回一个包含多个item的二维数组
                  [例]
                    return array(
                        array("title" => "a1", "description" => "world",
                             "pic" => "http://host/a1.jpg", "url" => "http://host/"),
                        array("title" => "a2", "description" => "world",
                             "pic" => "http://host/a2.jpg", "url" => "http://host/"),
                    );

 */

class Wechat
{
    //似乎没什么用，放着用来自动完成吧。
    static $req_keys = array("Content", "CreateTime", "FromUserName",
        "Label", "Location_X", "Location_Y", "MsgType", "Scale", "ToUserName"
    );
    public $token;
    public $request = array();

    protected $funcflag = false;

    public function __construct($token)
    {
        $this->token = $token;
    }

	public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function set_funcflag()
    {
        $this->FuncFlag = true;
    }

    public function replyText($message)
    {
        $textTpl = <<<eot
<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[%s]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    <FuncFlag>%d</FuncFlag>
</xml>
eot;
        $req = $this->request;
        echo sprintf($textTpl, $req['FromUserName'], $req['ToUserName'],
                time(), 'text', $message, $this->funflag ? 1 : 0);
    }

    public function replyNews($arr_item)
    {
        $itemTpl = <<<eot
        <item>
            <Title><![CDATA[%s]]></Title>
            <Discription><![CDATA[%s]]></Discription>
            <PicUrl><![CDATA[%s]]></PicUrl> 
            <Url><![CDATA[%s]]></Url>
        </item>

eot;
        $real_arr_item = $arr_item;
        if (isset($arr_item['title']))
            $real_arr_item = array($arr_item); 

        $nr = count($real_arr_item);
        $item_str = "";
        foreach ($real_arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['title'], $item['description'],
                    $item['pic'], $item['url']);

        $time = time();
        $fun = $this->funcflag ? 1 : 0;

        echo <<<eot
<xml>
    <ToUserName><![CDATA[{$this->request['FromUserName']}]]></ToUserName>
    <FromUserName><![CDATA[{$this->request['ToUserName']}]]></FromUserName>
    <CreateTime>{$time}</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <Content><![CDATA[]]></Content>
    <ArticleCount>{$nr}</ArticleCount>
    <Articles>
$item_str
    </Articles>
    <FuncFlag>{$fun}</FuncFlag>
</xml> 
eot;
    }

    public function reply($callback)
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if(empty($postStr) || !$this->checkSignature())
            die("bad request");

        $this->request = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        $arg = call_user_func($callback, $this->request, $this);

        if (!is_array($arg))
            $this->replyText($arg);
        else
            $this->replyNews($arg);
    }

	private function checkSignature()
	{
        $args = array("signature", "timestamp", "nonce");
        foreach ($args as $arg)
            if (!isset($_GET[$arg]))
                return false;

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$tmpArr = array($this->token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>
