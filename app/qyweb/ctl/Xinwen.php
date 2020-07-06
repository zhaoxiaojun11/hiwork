<?php namespace proj\ctl;

use \kernel\Rtn as rtn;
use \lib\Db5 as db;
use \proj\mdl\News;
class Xinwen extends \kernel\Controler
{
	//所有
	public function index()
	{
		$pg=['sh'=>5];
		$db=new db();
		$rows=$db->P('SELECT id,tit,cnt,grp,stat,ctime FROM t_qyweb_news WHERE
		 stat=1 order by ord desc,id desc',[],$pg);
		 //生成页码
		 $pgtag = \lib\Pager::mine()->set($pg , $pginfo);

		$this->assign('rows',$rows);
		$this->assign('pgtag',$pgtag);
		$this->assign('pginfo',$pginfo);
		$this->display('xinwen_index.html');

	}

	//一个
	public function one()
	{	
		//传入ID
		$rqt = new \kernel\Request(1,1);//不以json
		$id=$rqt->get('id');
		//禁止错误的访问
//		if(!$id) rtn::warning();
//		$mobj=News::mine()->get($id);
		 $db=new db();
		 $rows=$db->R('SELECT id,tit,cnt,grp,stat,ctime FROM t_qyweb_news WHERE id=?' ,[$id] , 1);
		 $this->assign($rows);
		 $this->display('xinwen_one.html');

	}

	
}




