<?php namespace kernel;
/*
 * 取外部数据工具库
 * ver2 修改错误数据返回内容，加入debug
 * 20190524 加入使用verify库对数据的验证
 * 20190601 因修改了Rtn对返回数据的处理，不再区分返回错误的数据类型，并取消了构造函数
 * 
 * 使用示例一: 验证器 20200308174937：
 	$q = new request;
 	$uid = $q->get('uid', 'int', ['options'=>['min_range'=>1, 'max_range'=>10000]]);
 * 使用示例二： 净化器 20200308174937：
 	$q = new request(true, true, 2);
 	$uid = $q->get('uid', 'int');//uid参数被净化成为int型，但不能执行选项option
 * 
 * 使用说明： 
 * 1. 在取外部数据时，可以执行 过滤（找出不合格的值） 和 净化（将值转为要求的量值）；
 * 2. 在对值进行返回时可返回 json阻止型 和 bool结果型
 * 3. 对错误信息的提示分为：调试模式(错误信息明确) 和 生产模式(不提示详细信息)
 * 
 */

class Request{
	
	use traits\Baselib;
	
	private $err = 'ok';//错误信息	
	private $iptData = null;// input流数据
	private $isRtnjson=true; //JSON返回开启控制：true开启，当出现错误时以Json方式返回错误并停止程序
	private $isDebug=false; //调试开启控制：true开启，可显示错误的描述
	private $engine=1; //验证引擎: {1:验证器, 2:清化器}

	/*
	 * @param isRtnjson JSON返回开启控制
	 * @param isDebug 调试开启控制
	 * @param engine 验证引擎 
	 * 20200308174913
	 */
	public function __construct($isRtnjson=true, $isDebug=false, $engine=1){
		$this->isRtnjson=$isRtnjson;
		$this->isDebug=$isDebug;
		$this->engine=$engine===1 ? '_getValConst' : '_getSanConst';
	}


	// 取得错误信息 20200307144600
	public function getErr(){return $this->err;}


	// 设置错误 20200307141553
	public function _setErr($err){
		$this->err = $err;
		// var_dump($this->isRtnjson);
		return $this->isRtnjson ? Rtn::err($err,'',403) : null;

	}


	/*
	 * 对取值的判断及错误的生成【核心方法】
	 *  @param	数组顺序为:[值val,方式method,索引key,类型type]
	 *  20200307144647
	 */
	public function _judge(array $queryRtn){
		// var_dump($queryRtn);

		if($queryRtn[0]===null){
			$err = $this->isDebug ? "取值不存在：[{$queryRtn[1]}] - [{$queryRtn[2]}]" : '系统未能取到有效数据';
			return $this->_setErr($err);
		}
		else if($queryRtn[0]===false){
			$err = $this->isDebug ? "[{$queryRtn[1]}]方式取[{$queryRtn[2]}]的值，经验证不符合[{$queryRtn[3]}]格式控制要求！" : '系统取值验证不通过';
			return $this->_setErr($err);
		}
		else return $queryRtn[0];
	}

	/*
	 * get数据接收【核心方法】
	 * @param	key[string]	 [必]	待取值的索引	
	 * @param	type[string]	 [必]	待取值的类型：默认为string且转码特殊字符
	 * @param	option[array] [必]	待取值的控制项 	同php手册中option项
	 * @return value[mix] 取值正常则返回取得的值，否则验证不通过，则返回null
	 *
	 * 注：如果结果要求返回json，则有错误会停止所有后续执行
	 *
	 * 使用示例一：
		$rqt = new Request(false, true);//不返回json,开启debug错误描述
		$mail = $rqt->get('mail', 'email');//验证邮箱地址

		//验证整数值
		$age = $rqt->get('age', 'int',[
			'options' =>[
				// 'default'=>20,
				'min_range'=>9,
				'max_range'=>30,
			]
		]);
		var_dump($mail, $age);
	 * 
	 * //用户正则式：字母开头的6到30位的字符
	 * $usr=$rqt->get('usr', 'regexp', ['options'=>['regexp'=>'/^[a-zA-Z]\w{5,30}$/']]);
	 *
	 * //密码：6到20位的字位
	 * $pwd = $request->input('pwd', 'regexp', ['options'=>['regexp'=>'/\w{6,20}/']]);
	 *
	 * //函数：
	 * $uid=$rqt->get('ids', 'func',['options'=>'\ctl\Chy::myfunc']; //调用函数
	 * $uid=$rqt->get('ids', 'func',['options'=>function($v){
			return $v>0 ? $v : false;
		}]);
	 * 
	 * 
	 * 20200307144652
	 */
	public function get($key, $type='string', $options=[]){
		// 前置错误则禁止
		if($this->err!='ok') return null;
		// 经验证取值
		$e = $this->engine;
		$this->resetOption($options);
		$r = filter_input(INPUT_GET, $key, $this->$e($type), $options);
		// 结果判断
		$this->_judge([$r,__FUNCTION__, $key, $type]);
		// 结果返回
		return $r;

	}

	/*
	 * post数据接收（同get）
	 * 20200307144243
	 */
	public function post($key, $type='string', $options=[]){
		if($this->err!='ok') return null;
		$e = $this->engine;
		$this->resetOption($options);
		$val=filter_input(INPUT_POST, $key, $this->$e($type), $options);
		$this->_judge([$val, __FUNCTION__, $key, $type]);
		return $val;
	}

	/*
	 * 数据流数据的接收（参数同get）
	 * 20200307144350
	 */
	public function input($key, $type='string', $options=[]){
		// 前置错误则禁止
		if($this->err!='ok') return null;

		// 取数据流
		if($this->iptData===null){
			$this->iptData = json_decode(file_get_contents('php://input'), true);

			// 数据流判断
			if($this->iptData===null){
				$err = $this->isDebug ? "未传入Input流数据" : '系统未能取到有效数据';
				return $this->_setErr($err);
			}
		}

		// 验证器验证
		$e = $this->engine;
		$this->resetOption($options);
		$r= isset($this->iptData[$key]) ? filter_var($this->iptData[$key], $this->$e($type), $options) : null;

		// var_dump($r, $this->getErr());exit;

		// 验证结果判断
		$this->_judge([$r, __FUNCTION__, $key, $type]);
		return $r;
	}


	/* 
	 * 重置过滤器中的option
	 * 说明：filter_var 中 option 形式如：['option'=>[]],写起来复杂，故简化之
	 * 示例：之前写的
	  	option = [
			'option'=>[
				'min_rang'=>1,
				'max_rang'=>100,
			] 
		 ]
		现在仍可以用上面的，此外，还可以简化成：
		option = [
				'min_rang'=>1,
				'max_rang'=>100,
		]
	 * 
	 */
	public function resetOption(array &$options)
	{	
		// 非空 且 也没有option选项,则加入option
		if(empty($options)==false && isset($options['options'])==false){
			$options = [
				'options'=>$options,
			];
		}
	}



	// 取得类型常量 20200307101547
	public static function _getValConst($type='string'){
		//类型标记转为小写
		$type = strtolower($type);

		//类型分派
		switch ($type) {
			case 'int': 	return FILTER_VALIDATE_INT;
			case 'boolean': return FILTER_VALIDATE_BOOLEAN;
			case 'float': 	return FILTER_VALIDATE_FLOAT;
			case 'ip': return FILTER_VALIDATE_IP;
			case 'email': return FILTER_VALIDATE_EMAIL;
			case 'url': return FILTER_VALIDATE_URL;
			case 'regexp': return FILTER_VALIDATE_REGEXP;
			
			case 'func': return FILTER_CALLBACK;
			// 字串则为净化, 编码特殊字符（???此处不确定）
			default: return FILTER_SANITIZE_STRING;
		}

	}


	// 取得类型常量 20200307101547
	public static function _getSanConst($type='string'){
		//类型标记转为小写
		$type = strtolower($type);

		//类型分派
		switch ($type) {
			case 'int': 	return FILTER_SANITIZE_NUMBER_INT;
			case 'float': 	return FILTER_SANITIZE_NUMBER_FLOAT;
			case 'email': return FILTER_SANITIZE_EMAIL;
			case 'url': return FILTER_SANITIZE_URL;
			case 'quotes': FILTER_SANITIZE_MAGIC_QUOTES;//应用 addslashes()
			case 'encode': FILTER_SANITIZE_ENCODED;//URL-encode 字符串，去除或编码特殊字符。
			case 'url': return FILTER_SANITIZE_URL;
			
			// 字串则为净化, 编码特殊字符（???此处不确定）
			default: return FILTER_SANITIZE_STRING;
		}

	}

	// 判断是否 Ajax 请求 20200326084711
	public static function isAjax()
	{
	    return 'xmlhttprequest' ==  strtolower($_SERVER['X-Requested-With']??'');
	}


}