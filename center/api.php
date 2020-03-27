<?php
//HTTP-API PHP 机器人消息中转中心
//支持多机器人 跨服务器中转等
//By CH
/*
提交功能：
正则留空
提交URL填写内网此文件的访问地址，例如：http://127.0.0.1/robot/center/api.php
推送功能：
服务启动打勾
启动验证不打勾
监听端口默认22731，多机器人时每个机器人端口应不同
允许推送IP：php文件和机器人在一台机器上时：127.0.0.1 若外网可填0.0.0.0
Key Secret均留空
定时任务：
不开启
均留空
开发者模式选择开启
*/
//HTTP-API设置方法
//将本文件的路径设置到机器人的提交URL中
/*----------------------------------------------------------------------------------------------------------------------------------*/
/** 这里是配置区开始*/
// $proxy_url="../../client/api/getMsg.php";//中转服务器相对本文件的相对地址（不建议使用）
$proxy_url="http://127.0.0.1/tbapi/ch/robot/client/api/getMsg.php";//中转服务器HTTP地址（推荐）
$debugMode=true;//true为启用调试模式，将远程服务器返回的数据存入result.txt文件中
$debugQQ="877562884";//当调试模式为true时且此项不为空时，远程服务器返回的数据将通过qq消息私聊的方式通过机器人发送给此项的qq号码，数据为空将不发送
$debugGroup="";//当调试模式为true时且此项不为空时，远程服务器返回的数据将通过qq消息群聊的方式通过机器人发送给此项的qq群，数据为空将不发送

$testMode=true;//true为启用测试模式
$testText="你好";//测试模式下发送的消息触发回复消息测试，设置为你好，测试模式为true时，向机器人发送你好，将得到回复“你好啊！”
$max_time=10;//中转多久无响应放弃（秒）
$con=null;//数据库
$con=new mysqli('127.0.0.1','root','数据库密码',"robot");//数据库配置，不配置数据库就注释这一行
/*
//数据库会记录所有接收到的消息，没有数据库不影响核心功能
数据库结构：如不支持json可用text代替
CREATE TABLE `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `text` json DEFAULT NULL,
  `createTime` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/

/** 下面就不用配置了，配置结束*/
header("Content-Type:text/html;Charset=utf8");//设置编码，必需
include 'HTTPSDK.php';
use ksust\http_api\HTTPSDK;
$input=file_get_contents("php://input");
if($input=='')die(json_encode("test success!"));//test
$timer_msg=json_decode(urldecode($input));
if($timer_msg&&($timer_msg->Type??0)==="30001"){//定时器
    // if(!$con)die("{}");
    // $raw=$con->real_escape_string(urldecode($input));
    // $qq=$con->real_escape_string($timer_msg->Myqq??die);
    // $con->query("INSERT INTO `robot_active`(`raw`, `qq`) VALUES ('$raw','$qq')");
    die("{}");
}
$msg = json_decode($input);
if($msg&&isset($msg->pushRobotData)){//push
    $port=$msg->port??die;
    if(!$port)die;
    $push = HTTPSDK::httpPush('http://127.0.0.1:'.$port);
    $json=$msg->pushRobotData;
    die(json_encode(call_user_func_array([$push,$json->type],$json->data)));
}
$msg = urldecode(urldecode($input));//json字符串
function set_log($text){
    global $con;
    if(!$con)return;
    $text=$con->real_escape_string($text);
    $con->query("insert into `log`(`text`) values('$text')");
}
set_log($msg);
$arr=[];
$data=json_decode($msg);
if(strpos($proxy_url,"http://")===0||strpos($proxy_url,"https://")===0){
    $conn = curl_init($proxy_url);//中转
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);//参数1  不显示
    curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($conn, CURLOPT_TIMEOUT, $max_time);
    curl_setopt($conn, CURLOPT_POST, 1);
    curl_setopt($conn, CURLOPT_POSTFIELDS, $msg);
    curl_setopt($conn, CURLOPT_HEADER, 0);
    $result=curl_exec($conn);
    curl_close($conn);
    if($debugMode){//调试模式
        $handle=fopen("result.txt","a+");
        fwrite($handle,date("Y-m-d H:i:s")."|".$result);
        fwrite($handle,"\n");
        fclose($handle);
    }
    if($debugMode&&$result===false&&($debugQQ||$debugGroup)){
        $arr[]=[//返回数据中的data单元
            'ID' => md5(mt_rand(-time(), time()) . time()),//消息唯一标识，ID，可使用毫秒时间戳、UUID等
            'Type' => $debugGroup?2:1,//发送消息类型， 1 好友信息 2,群信息 3,讨论组信息 4,群临时会话 5,讨论组临时会话 ...,20001 群禁言,
            'SubType' => 0,//0普通，1匿名（需要群开启，默认0）
            'StructureType' => 0,//消息结构类型，0为普通文本消息（默认）、1为XML消息、2为JSON消息
            'Group' => $debugGroup,//操作或发送的群号或者讨论组号
            'QQ' => $debugQQ,//操作或者发送的QQ
            'Msg' => '远程服务器无响应！',//文本消息【标签等】，当发送类型为JSON、XML时为相应文本，禁言时为分钟数【分钟】
            'Send' => 0,//是否开启同步发送（1为开启同步发送【测试】，0为不开启【默认】）
            'Data' => ''//附加数据，用于特定操作等（文本型
        ];
    }
    if($debugMode&&$debugQQ&&!$result){//调试模式私聊的方式
        $arr[]=[//返回数据中的data单元
            'ID' => md5(mt_rand(-time(), time()) . time()),//消息唯一标识，ID，可使用毫秒时间戳、UUID等
            'Type' => 1,//发送消息类型， 1 好友信息 2,群信息 3,讨论组信息 4,群临时会话 5,讨论组临时会话 ...,20001 群禁言,
            'SubType' => 0,//0普通，1匿名（需要群开启，默认0）
            'StructureType' => 0,//消息结构类型，0为普通文本消息（默认）、1为XML消息、2为JSON消息
            'Group' => '',//操作或发送的群号或者讨论组号
            'QQ' => $debugQQ,//操作或者发送的QQ
            'Msg' => $result,//文本消息【标签等】，当发送类型为JSON、XML时为相应文本，禁言时为分钟数【分钟】
            'Send' => 0,//是否开启同步发送（1为开启同步发送【测试】，0为不开启【默认】）
            'Data' => ''//附加数据，用于特定操作等（文本型
        ];
    }
    if($debugMode&&$debugGroup&&!$result){//调试模式私聊的方式
        $arr[]=[//返回数据中的data单元
            'ID' => md5(mt_rand(-time(), time()) . time()),//消息唯一标识，ID，可使用毫秒时间戳、UUID等
            'Type' => 2,//发送消息类型， 1 好友信息 2,群信息 3,讨论组信息 4,群临时会话 5,讨论组临时会话 ...,20001 群禁言,
            'SubType' => 0,//0普通，1匿名（需要群开启，默认0）
            'StructureType' => 0,//消息结构类型，0为普通文本消息（默认）、1为XML消息、2为JSON消息
            'Group' => $debugGroup,//操作或发送的群号或者讨论组号
            'QQ' => '',//操作或者发送的QQ
            'Msg' => $result,//文本消息【标签等】，当发送类型为JSON、XML时为相应文本，禁言时为分钟数【分钟】
            'Send' => 0,//是否开启同步发送（1为开启同步发送【测试】，0为不开启【默认】）
            'Data' => ''//附加数据，用于特定操作等（文本型
        ];
    }
}else include $proxy_url;//此方法不建议，性能稍好些，但很多功能没有

if($data->Type==1&$data->Msg==$testText&&$testMode){
    $arr[]=[//返回数据中的data单元
        'ID' => md5(mt_rand(-time(), time()) . time()),//消息唯一标识，ID，可使用毫秒时间戳、UUID等
        'Type' => 1,//发送消息类型， 1 好友信息 2,群信息 3,讨论组信息 4,群临时会话 5,讨论组临时会话 ...,20001 群禁言,
        'SubType' => 0,//0普通，1匿名（需要群开启，默认0）
        'StructureType' => 0,//消息结构类型，0为普通文本消息（默认）、1为XML消息、2为JSON消息
        'Group' => '',//操作或发送的群号或者讨论组号
        'QQ' => $data->QQ,//操作或者发送的QQ
        'Msg' => '你好啊！',//文本消息【标签等】，当发送类型为JSON、XML时为相应文本，禁言时为分钟数【分钟】
        'Send' => 0,//是否开启同步发送（1为开启同步发送【测试】，0为不开启【默认】）
        'Data' => ''//附加数据，用于特定操作等（文本型
    ];
}
print(json_encode(['data' => $arr]));
?>