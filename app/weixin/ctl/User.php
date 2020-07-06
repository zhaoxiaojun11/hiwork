<?php namespace proj\ctl;

use \lib\Db5 as db;
use \kernel\Rtn as rtn;
use \lib\Cookie as ck;
use \lib\Session as ss;

class User extends \kernel\Controler
{
	// 用户标记：标记当前登陆用户
	private const UKEY='uname4664656';
	private const YZMKEY='_yzm';

	public function index()
	{
		$this->login();
	}

	// 注意：使用 邮箱+动态验证码
	public function regPro()
	{
		$this->display('user/reg_pro.tpl');
	}

	// 注册的后台验证
	public function regingPro()
	{
		// 1. 验证传入的数据
		$rqt = new \kernel\Request(true, true);
		$mail = $rqt->input('mail', 'email');
		$yzm =  $rqt->input('yzm', 'int',[
			'options'=>[
				'min_range'=>100000,
				'max_range'=>999999,
			]
		]);

		// 取得本地验证码 与 计算的验证码进行 匹配
		$localCode =ck::get(self::YZMKEY);
		if(!$localCode){
			rtn::err('非法访问');//没有通过前端发送验证码
		}

		//验证码生成函数
		$setLocalCode=function($code, $mail){
			return base64_encode($code.$mail);
		};

		if( $localCode!= $setLocalCode($yzm, $mail)){
			rtn::err('验证码错误');
		}

		// 验证成功后立即删除
		ck::del(self::YZMKEY);//验证码验证成功后立马删除

		// 2. 写入数据表
		$db = new db;
		$c = $db->I('t_myh_user', [
			'usr'=>$mail,
			'pwd'=> password_hash(rand(100, 99999999), \PASSWORD_DEFAULT),	
		]);

		// var_dump($c, $db->getErr());

		$c===false ? rtn::err($db->getErr()) : rtn::okk();
	}

	// 生成动态验证码，并发送给注册邮箱
	public function yzmPro()
	{
		//1.验证邮箱
		$rqt = new \kernel\Request();
		$mail = $rqt->get('mail', 'email');
		// var_dump($mail);

		//2. 生成验证码
		$yzm = rand(100000, 999999);

		//3. 写入会话
		//验证码生成函数
		$setLocalCode=function($code, $mail){
			return base64_encode($code.$mail);
		};

		ck::set(self::YZMKEY, $setLocalCode($yzm, $mail), 300);
		
		//4. 将验证码发送到 邮箱帐号
		$cmail = new \lib\Mail();
		$b = $cmail->sendMail([$mail=>'aa'], '后台注册验证码', '您在'.date('Y-m-d H:i:s').'注册了后台系统，您的验证码是：'.$yzm);
		
		$b ? rtn::okk() : rtn::err();	
	}

	// 使用 邮件验证码登陆
	public function loginPro()
	{
		$this->display('user/login_pro.tpl');

	}

	// 邮件验证码的登陆后台20200428145817
	public function logingPro()
	{
		// 1. 验证传入的数据
		$rqt = new \kernel\Request(true, true);
		$mail = $rqt->input('mail', 'email');
		$yzm =  $rqt->input('yzm', 'int',[
			'options'=>[
				'min_range'=>100000,
				'max_range'=>999999,
			]
		]);

		// 取得本地验证码 与 计算的验证码进行 匹配
		$localCode =ck::get(self::YZMKEY);
		if(!$localCode){
			rtn::err('非法访问');//没有通过前端发送验证码
		}

		//验证码生成函数
		$setLocalCode=function($code, $mail){
			return base64_encode($code.$mail);
		};

		if( $localCode!= $setLocalCode($yzm, $mail)){
			rtn::err('验证码错误');
		}

		// 验证成功后立即删除
		ck::del(self::YZMKEY);//验证码验证成功后立马删除

		// 2. 写入数据表
		$db = new db;
		$r= $db->R('select id from t_myh_user where usr=?', [$mail], 1);
		// var_dump($r);

		if(empty($r)) rtn::err('用户不存在！');
		else{
			// 会话
			ck::set(self::UKEY, $r['id'], 7200);

			// 返回成功
			rtn::okk();
		}

	}


	public function cs()
	{

		var_dump( ck::get(self::UKEY) );

	}
























	// 用户注册
	public function reg()
	{
		$this->display('user/reg.tpl');
	}


	//处理注册信息
	public function reging()
	{
		$rqt = new \kernel\Request(true, true);

		// 验证吗是否ok
		$username = $rqt->input('username', 'mail');
		$password = $rqt->input('password', 'regexp',[
			'options'=>[
				'regexp'=>'/^[a-zA-Z0-9_.-]{3,6}$/',
			]
		]);

		$mdl = new \proj\mdl\User;
		$c = $mdl->add([
			'usr'=>$username,
			'pwd'=>password_hash($password, \PASSWORD_BCRYPT)
		]);

		// var_dump($c);
		$c===false ? rtn::err($mdl->getErr()) :  rtn::okk();
		
	}


	// 用户登陆的验证
	public function loging()
	{
		$rqt = new \kernel\Request(true, true);

		// 接收验证码
		$yzm = $rqt->input('yzm', 'int',[
			'options'=>[
				'min_rang'=>'1000',
				'max_rang'=>'9999',
			]
		]);

		// 验证 验证码 的值是否正确
		if($yzm!=ss::get(self::YZMKEY)){
			rtn::err('验证码错误！');
		}

		$username = $rqt->input('username', 'mail');

		$password = $rqt->input('password', 'regexp',[
			'options'=>[
				'regexp'=>'/^[a-zA-Z0-9_.-]{3,6}$/',
			]
		]);

		// var_dump($username, md5($password));exit;

		// 用户名 和 密码 的验证
		$db = new db();
		$r = $db->R('
			select usr,pwd,id 
			from t_myh_user
			where usr=?
		', [$username], 1);

		// var_dump($r, $db->getErr());exit;

		// 匹配用户名
		if($r===false) rtn::err('服务器错误！');
		if(empty($r)) rtn::err('用户名不存在！');
		

		// 匹配与用户名对应密码
		if(\password_verify($password, $r['pwd'])==false){
			rtn::err('用户名与密码不匹配！');
		}

		// 设定有户登陆1分名有效
		ck::set(self::UKEY, $username, 3600*2);

		// 返回成功的结果
		rtn::okk('登陆成功！');
	}

	// 登陆页
	public function login()
	{
		// 载入登陆视图
		$this->display('user/login.tpl');
	}




	// 验证码生成页面
	public function yzm()
	{
		// 1. 生成验证码
		$n = rand(1000, 9999);

		// 2. 写入会话
		ss::set(self::YZMKEY, $n);

		rtn::okk('ok', $n);
	}

	// 退回页面
	public function logout()
	{
		// 清除会话标记
		ck::del(self::UKEY);

		// 转向登陆
		header('location: /user-login');
	}



	
	public static function isLogined()
	{
		if(ck::get(self::UKEY)===null)
		{
			rtn::alert( '未登陆 或 登陆超时'.CTL.'-'.ACT.'，请<a href="/user-login">登陆</a>' );			
		}
	}


}

