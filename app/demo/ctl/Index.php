<?php namespace proj\ctl;

use \kernel\Rtn;

/*
 * 基础控制器展示
 * chy 20190927110211
 */

class Index extends \kernel\Controler
{
	//视图解析
	public function index()
	{
		// echo $a;
		//直接返回内容
		//echo '<h1>欢迎来到Index主页</h1>';
		
		//解析数据视图
		$this->assign(['author'=>'Yuhang Chu', 'creatAt'=>'2013年9月']);
		$this->display('index.tpl');
	}

	public function view()
	{
		$this->assign([
			'header'=>'{我是变量header的值}',
			'mainer'=>'{我是变量mainer的值}',
			'footer'=>'{我是变量footer的值}',
		]);

		$this->display('header.tpl');
		$this->display('mainer.tpl');
		$this->display('footer.tpl');
	}

	//输出与显示
	public function shErr()
	{
		// 返回一个信息页面(含有栈调用信息)
		rtn::alert('这是一个以错误方式返回的页面!');

		// 返回一个信息页面
		// rtn::warning();

		//错误页面输出
		// Rtn::e404();
		// Rtn::e403();
		// Rtn::e500();
		
		// json格式[成功]时的输出
		// Rtn::okk();
		// json格式[失败]时的输出
		Rtn::err('用户名或密码错误', '-1', 401);
	}
	
}




