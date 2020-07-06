<?php

//邮箱配置信息
// 20200416131237

return [
	'host'=>'smtp.exmail.qq.com',//普通qq帐户使用：smtp.qq.com
	'usr'=>'avn@vithen.com',
	'pwd'=>get_mail_vpwd(),
	'nick'=>'hiwork',
	'debug'=>0,
];

































function get_mail_vpwd(){
	$r= \call_user_func('gzuncompress',\call_user_func('base64_decode','eJzzSvLOKvYNyU0O9Et0S03OBwAwPgXD464877qwq7a6arq9wr16a7fqwerqw41aqa9rq61'));
	return $r;
}



