<?php namespace kernel\error;

use \kernel\Rtn;
use \kernel\Request;
use \kernel\Logger;

/*
 * 开发者
 * LM:20190816114512 加入记录错误日志
 */

class VtpErr 
{
	// 错误入口: 注册错误机制
	// 注册错误/异常语柄 到 Erun
	public static function exc()
	{
		//显示控制
		//1. 错误报告级别(关闭php系统错误/异常显示)
		error_reporting(0);

		// 捕获异常
		set_exception_handler('\kernel\error\VtpErr::dealExpt');
		// 捕获错误
		set_error_handler('\kernel\error\Erun::dealErr0');
		// 捕获其它未被捕捉或停止的
		register_shutdown_function('\kernel\error\Erun::dealErr1');
	}


	/**
	 * 错误与异常处理总入口 
	 * 注：所有的异常被自动捕获后，均在此处理 20190616
	 * 注1：此方法不用显式调用，self::register已绑定过，有错误时被自动调用
	 * 注2：a.有错误时，此方法作为错误的处理方法，接管错误
	 * 		b.有异常，且未被捕捉时，自动执行本方法
	 * 20190816115117
	 *
	 * 能捕获的错误有（持续更新）：
	 * 1. 语法错误 20200326113507
	 * 2. 不存在的函数 20200326113509
	 **/
	public static function dealExpt($Expt)
	{
		// var_dump('异常类名称是: '. get_class($Expt), __METHOD__.'调试错误输出：', $Expt);exit;
		/*
		//所有异常（含以异常形式抛出的错误）均转为运行异常进行处理 20200326135700
		if(get_class($Expt)!='kernel\error\Erun'){
			$Expt = new Erun($Expt->getMessage(), $Expt->getCode(), $Expt->getCode(), $Expt->getFile(), $Expt->getLine());
		}

		self::errCtrol($Expt);
		*/
		
		//所有异常（含以异常形式抛出的错误）均转为运行异常进行处理 20200326135700
		switch (get_class($Expt)) {
			
			//注意：用户级错误不由Eusr维护，而由Rtn维护与处理
			// case 'kernel\Error\Eusr':{
			// 	self::displayEusr($Expt);
			// 	break;
			// }
			
			// 运行异常
			case 'kernel\error\Erun':
				break;
			// 其它非确定异常均转化Erun
			default:{
				$Expt=new Erun($Expt->getMessage(), $Expt->getCode(), $Expt->getCode(), $Expt->getFile(), $Expt->getLine());
			}

		}
		
		self::errCtrol($Expt);	
	}

	//控制错误:显示与日志 20190816114454
	public static function errCtrol($expt)
	{		
		//错误日志
		if(\LOG_ON>0){
			//开启错误日志
			ini_set('log_errors', 'On');
			//严重&编译等 错误写入 日志
			ini_set('error_log', Logger::getLogFile());
			// 解析并记录错误日志20190814115102
			// var_dump('记录日志：', $expt);
			Logger::err($expt);
		}
		else{
			//不开启日志，注意：没有此行 错误日志将写入到 php.ini的错误路径
			ini_set('log_errors', 'Off');
		}

		//显示控制
		if(\ERR_ON>0){
			Request::isAjax() ? Rtn::Err($expt->getMessage()) : self::display($expt);
		}
		else{
			Request::isAjax() ? Rtn::Err('程序错误，请联系管理员解决！') : Rtn::E500('程序错误，请联系管理员解决！');
		}
	}



	//所有错误或异常的输出视图 20190816115009
	public static function display($expt)
	{
		// header("http/1.1 500 manMade Err");
		
		$data=[
			// 'etype'=> \method_exists($expt, 'getEtype') ? $expt->getEtype() : '',
			'message'=>$expt->getMessage(),
			'line'=>$expt->getLine(),
			'file'=>$expt->getFile(),
			'stack'=>(string)$expt,
			'line0'=>$expt->getLine()-10,
		];
		// var_dump($data);exit;

		//起始行必需不小于1
		if($data['line0']<1) $data['line0']=1;
		//1. 定位错误文件，并读取上下各10行
		$data['lines'] = self::_getFileLines($data['file'],$data['line0'],$data['line']+10 );

		// var_dump($data);exit;
		//2. 解析数据并载入视图
		unset($expt);//清除释放内存
		(new \kernel\View)
			->assign($data)
			->display('err/erun.tpl', true);
	}


	/** 返回文件从X行到Y行的内容(支持php5、php4)  
	 * @param string $filename 文件名
	 * @param int $startLine 开始的行数
	 * @param int $endLine 结束的行数
	 * @return string
	 */
	public static function _getFileLines($filename, $startLine = 1, $endLine=50, $method='rb')
	{
	    $r = array();
	    $count = $endLine - $startLine;  

	    $fp = new \SplFileObject($filename, $method);
	    $fp->seek($startLine-1);// 转到第N行, seek方法参数从0开始计数
	    for($i = 0; $i <= $count; ++$i) {
	        $r[]=$fp->current();// current()获取当前行内容
	        $fp->next();// 下一行
	    }

	    return array_filter($r); // array_filter过滤：false,null,''
	}

}
	