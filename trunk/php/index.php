<?php

/*
    File: index.php
    Author: felix021@gmail.com
    Date: 2012.11.26
    Usage: 公众平台的请求入口 + 示例(验证+消息)
 */

define("TOKEN", "TODO: Your Token Here");

require_once(dirname(__FILE__) . "/wechat.php");

$w = new Wechat(TOKEN);

//首次验证，验证过以后可以删掉
if (isset($_GET['echostr'])) {
    $w->valid();
    exit();
}

//回复用户
$w->reply("reply_cb");

//后续必要的处理...
/* TODO */
exit();

function reply_cb($request, $w)
{
    if ($request['MsgType'] == "location")
        return "暂不支持位置服务。";

    $content = trim($request['Content']);
    if ($content !== "url") //发纯文本
    {
        //$w->set_funcflag(); //如果有必要的话，加星标，方便在web处理
        if(!empty($content))
            return "回复: " . $content;
        else
            return "请说点什么...";
    }
    else //发图文消息
    {
        //* 单个图文
        return array(
            "title" =>  "hello",
            "description" =>  "world",
            "pic" =>  "http://www.felix021.com/mm/pic/x.jpg",
            "url" =>  "http://www.felix021.com/mm/test.php",
        );
        // */
        /* 多个图文，并加星标
        $w->set_funcflag();
        return array(
            array(
                "title" =>  "a1",
                "description" =>  "a1",
                "pic" =>  "http://www.felix021.com/mm/pic/x.jpg",
                "url" =>  "http://www.felix021.com/mm/test.php",
            ),
            array(
                "title" =>  "a2",
                "description" =>  "a2",
                "pic" =>  "http://www.felix021.com/mm/pic/fm.jpg",
                "url" =>  "http://www.felix021.com/mm/test.php",
            ),
        );
        // */
    }
}

?>
