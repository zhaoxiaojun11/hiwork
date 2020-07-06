<?php namespace kernel\traits;

/*
 * 控制器基础类－复用代码
 * CM: 20160321
 * LM: 20161007 在构造函数中加入unset，删除构架的参数__dir 和 __prm
 * 20160925 加入act()，用于处理不存在的影响器
 * 20170731 更改命名空间为clib
 * 20190519 多向继承模版类
 * 20190811170709 改为trait，否则"tatic $_mine"在子类中仍然是父类的
 *
*/

trait Baselib
{
	protected static $_mine;

	//单态实例 20190811170839
	//LM: CHY 20200509144506 兼容性修复：单态实例与实例化时参数不对应的问题
	public static function mine(...$args)
	{
		if(!static::$_mine){
			//后期静态绑定找回当前类名
			$class=static::class;
			// var_dump($class, static::class);exit;//必需是static

			// PHP5.6+，...operator解构（20200510083627不确定是否有bug）
			static::$_mine=new $class(...$args);

			// 通过映射方式到构造函数，同上面的解构
			// $reflect = new \ReflectionClass($class);
    		// static::$_mine = $reflect->newInstanceArgs($args);
		}
			
		return static::$_mine;
	}

}