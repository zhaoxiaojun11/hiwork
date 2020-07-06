<?php namespace proj\ctl\admin;

use \kernel\Rtn as rtn;
use \lib\Cookie as ck;
use \proj\ctl\Yonghu as user;
use \lib\Db5 as db;

class Index extends \kernel\Controler
{
	// 在构造函数中统一对用户权限进行控制
	public function __construct()
	{
		// var_dump($_COOKIE);exit;
		if(user::uid()==null){
			rtn::warning("请登陆之后再访问！<a href='/yonghu.html'>登陆</a>");
		}
	}

	public function index()
	{
		$uinf = db::mine()->R('select id,usr,nick,last,ctime from t_myh_web where id=?', [user::uid()], 1);
		$this->assign($uinf);
		$this->display('admin/index.html');
	}
	public function deft(){
        $this->display('admin/def.html');
    }
}
