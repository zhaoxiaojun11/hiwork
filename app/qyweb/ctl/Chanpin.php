<?php namespace proj\ctl;

use \kernel\Rtn;
use \lib\Db5 as db;
class Chanpin extends \kernel\Controler
{
	// 产品主页：所有产品的展示页面
	public function index()
	{
	    $db = new db();
	    $canpin=$db::mine()->R('select id,tit,src from t_qyweb_canpin where stat=? ',['1']);
//	    foreach ($canpin as $row){
//            var_dump($row['ti);
//        }
        $this->assign('canpin',$canpin);
		$this->display('changpin_index.html');
	}

	// 产品内页：一个产品的展示页面
	public function one()
	{
		$this->display('changpin_one.html');
	}


	
}




