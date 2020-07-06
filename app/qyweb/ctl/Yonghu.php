<?php namespace proj\ctl;

use \lib\Db5 as db;
use \kernel\Rtn as rtn;
use \lib\Cookie as ck;
use \lib\Session as ss;

class Yonghu extends User
{

	// 注意：使用 邮箱+动态验证码
	public function reg()
	{
		$this->display('user/reg_1.tpl');
	}

	// 使用 邮件验证码登陆
	public function login()
	{
		$this->display('user/login_1.tpl');
	}


	// 邮件验证码的登陆后台20200428145817
	public function loging()
	{
		//1. 验证并取得帐户
		$mail = $this->yzUser();

		// 2. 写入数据表
		$db = new db;
		$r= $db->R('select id from t_myh_web where usr=?', [$mail], 1);
		// var_dump($r);

		if(empty($r))
			rtn::err('帐户不存在，请检查后再试！');
		else{		
			ck::set(self::UKEY, $r['id'], 7200);// 会话
			rtn::okk();// 返回成功
		}
	}

	// 注册的后台验证
	public function reging()
	{
		//1. 验证并取得帐户
		$mail = $this->yzUser();
		// $rqt = new \kernel\Request();
		// $mail = $rqt->input("mail");
		// 2. 写入数据表
		$db = new db();
		$c = $db->I('t_myh_web', [
			'usr'=>$mail,
			'pwd'=> password_hash(rand(100, 99999999), \PASSWORD_DEFAULT),	
		]);

		$c===false ? rtn::err($db->getErr()) : rtn::okk();
	}


	
	// 生成动态验证码，并发送给注册邮箱
	public function yzm()
	{
		//1.验证邮箱
		$rqt = new \kernel\Request();
		$mail = $rqt->input('mail', 'email');
		// var_dump($mail);

		//2. 生成6位随机码
		$code = rand(100000, 999999);

		//3. 写入会话(5分钟有效,包含“随机码”和“帐户”信息)
		ck::set(self::YZMKEY, self::setYzCode($code, $mail), 300);
		// rtn::okk();
		
		//4. 将验证码发送到 邮箱帐号
		$cmail = new \lib\Mail();
		$b = $cmail->sendMail([$mail], '后台注册验证码', '您在'.date('Y-m-d H:i:s').'注册了后台系统，您的验证码是：'.$code);
		
		$b ? rtn::okk() : rtn::err($cmail->getErr());	
	}

	// 验证用户和验证码
	protected function yzUser()
	{
		// 1. 验证传入的数据
		$rqt = new \kernel\Request();
		$mail = $rqt->input('mail','email');
		$yzm =  $rqt->input('yzm','int',[
			'options'=>[
				'min_range'=>100000,
				'max_range'=>999999,
			]
		]);

		// 取得本地验证码
		$localCode =ck::get(self::YZMKEY);
		// 用传入数据生成的验证码 与 本地验证码 匹配
		if($localCode==null || self::setYzCode($yzm, $mail) != $localCode ){
			rtn::err('验证码错误 或 已过期！');
		}

		// 验证成功后立即删除，防止重复提交
		ck::del(self::YZMKEY);
		
		// 返回帐户信息
		return  $mail;
	}

	// 设置本地会话的验证YzCode 
	// 由 随机码+mail 组成的base64值
	protected static function setYzCode($code, $mail)
	{
		return base64_encode($code.''.$mail);
	}


}

