<?php namespace proj\ctl;

use \kernel\Rtn;
use \lib\Db5 as db;
class Index extends \kernel\Controler
{
	public function index()
	{
		//直接返回内容
		// exit('<h1>欢迎来到, 项目：'.\APP_NAME.'</h1>');
        $pg=['sh'=>5];
        $db=new db();
        $rows=$db->P('SELECT id,tit,cnt,grp,stat,ctime FROM t_qyweb_news WHERE
		 stat=1 order by ord desc,id desc',[],$pg);
        //产品展示
        $canpin=$db::mine()->R('select id,tit,src from t_qyweb_canpin where stat=? ',['1']);
        $this->assign('canpin',$canpin);
        $this->assign('rows',$rows);
		$this->display('index.html');
	}

	
}




