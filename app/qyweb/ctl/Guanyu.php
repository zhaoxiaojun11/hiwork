<?php namespace proj\ctl;

use \kernel\Rtn;

class Guanyu extends \kernel\Controler
{
	public function index()
	{
		$this->display('guanyu_index.html');
	}
	
	public function jieshao()
	{
		$this->display('guanyu_jieshao.html');
	}
	
}




