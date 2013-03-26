<?php
require_once('lib/util.php');
require_once('lib/db.php');
require_once('sixresult.php');
require_once('checkin.php');
require_once('song.php');
require_once('conf/foreign.conf.php');
require_once('weboshare/update51zaoqi2weibo.php');
//echo '<strong>Welcome to SAE!</strong>';

//    echo $_GET['echostr'];
//exit;
if(!isset($_GET['signature']) || !(Util::isWeiXinValid())) {
    header('Location: http://51checkin.sinaapp.com/doc/intro.html');
    echo "failed";
    exit;
} 

//全局数组
global $data;
$data = array();
//获取post信息
$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$fromUserName = $postObj->FromUserName;
$toUserName = $postObj->ToUserName;
$keyWord = trim($postObj->Content);
sae_debug("[keyword]".$keyWord);
$data['fromUserName'] = $fromUserName;
$userId = Util::getUserId($fromUserName);
$userName = Util::getUserName($fromUserName);
if(isset($userId)) {
    $userWbName = Util::getWbName($userId);
}
if(isset($foreignConf[$userId])) {
    date_default_timezone_set($foreignConf[$userId]);
}
$role = Util::getUserRole($fromUserName);
$data['toUserName'] = $toUserName;
$keyWord = str_replace("＃","#",$keyWord);
$data['keyWord'] = $keyWord;

$arrKeyWord = explode("#",$data['keyWord']);
$type = trim($arrKeyWord[0]);
$arrKeyWord[1] = trim($arrKeyWord[1]);
try {
    switch ($type) {
    case "注册6点组" :
    case "注册6点" :
    case "6点组" :
    case "6点" :
    case "六点" :
    case "六点组" :
    case "注册6" :
    case "注册六点" :
    case "注册六点组" :
    case "我要注册6点" :
    case "我要注册6点组" :
    case "我要注册六点" :
    case "我要注册六点组" :
        if(strlen($arrKeyWord[1]) <= 0) {
            echo Util::responseMsg("你未输入您的昵称,请回复'注册6点#您的昵称'");
            exit;
        }
        if(Util::hasUserExist()) {
            echo Util::responseMsg("您已注册过");
            exit;
        }
        $count = Util::register($arrKeyWord[1],1);
        $count ++;
        $msg = Util::getWelcome();
        echo Util::responseMsg("您是第".$count."位用户,".$msg."请您在每天早晨5点到6点30分回复'早'进行签到"); 
        exit;
    case "我要注册7点" :
    case "我要注册7点组" :
    case "注册7点" :
    case "7点" :
    case "七点" :
    case "7点组" :
    case "七点组" :
    case "注册7点组" :
    case "注册7" :
    case "注册七点组" :
    case "我要注册七点" :
    case "我要注册七点组" :
        if(strlen($arrKeyWord[1]) <= 0) {
            echo Util::responseMsg("你未输入您的昵称,请回复'注册7点#您的昵称'");
            exit;
        }
        if(Util::hasUserExist()) {
            echo Util::responseMsg("您已注册过");
            exit;
        }
        $count = Util::register($arrKeyWord[1],2);
        $count ++;
        $msg = Util::getWelcome();
        echo Util::responseMsg("您是第".$count."位用户,".$msg."请您在每天早晨6点到7点30分回复'早'进行签到"); 
        exit;

    case "我要签到" :
    case "再签" :
    case "再签到一次" :
    case "签到" :
    case "早" :
        $objCheckIn = new CheckIn();
        if(!Util::hasUserExist()) {
            echo Util::responseMsg('您尚未注册，请先发送"注册6点#您的昵称"或"注册7点#您的昵称"进行注册');
            exit;
        }
        $checkInResult = $objCheckIn->hasBeenChecked($fromUserName);
        if($checkInResult['result'] === true && $userId != 0) {
//            sae_debug('song');
            if($checkInResult['isOnTime'] == 1) {
                $objSong = new Song();
                $songUrl = Song::getSongUrl();
                echo Util::responseMusicMsg("点击左边播放","再回复'早'可更换歌曲",$songUrl);
            }
            else {
                echo Util::responseMsg("您今天已签到,不需要再签了");
            }
        } else {
            if($userId != 0) {   //test
                $result = $objCheckIn->checkIn($fromUserName);
                $defeat = $objCheckIn->getDefeatRes($result['rank'],$role);
                $maxCounter = $objCheckIn->getMaxCheckCounter($userId);
                $counter = $result['counter'];
                $isOnTime = $result['isOnTime'];
            }
            else {
                $counter = 1;
                $isOnTime = 1;
                $maxCounter = 10; 
                $defeat = 10;
                $result = array(
                    'rank' => 1,
                    'frontUsers' => array(
                        'a' ,'b','c',
                    ),
                );
            }
            if(isset($result['frontUsers'])){
                $strFrontUsers = implode(",",$result['frontUsers']);
                $rank = $result['rank'];
                if(isset($userWbName)) {
                    $userName = $userWbName; 
                }
                if($isOnTime) {
                    if($role == 1) {
                        $musicTitle = "签到成功,第".$rank."名";
                        $msg = "你是六点组本日第".$rank."个签到的,打败了".$defeat."的用户，已按时连续签到".$counter."天。但还有上升空间，来看看比你早的人吧，有[".$strFrontUsers."]";
                        $wbmsg = "@".$userName." 通过我要早起向大家问早,@".$userName." 是六点组本日第".$rank."个签到的,打败了".$defeat."的用户，已按时连续签到".$counter."天。";
                    }
                    if($role == 2) {
                        $musicTitle = "签到成功,第".$rank."名";
                        $msg = "你是七点组本日第".$rank."个签到的,打败了".$defeat."的用户，已按时连续签到".$counter."天。但还有上升空间，来看看比你早的人吧，有[".$strFrontUsers."]";

                        $wbmsg = "@".$userName." 通过我要早起向大家问早,@".$userName." 是七点组本日第".$rank."个签到的,打败了".$defeat."的用户，已按时连续签到".$counter."天。";
                    }

                } else {
                    if($role == 1) {
                        $checkInTime = "5点到6点30"; 
                    $msg = "你是六点组本日第".$rank."个签到的,但您已超出签到时间，您需要在".$checkInTime."进行签到,明天加油哦,及时签到可以听到美妙的歌曲哦";
                    $wbmsg = "@".$userName." 是我要早起六点组本日第".$rank."个签到的,但已超出签到时间，@".$userName." 需要在".$checkInTime."进行签到,明天加油哦";
                    } else if($role == 2) {
                        $checkInTime = "6点到7点30"; 
                        $msg = "你是七点组本日第".$rank."个签到的,但您已超出签到时间，您需要在".$checkInTime."进行签到,明天加油哦,及时签到可以听到美妙的歌曲哦";

                    $wbmsg = "@".$userName." 是我要早起七点组本日第".$rank."个签到的,但已超出签到时间，@".$userName." 需要在".$checkInTime."进行签到,明天加油哦";

                    }
                }
            }
            else {
                $musicTitle = "签到第一名";
                $msg = "您是本日的签到冠军，打败了".$defeat."的用户,已连续签到".$counter."天";
                $wbmsg = "@".$userName." 是本日的签到冠军，打败了".$defeat."的用户,已连续签到".$counter."天";
            }
            if(isset($wbmsg)) {
                $wbmsg .= " @".$userName." 已最长连续签到".$maxCounter."天http://51checkin.sinaapp.com/doc/intro.html";
                updateWeibo($wbmsg);
            }
//            
//            if(isset($musicTitle)) {
//             
//                echo Util::responseMusicMsg($musicTitle.",已连续".$counter."天","最长连续".$maxCounter."天","http://51checkin.sinaapp.com/data/angel.mp3");
//            } else {
//                echo Util::responseMsg('签到成功，'.$msg. " ps您的结果已直播到http://weibo.com/u/3209624664 ,微博昵称不对？发送'新浪微博#微博昵称'进行更改");
//            }
            if($isOnTime) { 
                echo Util::responseMsg('签到成功，'.$msg. ' 您曾最长连续签到'.$maxCounter.'天。'.",再签到一次（回复'早'),可以听到歌曲醒醒神哦");
            } else {
                echo Util::responseMsg('签到成功，'.$msg. " ps您的结果已直播到http://weibo.com/u/3209624664 ,微博昵称不对？发送'新浪微博#微博昵称'进行更改");
            }
        }
        exit;
    case "结果" :
        if($role === false) {
            echo Util::responseMsg('您尚未注册，请先发送"注册6点#您的昵称"或"注册7点#您的昵称"进行注册.');
            exit;
        } else if($role == 1){
            runSixResult();
            $msg = Util::getSixResult(); 
            echo Util::responseMsg($msg);
            exit;
        } else if($role == 2 ) {
            runSevenResult();
            $msg = Util::getSevenResult(); 
            echo Util::responseMsg($msg);
            exit;
        }
    case "获得七点结果":
            runSevenResult();
            $msg = Util::getSevenResult(); 
            echo Util::responseMsg($msg);
            exit;

    case "我的统计" :
    case "我得统计" :
    case "签到统计" :
    case "统计" :
        if(!Util::hasUserExist()) {
            echo Util::responseMsg('您尚未注册，请先发送"注册6点#您的昵称"进行注册');
            exit;
        }
        $msg = myResult($userId);
        echo Util::responseMsg($msg);
        exit;
    case "帮助" :
        $help = Util::getHelp();
        echo Util::responseMsg($help);
        exit;
    case "时间" :  
        $hour = date('G');
        $min = date('i');
        echo Util::responseMsg($hour.":".$min);
        exit;
    case "Hello2BizUser" :
        $msg = Util::getResiterWelcome();
        echo Util::responseMsg($msg);
        exit;
    case "起晚了" :
        $msg = "木关系，明天加油，可以再发个早签个到哦";
        echo Util::responseMsg($msg);
        exit;
    case "新浪微博":
        if($role === false) {
            echo Util::responseMsg('您尚未注册，请先发送"注册6点#您的昵称"或"注册7点#您的昵称"进行注册');
            exit;
        } 
        if(strlen($arrKeyWord[1]) <= 0) {
            echo Util::responseMsg("你未输入您的微博昵称,请回复'新浪微博#您的昵称'");
            exit;
        }
        Util::registerSinaWeiboName($userId,$arrKeyWord[1]);
        echo Util::responseMsg("设置新浪微博昵称成功"); 
        exit;
    case "歌":
        echo Util::responseMusicMsg("签到成功,第二名,来个歌精神下","点击左边播放","http://s1.mjbox.com/file/u/92815/2/2.mp3");
        exit;
    case 'angel' :
        echo Util::responseMusicMsg("Angel","点击左边播放","http://51checkin.sinaapp.com/data/angel.mp3");
        exit;
    default:
        throw new exception();
    }
} catch (exception $e) {
    $help = Util::getHelp();
    $help = "您得宝贵建议我们已记录".$help;
    echo Util::responseMsg($help);
    exit;
} 

/*
/*
if($keyword == "我是钟倩倩") {
    echo Util::responseMsg("曹建栋爱你");
} else {
    echo Util::responseMsg("你不是钟倩倩");
}
*/
