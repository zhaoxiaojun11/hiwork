<?php namespace proj\ctl;

use \kernel\Rtn;
use \kernel\Logger;

/* 
 * 微信平台（平台1）后台
 * 20200512105125
 */
class Wxidx extends \kernel\Controler
{
	// 公号的基本配置
	const CNF=[
		'appid'=>'wx582b5ae6697b869c',
		'token'=>'zxj_123_gu',
		'aeskey'=>'RuoSWGlITVFIcOwbwaUOlk6A8PS7t1ySUgzsy2FBYBj',
	];

	public function index()
	{
		if(isset($_GET["echostr"])){
			//进行签名验证
			$bool = $this->checkSignature();
			exit($bool ? $_GET['echostr'] : 'err');
		}
		else{
			//一般信息的接收
			var_dump('一般信息的接收与处理');

		}	
	}
	//进行签名验证
	private function checkSignature()
	{
		// 签名、时间、随机数
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$token = self::CNF['token'];

		// 生成签名：将token、timestamp、nonce三个参数进行字典序排序 2）将三个参数字符串拼接成一个字符串进行sha1加密 
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		// 签名验证：对比生成的签名 与 微信传入的签名
		return $tmpStr == $signature;
	}
	
}




