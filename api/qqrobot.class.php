<?php
class qqrobot{
	public static $asyn=0;//异步同步
	public static $port=0;//port
	private static $api="http://127.0.0.1/tbapi/ch/robot/center/api/api.php";//api url
	public static function handle($json){
		
	}
	public static function post($url,$data){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// curl_setopt($curl, CURLOPT_PROXY, "202.193.80.87:8080");//加上代理不卡顿，否则第一次连接172.16.0.63会卡顿
		// curl_setopt($curl, CURLOPT_PROXYUSERPWD, "yiban:GLUTyiban.cn");//加上代理不卡顿，否则第一次连接172.16.0.63会卡顿
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);//设置post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(["port"=>self::$port,"pushRobotData"=>$data]));//设置post的数据
		$da = curl_exec($curl);
		//var_dump("json=".urlencode(json_encode($data)));
		//var_dump($da);
		curl_close($curl);
		
		return $da;
	}
	public static function postAsyn($url,$data){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// curl_setopt($curl, CURLOPT_PROXY, "202.193.80.87:8080");//加上代理不卡顿，否则第一次连接172.16.0.63会卡顿
		// curl_setopt($curl, CURLOPT_PROXYUSERPWD, "yiban:GLUTyiban.cn");//加上代理不卡顿，否则第一次连接172.16.0.63会卡顿
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);//设置post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(["port"=>self::$port,"pushRobotData"=>$data]));//设置post的数据
		curl_setopt($curl, CURLOPT_TIMEOUT, 1);
		$da = curl_exec($curl);
		curl_close($curl);
	}
	public static function send($type,$data){
		if(self::$asyn==0)
			return json_decode(self::post(self::$api,[
				"type"=>$type,
				"data"=>$data
			]));
		else
			self::postAsyn(self::$api,[
				"type"=>$type,
				"data"=>$data
			]);
			return "asyn";
	}
	//下面是封装的函数//////https://github.com/ksust/HTTP--API
	/**
	 * 通用发送消息方法（为解决某些平台兼容问题）
	 * @param int $type 消息类型，见TypeEnum（如1为好友消息，2为群消息，3为讨论组消息，4为群临时消息等）
	 * @param string $group 群号
	 * @param string $qq QQ
	 * @param string $msg 消息内容
	 * @param int $structureType 消息结构类型 0普通消息，1 XML消息，2 JSON消息
	 * @param int $subType XML、JSON消息发送方式下：0为普通（默认），1为匿名（需要群开启）
	 * @return mixed
	 */
	public static function sendMsg($type, $group, $qq, $msg, $structureType = 0, $subType = 0){
		return self::send("sendMsg",[$type,$group,$qq,$msg,$structureType,$subType]);
	}
	/**
	 * 发送私聊消息
	 * @param string $qq
	 * @param string $msg
	 * @param int $structureType 消息结构类型 0普通消息，1 XML消息，2 JSON消息
	 * @param int $subType XML、JSON消息发送方式下：0为普通（默认），1为匿名（需要群开启）
	 * @return mixed
	 */
	public static function sendPrivateMsg($qq, $msg, $structureType = 0, $subType = 0){//向QQ发送消息
		return self::send("sendPrivateMsg",[$qq,$msg,$structureType, $subType]);
	}
	/**
	 * 发送群消息
	 * @param string $groupId
	 * @param string $msg
	 * @param int $structureType 消息结构类型 0普通消息，1 XML消息，2 JSON消息
	 * @param int $subType XML、JSON消息发送方式下：0为普通（默认），1为匿名（需要群开启）
	 * @return mixed
	 */
	public static function sendGroupMsg($groupId, $msg, $structureType = 0, $subType = 0){//群聊消息
		return self::send("sendGroupMsg",[$groupId, $msg, $structureType, $subType]);
	}
	/**
	 * 发送讨论组消息
	 * @param string $discuss
	 * @param string $msg
	 * @param int $structureType 消息结构类型 0普通消息，1 XML消息，2 JSON消息
	 * @param int $subType XML、JSON消息发送方式下：0为普通（默认），1为匿名（需要群开启）
	 * @return mixed
	 */
	public static function sendDiscussMsg($discuss, $msg, $structureType = 0, $subType = 0){//获取登录的QQ
		return self::send("sendDiscussMsg",[$discuss, $msg, $structureType, $subType]);
	}
	/**
	 * 向QQ点赞
	 * @param string $qq
	 * @param int $count 默认为1，作为消息的 Msg项
	 * @return mixed
	 */
	public static function sendLike($qq,$count){//点赞
		return self::send("sendLike",[$qq,$count]);
	}
	/**
	 * 窗口抖动
	 * @param string $qq
	 * @return mixed
	 */
	public static function sendShake($qq){//窗口抖动
		return self::send("sendShake",[$qq]);
	}

	/******************群操作、事件处理*************************/
	/**
	 * 群禁言（管理）
	 * @param string $groupId 群号
	 * @param string $qq 禁言QQ，为空则禁言全群
	 * @param int $time 禁言时间，单位秒，至少10秒。0为解除禁言
	 * @return mixed
	 */
	public static function setGroupBan($groupId, $qq = '', $time = 10){
		return self::send("setGroupBan",[$groupId, $qq, $time]);
	}
	/**
	 * 主动退群
	 * @param string $groupId
	 * @return mixed
	 */
	public static function setGroupQuit($groupId){
		return self::send("setGroupQuit",[$groupId]);
	}
	/**
	 * 踢人（管理）
	 * @param string $groupId
	 * @param string $qq
	 * @param boolean $neverIn 是否不允许再加群
	 * @return mixed
	 */
	public static function setGroupKick($groupId, $qq, $neverIn = false){
		return self::send("setGroupKick",[$groupId, $qq, $neverIn]);
	}
	/**
	 * 设置群名片
	 * @param string $groupId
	 * @param string $qq
	 * @param string $card
	 * @return mixed
	 */
	public static function setGroupCard($groupId, $qq, $card = ''){
		return self::send("setGroupCard",[$groupId, $qq, $card]);
	}
	/**
	 * 设置管理员（群主）
	 * @param string $groupId
	 * @param string $qq
	 * @param boolean $become true为设置，false为取消
	 * @return mixed
	 */
	public static function setGroupAdmin($groupId, $qq, $become = false){
		return self::send("setGroupAdmin",[$groupId, $qq, $become]);
	}
	/**
	 * 处理加群事件，是否同意
	 * @param string $groupId
	 * @param string $qq
	 * @param boolean $agree 是否同意加群
	 * @param int $type 213请求入群  214我被邀请加入某群  215某人被邀请加入群 。为0则不管哪种
	 * @param string $msg 消息，当拒绝时发送的消息
	 * @return mixed
	 */
	public static function handleGroupIn($groupId, $qq, $agree = true, $type = 0, $msg = ''){
		return self::send("handleGroupIn",[$groupId, $qq, $agree, $type, $msg]);
	}
	/**
	 * 是否同意被加好友
	 * @param string $qq
	 * @param boolean $agree 是否同意
	 * @param string $msg 附加消息
	 * @return mixed
	 */
	public static function handleFriendAdd($qq, $agree = true, $msg = ''){
		return self::send("handleFriendAdd",[$qq, $agree, $msg]);
	}
	
	/**
	 * 发群公告（管理）
	 * @param string $groupId
	 * @param string $title 内容
	 * @param string $content 信息
	 * @return mixed
	 */
	public static function addGroupNotice($groupId, $title, $content){
		return self::send("addGroupNotice",[$groupId, $title, $content]);
	}
	/**
	 * 发群作业（管理）。注意作业名和标题中不能含有#号
	 * @param string $groupId
	 * @param string $homeworkName 作业名
	 * @param string $title 标题
	 * @param string $content 内容
	 * @return mixed
	 */
	public static function addGroupHomework($groupId, $homeworkName, $title, $content){
		return self::send("addGroupHomework",[$groupId, $homeworkName, $title, $content]);
	}
	/**
	 * 主动申请加入群
	 * @param string $groupId 群号
	 * @param string $reason 加群理由
	 * @return mixed
	 */
	public static function joinGroup($groupId, $reason = ''){
		return self::send("joinGroup",[$groupId, $reason]);
	}
	/**
	 * 创建讨论组
	 * @param string $disName 讨论组名。并作为创建后第一条消息发送（激活消息）
	 * @param array $qqList 需要添加到讨论组的QQ号列表
	 * @return mixed 讨论组ID
	 */
	public static function disGroupCreate($disName, $qqList = []){
		return self::send("disGroupCreate",[$disName, $qqList]);
	}
	/**
	 * 退出讨论组
	 * @param string $disGroupId 讨论组ID
	 * @return mixed
	 */
	public static function disGroupQuit($disGroupId){
		return self::send("disGroupQuit",[$disGroupId]);
	}
	/**
	 * 踢出讨论组
	 * @param string $disGroupId 讨论组ID
	 * @param array $qqList 欲踢出的QQ号列表
	 * @return mixed
	 */
	public static function disGroupKick($disGroupId, $qqList = []){
		return self::send("disGroupKick",[$disGroupId, $qqList]);
	}
	/**
	 * 添加讨论组成员
	 * @param string $disGroupId 讨论组号
	 * @param array $qqList 欲添加的QQ号列表
	 * @return mixed
	 */
	public static function disGroupInvite($disGroupId, $qqList = []){
		return self::send("disGroupInvite",[$disGroupId, $qqList]);
	}
	/**
	 * 邀请QQ入群（管理+群员）
	 *
	 * @param string $groupId 群号
	 * @param string $qq QQ
	 * @return mixed 状态
	 */
	public static function groupInvite($groupId, $qq){
		return self::send("groupInvite",[$groupId, $qq]);
	}
	
	/*********************** 获取信息：注意获取反馈消息，通过ID识别 *******************************************/
	/**
	 * 获取陌生人信息
	 * @param string $qq
	 * @return mixed
	 */
	public static function getStrangerInfo($qq){//窗口抖动
		return self::send("getStrangerInfo",[$qq]);
	}
	/**
	 * 获取当前登陆的QQ
	 * @return mixed
	 */
	public static function getLoginQQ(){//获取登录的QQ
		return self::send("getLoginQQ",[]);
	}
	/**
	 * 获取当前QQ群列表
	 * @return mixed
	 */
	public static function getGroupList(){
		return self::send("getGroupList",[]);
	}
	/**
	 * 获取当前登陆QQ好友列表
	 * @return mixed
	 */
	public static function getFriendList(){
		return self::send("getFriendList",[]);
	}
	/**
	 * 获取指定群群成员列表
	 * @param string $groupId
	 * @return mixed
	 */
	public static function getGroupMemberList($groupId){
		return self::send("getGroupMemberList",[$groupId]);
	}
	/**
	 * 获取群公告
	 * @param string $groupId
	 * @return mixed
	 */
	public static function getGroupNotice($groupId){
		return self::send("getGroupNotice",[$groupId]);
	}
	/**
	 * 获取对象QQ赞数量
	 * @param $qq
	 * @return mixed
	 */
	public static function getLikeCount($qq){//获取对象QQ赞数量
		return self::send("getLikeCount",[$qq]);
	}
	/**
	 * 获取讨论组列表
	 * @return mixed
	 */
	public static function getDisGroupList(){
		return self::send("getDisGroupList",[]);
	}
	/**
	 * 获取QQ等级
	 *
	 * @param string $qq QQ
	 * @return mixed 等级
	 */
	public static function getQQLevel($qq){//获取QQ等级
		return self::send("getQQLevel",[$qq]);
	}
	/**
	 * 获取群成员名片
	 *
	 * @param string $groupId 群号
	 * @param string $qq QQ
	 * @return mixed 名片
	 */
	public static function getGroupMemberCard($group,$qq){//获取群成员名片
		return self::send("getGroupMemberCard",[$group,$qq]);
	}
	/**
	 * 查询QQ是否在线
	 *
	 * @param string $qq QQ
	 * @return mixed 是否在线
	 */
	public static function getQQIsOline($qq){//查询QQ是否在线
		return self::send("getQQIsOline",[$qq]);
	}
	/**
	 * 查询QQ是否好友
	 *
	 * @param string $qq QQ
	 * @return mixed 是否好友
	 */
	public static function getQQIsFriend($qq){
		return self::send("getQQIsFriend",[$qq]);
	}
	/**
	 * 获取当前QQ机器人状态信息（如是否在线）
	 *
	 * @return mixed 结构信息
	 */
	public static function getQQRobotInfo(){//获取当前QQ机器人状态信息（如是否在线）
		return self::send("getQQRobotInfo",[]);
	}
	/**
	 * 置正在输入 状态，发送消息撤销
	 *
	 * @param string $qq QQ
	 * @return mixed 状态
	 */
	public static function setInputStatus($qq){//置正在输入 状态，发送消息撤销
		return self::send("setInputStatus",[$qq]);
	}
}

?>