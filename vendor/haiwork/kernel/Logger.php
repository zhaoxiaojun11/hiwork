<?php namespace kernel;

/**
 * 日志类
 * v0 首发 err记录错误 20190816120032
 * 注：后续考虑 全局配置 和 加入channel
 * lm:20200510112202 加入note记录，以支持后台数据的记录
 */

class Logger
{
	// err日志的位置
	public static function getLogFile()
	{
		return DOC_ROOT.'run/logs/'.APP_NAME.'_'.date('Ymd').'.txt';
	}

	// note的位置 20200510112233
	public static function getNoteFile()
	{
		return DOC_ROOT.'run/note/'.APP_NAME.'_'.date('Ymd').'.txt';
	}

	
	/*
	 * 记录
	 * 使用场景：用于不能在前端返回数据，但需要检查数据时
	 * $var mix混合型 要记录的值或变量，标量或复合量均可
	 * $fileSite string 要保存的位置，默认：/run/note/项目名_日期.txt
	 * @return null
	 * 
	 * 调用示例一：\lib\tools::note('err undefined xxx');//记录字串
	 * 调用示例二：\lib\tools::note($_GET);//记录数组
	 * 
	 * 记录示例：
	 * 2020-05-10 11:13:02 At proj\ctl\Wxidx::cs:21
		array (
		'id' => '45',
		'nick' => 'xyz',
		)
	 * 
	 * 20200510112237
	 */
	public static function note($var, $fileSite='')
	{
		// note的默认记录地址
		if(!$fileSite) $fileSite = self::getNoteFile();

		//生成note
		$msg = \date('Y-m-d H:i:s');
		// 取回溯跟踪,: 不取参数,且只取到第一层
		$trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS,2);
		// var_dump($trace);exit;

		$file=$trace[1]['class'] ?? 'X';
		$func=$trace[1]['function'] ?? 'X';
		$msg.=" At {$file}::{$func}:{$trace[0]['line']}";

		$msg .= \PHP_EOL.\var_export($var, true).\PHP_EOL.\PHP_EOL;

		//记录note
		\file_put_contents($fileSite, $msg, \FILE_APPEND);
	}


	/**
	 * 将内容写入错误日志
	 * 
	 * 20190815115942
	 */
	public static function err($expt)
	{
		$txt = 'Note Error '.date('Y-m-d H:i:s').'-'.$_SERVER['REMOTE_ADDR'].PHP_EOL;

		$txt .= $expt->getEtype().':'.$expt->getMessage().PHP_EOL;

		$txt .= "Uri is {$_SERVER['REQUEST_URI']} In ".$expt->getFile()." At ".$expt->getLine().PHP_EOL.PHP_EOL;
		
		// 生成日志
		\file_put_contents(self::getLogFile(), $txt, \FILE_APPEND);
	}


	// 返回调用栈信息 20190816083107
	// 20200416164813 暂时不用
	public static function backbrace()
	{
        if (version_compare(PCRE_VERSION, '7.0.0', '>=')) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $backtrace = debug_backtrace(false);
        }

        return $backtrace[1];
	}
		
}