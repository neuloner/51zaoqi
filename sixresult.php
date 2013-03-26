<?php
/***************************************************************************
 * 
 * Copyright (c) 2013 Baidu.com, Inc. All Rights Reserved
 * $Id$ 
 * 
 **************************************************************************/
 
 
 
/**
 * @file sixresult.php
 * @author caojiandong(caojiandong@baidu.com)
 * @date 2013/02/19 09:12:12
 * @version $Revision$ 
 * @brief 
 *  
 **/

require_once('lib/util.php');
function runSixResult() {
    $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
    if(!$link) {
        sae_debug("mysql_connect_error".mysqli_error($link));
        throw new exception();
    }
    
    $sql = "select time from sixtimeresult order by id desc limit 1";
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }
    $row = mysqli_fetch_row($res);
    $lastResultTime = $row[0];

    $sql = "select userid from checkin where citime > $lastResultTime";
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }
    $row = mysqli_fetch_row($res);
    if(empty($row)) {
        return true; 
    }
    $users = array();
    $usersWxId = array();
    $sql = "select username,users.wxid from users,role where role.userid= users.id and role.role=1";

    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }

    while($row = mysqli_fetch_row($res)) {
        $users[] = $row[0];
        $usersWxId[] = $row[1];
    }
    //var_dump($users);
    $today = date('y-m-j');
    $sql = "select username,users.wxid from checkin,users where users.id = checkin.userid and cidate = '$today' and role=1 
            and isontime = 1";
    sae_debug($sql);
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }

    $ciusers = array();
    $ciusersWxId = array();
    while($row = mysqli_fetch_row($res)) {
        $ciusers[] = $row[0];
        $ciusersWxId[] = $row[1];
    }
    //var_dump($ciusers);
    $unciusers = array_diff($users,$ciusers);
    $unciusersWxId = array_diff($usersWxId,$ciusersWxId);
    var_dump($unciusers);
    $result = json_encode(array(
        'ciusers' => $ciusers,
        'unciusers' => $unciusers,
    ));
    $resultWxId = json_encode(
        array(
        'ciuserswxid' => $ciusersWxId, 
        'unciuserswxid' => $unciusersWxId
        )
    );
    $result = mysqli_real_escape_string($link,$result);
    $time = time();
    $sql = "insert into sixtimeresult (result,time,resultwxid) values ( '$result',$time,'$resultWxId')";
    sae_debug($sql);
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }
    return true;
}


function runSevenResult() {
    $link=mysqli_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS,SAE_MYSQL_DB);
    if(!$link) {
        sae_debug("mysql_connect_error".mysqli_error($link));
        throw new exception();
    }
    
    $sql = "select time from seventimeresult order by id desc limit 1";
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }
    $row = mysqli_fetch_row($res);
    $lastResultTime = isset($row[0]) ? $row[0] : 0;

    $sql = "select userid from checkin where citime > $lastResultTime";
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }
    $row = mysqli_fetch_row($res);
    if(empty($row)) {
        return true; 
    }
    $users = array();
    $usersWxId = array();
    $sql = "select username,users.wxid from users,role where role.userid= users.id and role.role=2";

    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }

    while($row = mysqli_fetch_row($res)) {
        $users[] = $row[0];
        $usersWxId[] = $row[1];
    }
    //var_dump($users);
    $today = date('y-m-j');
    $sql = "select username,users.wxid from checkin,users where users.id = checkin.userid 
            and cidate = '$today' and role=2 and isontime=1";
    sae_debug($sql);
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }

    $ciusers = array();
    $ciusersWxId = array();
    while($row = mysqli_fetch_row($res)) {
        $ciusers[] = $row[0];
        $ciusersWxId[] = $row[1];
    }
    //var_dump($ciusers);
    $unciusers = array_diff($users,$ciusers);
    $unciusersWxId = array_diff($usersWxId,$ciusersWxId);

    $result = json_encode(array(
        'ciusers' => $ciusers,
        'unciusers' => $unciusers,
    ));
    $resultWxId = json_encode(
        array(
        'ciuserswxid' => $ciusersWxId, 
        'unciuserswxid' => $unciusersWxId
        )
    );
    $result = mysqli_real_escape_string($link,$result);
    $time = time();
    $sql = "insert into seventimeresult (result,time,resultwxid) values ( '$result',$time,'$resultWxId' )";
    sae_debug($sql);
    $res = mysqli_query($link,$sql);
    if($res === false) {
        sae_debug("mysql_query_error".mysqli_error($link));
        throw new exception();
    }
    return true;
}

function myResult($userId) {
    $db = new Db();
    $week = date('N') - 1;  //计算周几
    $lastDay = time() - $week*24*60*60;
    $str = date('y-m-j',$lastDay);
    $time = strtotime($str);
    $sql = "select cidate,citime from checkin where isOnTime = 1 and userid=$userId and citime > $time";
    $res = $db->query($sql);
    $ciTimeName = array();
    while($row = mysqli_fetch_row($res)) {
        $ciTime = $row[1];
        sae_debug($ciTime);
        sae_debug(Util::getChineseWeek($ciTime));
        $ciTimeName[] = Util::getChineseWeek($ciTime);
    }
    $cicount = count($ciTimeName);
    $uncicount = $week + 1- $cicount;
    $strCiTime = implode(",",$ciTimeName);

    //get check count
    $sql = "select counter,maxcounter from usercheckcount where userid=$userId";
    $res = $db->query($sql);
    $row = mysqli_fetch_row($res);
    $counter = $row[0];
    $maxcounter = $row[1];
    $msg = "本周按时签到".$cicount."天,未按时签到".$uncicount.
            "天。已连续按时签到".$counter."天。最长连续签到".$maxcounter."天。";
    if($cicount > 0) {
        $msg .= "本周按时签到的时间为".$strCiTime;
    }
    return $msg;
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
