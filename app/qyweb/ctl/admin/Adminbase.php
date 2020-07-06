<?php namespace proj\ctl\admin;

use \kernel\Rtn as rtn;
use \lib\Cookie as ck;
use \proj\ctl\User as user;
use \lib\Db5 as db;

abstract class Adminbase extends \kernel\Controler
{
	protected $loginUrl='/user.html';
	public function __construct()
	{
		if(user::uid()==null){
			rtn::warning("请登陆之后再访问！<a href='/yonghu.html'>登陆</a>");
		}
	}

	
}
