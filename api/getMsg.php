<?php
if(isset($data)){//include方式
	$result=$data;
}else{//http方式
	$input=file_get_contents('php://input');
	if(!trim($input))die("empty");
	$result=json_decode($input);
}
// $handle=fopen("test2.txt","a+");
// fwrite($handle,json_encode($result));
// fwrite($handle,"\n");
// fclose($handle);
// die;
include_once "qqrobot.class.php";//https://github.com/ksust/HTTP--API
set_error_handler('phperror');//报错就自定义输出！！！这个很实用。
function phperror($type, $message, $file, $line){
	$error_qq="877562884";//报错推送的qq
	$error_group="";//报错推送到自己的小群可以设置免打扰
	if($error_qq)return qqrobot::sendPrivateMsg($error_qq,'机器人报错: '.$type.':'.$message.' in '.$file . ' on ' . $line . ' line .');
	//if($error_group)return qqrobot::sendGroupMsg($error_group,'机器人报错: '.$type.':'.$message.' in '.$file . ' on ' . $line . ' line .');
}
$Myqq=$result->Myqq??0;
if($Myqq=="机器人qq号")qqrobot::$port=22731;else//设置第一个机器人和端口
// if($Myqq=="机器人qq号")qqrobot::$port=22732;else//多机器人
die("此qq暂未绑定端口！");
$qq=$result->QQ;
$group=$result->Group??0;
$type=$result->Type;
$msg=$result->Msg;
if($type==4){//临时消息
	if(strpos($msg,"登录")!==false){
		qqrobot::sendMsg(4,$group,$qq,"先要加我为好友哦！");
		die;
	}
}else if($type==1){//私聊消息
	if($msg==="你好"){
		qqrobot::sendPrivateMsg($qq, "你好！");
		die;
	}
}else if($type==2){//群聊消息
	if($group!="1064110222")die;//只保留自己的群，其它群不触发
	if($msg==="你好"){
		qqrobot::sendGroupMsg($group, "你好！！！");
		die;
	}
}

?>