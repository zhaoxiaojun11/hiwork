<?php namespace proj\ctl\admin;
use \kernel\Rtn as R;
use proj\ctl\Cookie as user;
use \lib\Db5 as db;
class Index extends \kernel\Controler
{
	public function __construct()
	{
		//在构造函数中统一对用户权限进行控制
		$uid=user::uid();
		//判断是否有登录 
		if($uid==null){
			R::warning("请登录后在访问! <a href='/cookie-dl'>登录</a>");
		}
		// $this->display('admin/index.html');

	}
	public function index()
	{	
		$uinf=db::mine()->R('SELECT id,name,ctime FROM t_mysh_user where username=?',
		[user::uid()],1);
		$this->assign($uinf);
		$this->display('admin/index.html');
	}
	public function nav()
	{
		$uinf=db::mine()->R('SELECT id,name,ctime FROM t_myh_user where username=?',
		[user::uid()],1);
		// var_dump(user::uid());
		var_dump($uinf);
	}
    //后台页面
    
}
