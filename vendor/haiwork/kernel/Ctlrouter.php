<?php namespace kernel;
/*
 * LM: 20160923
 * VIT核心类
 * 功能：传统模式下的url控制器路由，实现 URL分发, CTL控制器定向
 *
 * 更新记录
 * V1.0 2015-04-27 修复当uri最后为"/"时返回参数为空字串的bug，后续直接用isset对参数进行判断
 * V1.0 2015-06-05 增加对CTL控制器的判断，防止将核心库读成控制器以报错
 * 	
 * V2.0 2015-08-10
 *  增加调试模式，优化URL处理方式，并引入新的静态变量
 * 	2015-09-11 重新对"调试"和"定向"模式逻辑进行处理，优化处理方式。并加入对url末端的"?"后内容进行去除，防止出现"控制器或方法"不存在的错误
 * 	2015-09-30 更改APP的APP_CTL(控制器目录)后，控制器名称也会跟着变化，因此"引入APP控制器文件中间名 APP_CTL_MID"
 * 	2015-10-15 当控制方法不存在时，调用默认控制器index
 * 	2015-12-04 取得REQUEST_URI时，直接进行urldecode解码，防止中文参数被编码
 * 	2015-12-07 $this->params由初始设置为true，修改为array()。即参数没有时为空数组，用empty对其进行判断
 * 	
 * V3.0 2016-01-28 
 * 	3.1 为更好的优化url，减短url长度，并对uri的参数优化设置，重新构架对url的解析
 		url为三段：/ctl-act/prm1-prm2-prm3/pg_nmb.html
 * 	3.2 对控制器引入基类ctl.lib.php 20160322
 		默认影响器原先为index，但新的url中不用再有响应器地址段，且在index控制器中,响应index替代了构造函数，导致解析时直接执行了响应，解决此bug，将默认act名称更改为dft 20160322
 		增加url的健壮性，对址进行小写转化 20160323
 * 	3.3 对url解析逻辑进行梳理，参数段 和 页码段 明确区别开来 20160401
 * 
 * V4.0 20160923
 	4.1 exc改为静态方法；
 	4.2 更改uri的解析方式，由Apache服务器重写对参数进行分派，由htaccess设定（不再exc中进行解析）；
 	4.3 不再控制重写模式，由url自行控制；
 	4.4 页码不再单独设定，而由GET参数控制。
 	4.5 将不存在的响应统一定位到\inc\ctl::act()中，可以像钩子一样重置act。20161001
 	4.6 $prm由[]变更为null。20161002
 	4.7 加入权限控制。 20170729
 * 	
 * V5.0 20181227
 	1. url使用PATH_INFO取得并解析，不再用get
 	2. GET参数默认传入执行器（注：不对应型参）
 	3. 控制器使用大驼峰命名，默认控制器Index，默认执行器index
 *
 *  V5.1 20200411104409
 *  修改对404的判断:控制器与执行器不存在为404, 其它被"抛出"接管
 *  增加对框架配置 DEBUG_ON 的支持,以保护安全
 *  
 *  V5.2 20200418110049
 *  对uri进行验证，非标准uri均不再接受
 *  对框架要求的uri格式进行约束，不规则的也不再接受
 *  对控制器的分发入口进行约定，执行器不存在不在控制器中处理
 * 
 * uri格式说明
	合法的uri规则是：
		/index.php/控制器-执行器/参数1-参数2-参数x.html
	总体要求：
		uri中不可以出现特殊字符，合法的字符包含：[0-9a-zA-Z_\-\.\,]
		控制器与参数部分，合法的字符包含：[\w,]
		
	uri简化:
		index.php 可以省略, 如 /控制器-执行器/参数1-参数2-参数x.html
		后缀.html 可以省略，如有则必需是.html
		控制器-执行器 可以省略，但如果存在：
			控制器名称 必须首位是字母，不用区分大小写
			执行器 必须首位是字母，不区分大小写
			不可以省控制器而只有执行器
			错误的使用：/ctl-.html 或 /-act.html 或 /ctl-456.html 或 /123456.html 等
	
 * 
 * 20200418110321
 */

 class Ctlrouter
{
    /*
     * URL分发与定位：处理并取得url参数，并初始化 控制器
     * 
     	使用重写的URL,如：
			/adm-index/aa-bb-cc.html?sn=5&pn=3
	    不使用重写的URL,如：
			/index.php?__dir=adm-index&__prm=aa-bb-cc&sn=5&pn=3
		
		说明:
		1. 常规uri由2段'/'组成： 第一段("控制-影响")，第二段("参数1-参数2-参数x")
		2. 其它参数以 GET参数 传递，如：页码pn，显示条数sn
		3. URL后缀名为".html" 或 留空。
     *
     * 20160923
     */
    public static function exc()
    {
		// 解析为标准url(用于取出uri)
		// 说明非法的url为false,解析后没有path的也为非法
		$parseUri = parse_url($_SERVER['REQUEST_URI']);
		if(!$parseUri || !isset($parseUri['path']) ) Rtn::e404('访问规则错误');
		// var_dump($parseUri);exit;

		// var_dump($parseUri['path']);exit;	
		// 匹配格式: /index.php/控制器-执行器/参数1-参数x.html
		// $b = preg_match("/^\/(index\.php\/?)?(([\w\/\-,])*)(\.html)?$/", $parseUri['path'], $all);
		// $b = preg_match("/^\/(index\.php\/?)?(\w+(\-\w+)?\/?)*(\.html)?$/", $parseUri['path'], $all);
		// $b = preg_match("/^\/(index\.php\/?)?([a-zA-Z_,]\w*(\-[a-zA-Z_]\w*)?\/?)?$/", $parseUri['path'], $all);
		$b = preg_match("/^\/(index\.php\/?)?([a-zA-Z_][\w,]*(\-[a-zA-Z_]\w*)?)?(\/(\w+(\-\w+)?)*)?(\.html)?$/", $parseUri['path'], $uriParsed);//uriParsed[2]对控制器的解析，uriParsed[4]对参数的解析（以/开始）
		if($b===0) Rtn::e404('访问规则错误');
		// var_dump($b, $uriParsed);exit;

		// 解析控制器与方法
		$ctlAct=isset($uriParsed[2]) ? explode('-',$uriParsed[2]) : null;
		$uriVars=[
			'ctl'=>$ctlAct[0] ?? 'Index',//默认控制器
			'act'=>$ctlAct[1] ?? 'index',//默认执行器
			'prm'=>isset($uriParsed[4]) ? \explode('-',\substr($uriParsed[4],1)) : [],//默认空参数为null
		];

		// var_dump($uriVars);exit;
		//定义全局参数
		define('CTL', $uriVars['ctl']);//
		define('ACT', $uriVars['act']);//		

		// 控制器与执行器不存在则返回404
		// 处理控制器首字母大写和多级控制器
		if(strpos($uriVars['ctl'], ',')){
			$ctl=\explode(',', $uriVars['ctl']);
			\array_splice($ctl, -1, 1, \ucwords(\end($ctl)));//加入转换首字母大写后的最后一位
			$ctl = \implode('\\', $ctl);//以"\"分隔的类完整名称
		}
		else{
			$ctl = \ucwords($uriVars['ctl']);
		}
		$ctl = '\\proj\\ctl\\'.$ctl;//加入ctl前缀

		// var_dump($uriVars, $ctl);exit;

		// 调用与错误输出
		// 注1：开发模式下，调用系统错误视图输出
		// 注2：生产模式下，除控制器与执行器不存在外，运行错误均转化为e403
		try{
			$ctlObj = new $ctl($uriVars['prm']);
			call_user_func_array([$ctlObj, 'funAssign'], [$uriVars['act'], $_GET]);
		}
		catch(\Throwable $e){
			// var_dump($e);exit;
			if(\ERR_ON)
				\kernel\error\VtpErr::display($e);
			else{
				if(preg_match('/\\\proj\\\ctl\\\[A-Z]\w{0,}[\w\'\s]+not found$/', $e->getMessage()))
					Rtn::e404('[C]受访页面不存在！');
				Rtn::e403('服务器故障！');
			}
		}		
	}

}
