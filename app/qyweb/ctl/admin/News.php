<?php namespace proj\ctl\admin;

use \kernel\Rtn;
use \lib\Db5 as db;
use \proj\ctl\User as user;
use \kernel\Model as mdl;
use \kernel\Request as rqt;
use \proj\mdl\News as mdlNews;

/*
 * admin,xinxi
 * 后台信息管理
 * chy 20200427144829
 */


class News extends \kernel\Controler
{

    public function index()
    {
        echo '新闻模块： 所有新闻';

	}
	
    public function add()
    {
        $this->display('admin/news_add.html');
    }
        //添加新闻（后台）
     public function adding(){
      if(empty($_POST['biaoti']) || empty($_POST['content'])){
            Rtn::err('输入错误');
      }
     $dd='fhiaifhash';
      $c=db::mine()->I('t_qyweb_news',[
          'uid'=>user::uid(),
          'tit'=>$_POST['biaoti'],
          'src'=>'1',
          'cnt'=>$_POST['content'],
      ]);
      $c ? Rtn::okk() :Rtn::err('数据写入失败,请联系管理员');
    }
    //新闻列表（前台）
    public function all(){
        $this->display('admin/news_list.html');
    }
    //新闻列表（后台）
    public function alling(){
        $stat = !isset($_GET['recycle']) ? 1 : 0; 
        $page=['sh'=>5];
        $r = db::mine()->P('select id,uid,tit,src,grp,stat,ctime from t_qyweb_news where uid=? and stat=? 
order by ord desc, id desc', [user::uid(), $stat], $page);
        $r===false ? rtn::err(db::mine()->getErr()) : rtn::okk('ok', [
            'rows'=>$r,
            'page'=>$page
        ]);
    }
    // 新闻回收站列表(页面)
    public function recycle()
    {
            $this->display('admin/news_recycle.tpl');
    }
    // 新闻回收站恢复功能
    public function recycle_hf()
    {
        $rqt = new \kernel\Request(1,1);
        $id=$rqt->get('nid');
        $mdl= mdlNews::mine($id);
        $mdl->stat='1';
        $sa=$mdl->save();
        $sa==true ? Rtn::okk('ok') : Rtn::err('失败');
    }	 
       // 新闻回收站删除功能
    public function recycle_dl()
    {
        $rqt = new \kernel\Request(1,1);
        $id=$rqt->get('nid');
        $mdl= mdlNews::mine($id)->del();
        $mdl==true ? Rtn::okk('ok') : Rtn::err('失败');
    }	  
    //通过ID删除一行
    public function delRow()
    {
        $rqt = new rqt();
        $id=$rqt->get('nid');
        $op=$rqt->get('op');

        // // 调用模型
        $mobj = mdlNews::mine($id);
        if($op=='delete'){
            // 硬删除，数据会被永久删除
            $c = $mobj->del();
        }
        else{
            // 软删除（更改状态为0，表示数据进行入"回收站"）
            $mobj->stat=0;
            // var_dump($mobj);exit;
            $c = $mobj->save();
        }

        $c===false ? rtn::err($mobj->getErr()) : rtn::okk();
    }
}
