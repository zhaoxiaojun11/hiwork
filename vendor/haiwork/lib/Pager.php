<?php namespace lib;

/*
 * 页码类
 * 20200118151356
 * 
 * 实例一：独立调用
 	$pager = \lib\Pager::mine();
	echo $pager->set([], $info);//生成页码
	echo $info;//输出分页信息

 * 实例二：配合分页调用
 * $rows = \lib\Db5::mine()->P(
		 'select * from news where id>? and uid=?',
		 [20, 45],
		 $page=['sh'=8]
	);
	$tags = \lib\Pager::mine()->set($page, $info);
	echo $tags;//分页标签
	echo $info;//分页信息

 * 
 * 
 	$pager = \lib\Pager::mine();
	echo $pager->set([], $info);//生成页码
	echo $info;//输出分页信息


 */

class Pager
{
	use \kernel\traits\Baselib;
	
	/*
	 * 输出分页页码标签
	 * @param $page [必] 由DB中P方法返回的分页信息[注意：必须传入pn和tp二个参数]
	 * return string 分页标签
	 * 20200118162738
	 */
	public static function set(array $page=[], &$info='')
	{
		//处理页码(用于调试，正式使用时用db::P的页码数组)
		$page+=['pn'=>$_GET['pn'] ?? 1, 'tp'=>10];
		//生成页码信息 
		$info="第{$page['pn']}页，共{$page['tp']}页";

		// 取回当前uri
		$uri = parse_url($_SERVER['REQUEST_URI']);

		//先删除原有页码
		unset($_GET['pn']);
		// 合成不含页码值的uri
		if(empty($_GET))
			$uri=$uri['path'].'?pn=';
		else
			$uri=$uri['path'].'?'.http_build_query($_GET).'&pn=';
		// var_dump($uri,  $page);exit;

		// 取数组索引变量并赋值
		extract($page, EXTR_OVERWRITE);
		// 上页，下页，首页，末页
		if($pn<=1){
			$pre = 'javascript:void(0)';
			$first = 'javascript:void(0)';
			$preCls = 'style="color:#AAA;"';
		}
		else{
			$pre = $uri.($pn-1);
			$first = $uri.'1';
			$preCls = '';
		}

		if($pn>=$tp){
			$next = 'javascript:void(0)';
			$end = 'javascript:void(0)';
			$nextCls = 'style="color:#AAA;"';
		}
		else{
			$next = $uri.($pn+1);
			$end = $uri.$tp;
			$nextCls = '';
		}

		// 返回页码标签
		return "<a title='首页' {$preCls} href='{$first}'>首页</a> <a title='上页' {$preCls} href='{$pre}'>上页</a> <a title='下页' {$nextCls} href='{$next}'>下页</a>	<a title='末页' {$nextCls} href='{$end}'>末页</a>";
	}
 
	/*
	 * 取得url中的路径部份
	 * 如：http://vtp.com/abc.php/cs-index/xx.html?abc=123&cc=c456
	   返回的是：/cs-index/xx.html
	 * 20200118153244
	 */
	// public static function uri($uri=''){
	// 	return $uri ?: $_SERVER['REQUEST_URI'];
	// }


	/*
	 * 重新生成url参数字串
	 * 注：$arr中单元用于覆盖query参数
	 * 如：http://vtp.com/abc.php/cs-index/adfa.html?cate=xy&pn=7
	   self:build(['pn'=>15]);//则重生成的参数是cate=xy&pn=15
	 * 20200118153238
	 */
	// public static function resetParams($arr=[]){
	// 	// 取出原get参数
	// 	parse_str($_SERVER['QUERY_STRING'], $row);
	// 	// 合并传入数据
	// 	$arr=$arr+$row;
	// 	// 生成新get参数
	// 	return http_build_query($arr);
	// }


}


