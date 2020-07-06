<?php namespace proj\ctl;

use \kernel\Rtn;
use \kernel\Request;
use \lib\DB4 as DB;
use Intervention\Image\ImageManagerStatic as Image;

/*
 * chy是所有模块的综合展示入口
 * 注：如模块有更多应用时，再独立创建控制器展示
 * 20190926160813
 */
class Chy extends \kernel\Controler
{
	// 默认主页：输出所有参数
	public function index(int $x=123, string $y='xiaoming')
	{
		echo '<h1>welcome to chy Index！</h1><hr/>';
		echo '当前控制器是：<b>{'.CTL.'}</b><br/>';
		echo '当前执行器是：<b>{'.ACT.'}</b><br/>';
		echo '当前的页码是：<b>{'.PN.'}</b><br/>';
		var_dump('输入的GET数据：', $this->prms);
		var_dump(func_get_args());
	}

	// get参数映射（1）
	// 测试uri: /chy-map1.html?age=15&name=小王
	public function map1(int $age, string $name)
	{
		var_dump($age, $name);
	}

	// get参数映射（2）
	// 测试uri: /chy-map2.html?key[]=789&key[]=999
	public function map2(array $key)
	{
		var_dump($key);
	}

	//错误的使用 20190619 lm:20190811114918
	public function error()
	{
		/*
		使用错误与异常
		注意：hw中将错误与异常分为：用户错误 与 系统运行错误
		1. 用户错误：由于用户的输入不符合要求导致的错误，如输出：404页面
		2. 系统运行错误：由于在运行中程序跑偏导致的错误
		*/

		// 错误页的输出，见：/index-sherr.html

		//运行中的错误与异常
		echo xxx();//调用不存在的函数
		// echo $abc12456;//返回不存在的变量
		// echo 'nihao err!'//语法错误:少分号		
	}	

	
	/*
	 * 数据传入
	 * 说明：request 与 verify的使用
	 * 示例url: /chy-request?uid=9&usr=afas45&mail=aa@sd.cf&age=15&idx[]=5&idx[]=7&idx[]=0
	*/
	public function request()
	{
		//请求数据：json+调试+验证（非校正）
		$rqt = new Request(true, true, 1);//说明json格式对错误是阻断的

		//以整型取uid
		$uid=$rqt->get('uid', 'int');
		//以整型取age,且要求9到24之间
		$age = $rqt->get('age', 'int',[
			'options' =>[
				// 'default'=>20,
				'min_range'=>9,
				'max_range'=>40,
			]
		]);
		//以邮箱格式取mail
		$mail = $rqt->get('mail', 'email');
		//以正则匹配usr
		$usr=$rqt->get('usr', 'regexp', ['options'=>['regexp'=>'/^[a-zA-Z]\w{5,30}$/']]);
		// 通过回调函数取idx[]
		$idx=$rqt->get('idx', 'func',['options'=>function($v){
			return $v<1 ? null : $v;
		}]);

		// 返回结果： 所有定义的变量 和 错误信息
		var_dump(get_defined_vars(), $rqt->getErr());exit;
	}

	//\lib\Curl方法接口
	//20190525
	public function curl()
	{
		//取回神马页面
		echo \lib\Curl::mine()->exc('https://m.sm.cn');
	}


	/**
	 * 邮箱库使用案例（vtp-mail已封装）
	 * chy 20190525
	 */
	public function mail()
	{
		$cmail = new \lib\Mail();
		//var_dump($cmail);exit;
		$b = $cmail->sendMail(['971657802@qq.com'=>'大航'], '今日天气', '15到27度，4到5级风');
		
		var_dump($b);
	}


	// DB模块 20190927115451
	public function db()
	{
		return '见 DataBase 控制器';
	}

	// 图片模块 20190927115451
	public function img()
	{
		return '见 图片 控制器';
	}

}




