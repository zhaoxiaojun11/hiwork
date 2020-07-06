<?php namespace proj\ctl;
use \lib\Mail as mail;
use \kernel\Rtn as rtn;
class Vv extends \kernel\Controler{
    public function mail(){
         $mail = new mail();
         var_dump($mail);
      
          $b = $mail->sendMail(['2603784292@qq.com'=>'大航'], '今日天气', '15到27度，4到5级风');
          var_dump($b);
	    	$b ? rtn::okk() : rtn::err();

        }
}