<?php namespace proj\ctl;

use \lib\Db5 as db;
use \kernel\Rtn as rtn;
use \lib\Cookie as ck;
use \lib\Session as ss;

class User extends \kernel\Controler
{

	// 用户标记：标记当前登陆用户
	protected const UKEY='uname';//登陆用户标记索引
	protected const YZMKEY='_yzm';//验证码标记索引

	/*
	 * 返回当前登陆用户的id
	 * return 已登陆则返回user_id，否则为null;
	 * 
	 * 说明：可以做为用户是否已登陆的判断
	 */
	public static function uid()
	{
		return ck::get(self::UKEY);
	}

	// 主页：一般为登陆页
	public function index()
	{
		$this->login();
	}


	// 用户注册页面
	public function reg()
	{
		$this->display('user/reg_0.tpl');
	}


	//注册的后台处理页面
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
		$db = new db();
		$uinf = $db::mine()->R('select usr  from t_myh_web where usr=?', [$username], 1);
		
		if($uinf==true){
			rtn::err('用户名已注册');
		}
		$c = $db->I('t_myh_web', [
			'usr'=>$username,
			'pwd'=> password_hash($password,PASSWORD_BCRYPT),	
		]);

		// var_dump($c);
		$c===false ? rtn::err('服务器错误：') :  rtn::okk();		
	}


	// 登陆的后台判定页面
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
		if($yzm!=ck::get(self::YZMKEY)){
			rtn::err('验证码错误！');
		}

		$username = $rqt->input('username', 'mail');
		$password = $rqt->input('password', 'regexp',[
			'options'=>[
				'regexp'=>'/^[a-zA-Z0-9_.-]{3,6}$/',
			]
		]);

		$db = new db();
		// 用户名 和 密码 的验证
		$r = $db::mine()->R('select usr,pwd,id from t_myh_web where usr=?', [$username], 1);

		// 匹配用户名
		if($r==false) rtn::err('服务器错误！');
		if(empty($r)) rtn::err('用户名不存在！');
		
		// 匹配与用户名对应密码
		if(\password_verify($password,$r['pwd'])==false){
			rtn::err('密码错误');
		}
		// 标记登陆用户：2h有效
		ck::set(self::UKEY, $r['id'], 3600*2);

		// 返回成功的结果
		rtn::okk('登陆成功！');
	}

	// 登陆页
	public function login()
	{
		$this->display('user/login_0.tpl');
	}

	// 随机验证码生成页面
	public function yzm()
	{
		// 1.生成验证码
		$n = rand(1000, 9999);
		// 2.写入会话
		ck::set(self::YZMKEY, $n);
		// 3.返回结果
		rtn::okk('ok', $n);
	}


	//退出页面
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
	public function cs(){
		$db=new db();
		$ps=$_GET['ps'];
		$us=$_GET['us'];
		$r = $db::mine()->R('select usr,pwd,id from t_myh_web where usr=?', [$us], 1);
        if( \password_verify($ps,$r['pwd'])==false){
			rtn::err('密码错误');
		}
		// var_dump($ps);
		var_dump($r['pwd'],$r['usr']);
	}
}

