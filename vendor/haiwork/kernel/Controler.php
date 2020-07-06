<?php namespace kernel;

use \kernel\Rtn; 

/**
 * 控制器基类
 * CM: 20160321
 * LM: 20161007 在构造函数中加入unset，删除构架的参数__dir 和 __prm
 * 20160925 加入act()，用于处理不存在的影响器
 * 20170731 更改命名空间为clib
 * 20190519 多向继承模版类
 * 20190815145428 删除_index方法，加入_call方法
 * 20200504204716 执行器不存在时的默认方法由__call修改为funAssign
 */

abstract class Controler
{
	//引入模版类
	use traits\View;
	
	protected $prms;

	public function __construct($prms=null)
	{
		$this->prms = $prms;
	}

	// GET-PARAMS参数映射
	// return false;
	private function paramsMaping($args, $obj, $method)
	{
		// 取回定义的参数
		$reflect = new \ReflectionMethod($obj, $method);
		$defines = $reflect->getParameters();

		// 逐个对参数进行比对与取值
		$params = [];
		foreach ($defines as $define) {
			$name = $define->name;

			// 传入有参数
			if (isset($args[$name])) {				
				// 取得参数类型
				$type = $define->getType();
				assert($type instanceof \ReflectionNamedType);
				$type = $type->getName();

				// get参数仅支持:string(默认)、int、array
				switch($type){
					case 'int':
					case 'float':
						$type='is_numeric';
						break;
					case 'array':
						$type='is_array';
						break;
					default:
					$type='is_string';
				}

				// 判断类型
				if($type($args[$name])){
					$params[$name] = $args[$name];			
				}
				else{
					Rtn::err("参数{[$name}]类型检验不通过！");
				}

			}
			// 未传入参数，但有默认值
			elseif ($define->isDefaultValueAvailable()) {
				$params[$name] = $define->getDefaultValue();
			}
			// 必填参数，但无传入参数
			else {
				Rtn::err("参数[{$name}]缺失！");
			}
		}
		
		return $params;
	}

	// 默认控制器方法的分发入口：注意不是默认入口
	// 20200418095941
	public function funAssign($method, $args)
	{
		// 存在时调用
		if(method_exists($this, $method)){
			
			// 开启映射后，会消耗更多的资源，但使用上更简单
			$params= \GETMAP_ON ? $this->paramsMaping($args, $this, $method) : $_GET;
			// var_dump( $params, $defines, $args);exit;

			return call_user_func_array([$this, $method], $params);
		}else{
			\kernel\Rtn::e404('[A]受访页面不存在!');
		}

	}

}