<?php namespace proj\ctl\admin;

use \kernel\Rtn;

/*
 * admin,xinxi
 * 后台信息管理
 * chy 20200427144829
 */

class Xinxi extends \kernel\Controler
{

    //页面访问地址： /admin,xinxi-index
    public function index()
    {
        echo '后台信息管理 - 主页面';

    }

    
	// 后台主页
	public function admin()
	{
		self::isLogined();
		echo '<h1>欢迎来到 天猫后台</h1>';
		var_dump("用户是：{$_COOKIE[self::UKEY]}");
		$this->display('user/logout.tpl');
	}


    
	// 收藏页面
	public function shoucang()
	{	
		self::isLogined();
		echo '<h3>欢迎来到 收藏页面</h3>';
		var_dump("用户------是：{$_COOKIE[self::UKEY]}");
		$this->display('user/logout.tpl');

	}


}
