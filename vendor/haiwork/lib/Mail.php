<?php namespace lib;

use \kernel\Rtn;

/**
 * 邮件库 swiftmailer
 * PHPMailer-master 20170103版
 * 20190517 v1.0 由PHPMailer切换为swiftmailer
 * chy
 */

class Mail
{
	use \kernel\traits\Baselib;

	private $cnf;
	//记录错误
	private $emsg='';

	public function __construct(array $keyVals=[])
	{
		//载入配置
		$this->cnf = \cnf_merge('mail.php');


		// var_dump(APP_DIR, $this->cnf);exit;
	}


	/**
	 * $rUsr 接收者 array 如：[ 'xiaowang@126.com', 'xiaoli@qq.com' => '小李']
	 * $tit 标题 string
	 * $body 邮件主体 string，可以是html
	 * 
	 * 20190517
	 */
    public function sendMail($rUsr, $tit, $body)
    {
	    vendor('swiftmailer-swiftmailer');

		//创建连接
		$transport = (new \Swift_SmtpTransport($this->cnf['host'],465,'SSL'))
		  ->setUsername($this->cnf['usr'])
		  ->setPassword($this->cnf['pwd'])
		;

		//创建邮件类
		$mailer = new \Swift_Mailer($transport);

		//填补内容
		$msg = (new \Swift_Message($tit))
		  ->setFrom([$this->cnf['usr'] => $this->cnf['nick']])
		  ->setTo($rUsr)
		  ->setBody($body)
		  ;
		  
		//var_dump($msg);exit;

		try{
			$b = $mailer->send($msg);
			if($this->cnf['debug']) Rtn::okk($b);
			return true;
		}
		catch (\Throwable $e){
			// var_dump('catch',$e);exit;			
			$this->emsg=$e->getMessage();
			if($this->cnf['debug']) Rtn::err($e);
			return false;
		}

    }

	//取回错误信息
	public function getErr(){return $this->emsg;}


}