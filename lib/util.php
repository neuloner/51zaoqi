<?php
/***************************************************************************
 * 
 * Copyright (c) 2013 Baidu.com, Inc. All Rights Reserved
 * $Id$ 
 * 
 **************************************************************************/
 
 
 
/**
 * @file util.php
 * @author caojiandong(caojiandong@baidu.com)
 * @date 2013/02/18 15:51:01
 * @version $Revision$ 
 * @brief 
 *  
 **/
require_once('../conf/const.conf.php');
class Util {


    public function isWeiXinValid() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    

        $token = ConstConfig::TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg($contentStr,$msgType="text") {
        global $data;
        $time = time();
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <FuncFlag>0</FuncFlag>
            </xml>";   
        $resultStr = sprintf($textTpl, $data['fromUserName'], $data['toUserName'], $time, $msgType, $contentStr);
        return $resultStr;
    }

    public function responseMusicMsg($title,$desc,$url) {
        global $data;
        $time = time();
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
            <Music>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <MusicUrl><![CDATA[%s]]></MusicUrl>
            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
            </Music>
            <FuncFlag>0</FuncFlag>
            </xml>";  
        $resultStr = sprintf($textTpl, $data['fromUserName'], $data['toUserName'], 
            $time, $title, $desc,$url,$url);
        return $resultStr;
    }


    public function pushMsg($wxid,$contentStr,$msgType="text") {
        $time = time();
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <FuncFlag>0</FuncFlag>
            </xml>";   
        $resultStr = sprintf($textTpl, $wxid, ConstConfig::MYWXID, $time, $msgType, $contentStr);
        echo $resultStr;
        exit;
    }

    public function register($userName,$type) {
        global $data;     
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $wxName = $data['fromUserName'];
        $userName = mysqli_real_escape_string($link,$userName);
        $sql = "select count(id) from `users` limit 500";
        sae_debug("sql $sql");
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line69 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        $counter = $row[0];

        $sql = "insert into `users` (wxid,username,rstime) values ('$wxName','$userName',NOW())";
        sae_debug("sql $sql");
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line69 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $id = mysqli_insert_id($link);
        $sql = "insert into `role` (userid,wxid,role) values ($id,'$wxName',$type)";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line76 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $sql = "insert into userscore (userid,wxid) values ($id,'$wxName')";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line76 mysql_query_error".mysqli_error($link).$sql);
            throw new exception();
        }

        $sql = "insert into usercheckcount (userid) values ($id)";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line76 mysql_query_error".mysqli_error($link).$sql);
            throw new exception();
        }

        return $counter;
    }

    public function registerSinaWeiboName($userId,$sinaWBName) {
//        $sql = "insert into userSinaWBName values($userId,'$sinaWBName')"; 
        $sql = "insert into userSinaWBName values($userId,'$sinaWBName') 
            ON DUPLICATE KEY UPDATE wbname='$sinaWBName'" ; 
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error($link));
            throw new exception();
        }
        $res = mysqli_query($link,$sql);
        if( $res===false ) {
            sae_debug("[mysql_query_error]".mysqli_error($link).'[sql]'.$sql);
            throw new exception();
        }
        return true;
    }

    public function getWbName($userId) {
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $sql = "select wbname from userSinaWBName where userid = $userId";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line168 mysql_query_error".mysqli_error($link)."[sql]".$sql);
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        if(count($row) > 0) {
            return $row[0];
        } else {
            return null; 
        }

    }

    public function hasUserExist() {
        global $data;
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $wxName = $data['fromUserName'];
        $sql = "select * from `users` where wxid = '$wxName'";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line93 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        if(count($row) > 0)
            return true;
        return false;
        
    }

    public function getUserRole($wxId){
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $sql = "select role from `role` where wxid = '$wxId'";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line93 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        if(count($row) > 0)
            return $row[0];
        return false;
    }


    public function getUserId($wxId) {
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $sql = "select id from `users` where wxid = '$wxId'";
        sae_debug($sql);
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line168 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        return $row[0];
    }

    public function getUserName($wxId) {
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $sql = "select username from `users` where wxid = '$wxId'";
        sae_debug($sql);
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line168 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        return $row[0];
    }

    public function getSevenResult() {
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $sql = "select result from seventimeresult order by id desc limit 1";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line168 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        $sixResult = json_decode($row[0],true);
        $strCiUsers = implode(",",$sixResult['ciusers']);
        $strUnCisers = implode(",",$sixResult['unciusers']);
        return "按时签到的用户有[".$strCiUsers."]\n".
             "未按时签到的用户有[".$strUnCisers."]\n";
    }



    public function getSixResult() {
        $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
        if(!$link) {
            sae_debug("mysql_connect_error".mysqli_error());
            throw new exception();
        }
        $sql = "select result from sixtimeresult order by id desc limit 1";
        $res = mysqli_query($link,$sql);
        if( !$res) {
            sae_debug("line168 mysql_query_error".mysqli_error($link));
            throw new exception();
        }
        $row = mysqli_fetch_row($res);
        $sixResult = json_decode($row[0],true);
        $strCiUsers = implode(",",$sixResult['ciusers']);
        $strUnCisers = implode(",",$sixResult['unciusers']);
        return "按时签到的用户有[".$strCiUsers."]\n".
             "未按时签到的用户有[".$strUnCisers."]\n";
    }

    public function getWeek($time) {
        $week = ((date('w',$time)-1) >= 0) ? ((date('w',$time)-1)) : 7;  //计算周几  
        return $week;
    }

    public function getChineseWeek($time) {
        $week = ((intval(date('N',$time))-1) >= 0) ? ((intval(date('w',$time))-1)) : 7;  //计算周几  
        $week = date("N",$time);
        sae_debug(var_export($week,true));
        switch($week) {
            case 1:
               return "周一" ;
            case 2:
               return "周二"; 
            case 3:
               return "周三"; 
            case 4:
               return "周四"; 
            case 5:
               return "周五"; 
            case 6:
               return "周六"; 
            case 7:
               return "周日"; 
        }
    }


    public function getHelp() {
        $str = <<<EOF
感谢使用'我要早起',从今天起，做一个晨型人。如果您希望得到一个整体的介绍，请点击http://51checkin.sinaapp.com/doc/intro.html 如果您仅仅是想知道如何操作，请点击http://51checkin.sinaapp.com/doc/help.html
EOF;
/*
<1>发送'注册6点#您的昵称'进行注册6点组或者发送'注册7点#您的昵称'注册7点组,每个微信用户只能属于一个组
<2>发送'早'进行签到
<3>发送'统计'查看自己本周内的签到情况.
<4>发送'结果'查看最近一次本组内所有成员的签到结果。
<5>发送'新浪微博#您的微博昵称'可以在将您的签到同步到新浪微博时@到您。
回复其他信息视为对app的建议。
 */
        return $str;
    }

    public function getWelcome() {
        $str = <<<EOF
恭喜注册成功，感谢使用'我要早起',共同督促，共同提高。回复"帮助"获得使用方法,由于时间仓促，所以比较简陋，请见谅
EOF;
        return $str;
    }

    public function getResiterWelcome() {
        $str = <<<EOF
尊敬的用户，您好。感谢关注我要早起，我要早起是一款可以为自动为用户统计早起情况的app，能关注我要早起，想必您一定
是一个不甘平凡的人，生前何必久睡，死后自会长眠,让们一起早睡早起，打造健康生活，塑造理想人生。回复"帮助"或直接点
击http://51checkin.sinaapp.com/doc/intro.html查看使用帮助,开启早起旅程,从今天起，做一个晨型人。
EOF;
        return $str;
    }
}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
