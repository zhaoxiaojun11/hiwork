<?php


namespace proj\ctl\admin;
use \lib\Db5 as db;
use \kernel\Rtn as rtn;
use \lib\Sjk as sjk;
use \proj\ctl\User as user;
use \proj\mdl\Canpin as mdCanpin;
use \kernel\Request as rqt;

class Canpin extends \kernel\Controler
{
    public function index(){
        $this->display('admin/canpin_add.html');
    }
    public function Canpinshow(){
        $this->display('admin/canpin_list.html');
    }
    public function CanpinList(){
        $stat = !isset($_GET['recycle']) ? 1 : 0;
        $page=['sh'=>5];
        $r = db::mine()->P('select id,uid,tit,src,stat,ctime from t_qyweb_canpin where stat=? order by ord desc, id desc', [$stat], $page);
        $r===false ? rtn::err(db::mine()->getErr()) : rtn::okk('ok', [
            'rows'=>$r,
            'page'=>$page
        ]);
    }
    public function CanpinDel(){
        $rqt = new rqt();
        $id=$rqt->get('nid');
        $op=$rqt->get('op');
        //先删除本地图片
        $sjk = new \lib\Sjk();//删除id为1005的行
        $mdl=$sjk->get_result("select src from t_qyweb_canpin where id=$id");


        $imgSrc=DOC_ROOT.'public/qyweb/'.$mdl[0]['src'];
        $un=unlink($imgSrc);

         // 调用模型
        $c=db::MINE()->D('t_qyweb_canpin', "id=$id");

        $c===false ? rtn::err() : rtn::okk();
    }
    public function CanpinAdd(){
        $rqt = new \kernel\Request(1,1);
        $tit=$_POST['biaoti'];
        if ($tit==''){
            rtn::err();
        }
        //获取图片的base64码
        $img=$_POST['img'];

        // 2.取回 base64 文件源值
        $arr = explode(',', $img);
        $img=base64_decode($arr[1]);

        // 3.取回 文件类型$type
        preg_match('/\/(.*);/', $arr[0], $r);
        $type=$r[1];
        $imgName='idx'.rand(1,100).'.'.$type;
        //保存路径
        $imgSrc=DOC_ROOT.'public/qyweb/images/chanpin/'.$imgName;
        // 5.生成文件
        $length = file_put_contents($imgSrc, $img);
        //保存数据库路径
        $dbSrc='images/chanpin/'.$imgName;
        // 6.存入数据库
        $db=new db();
            $c=$db->I('t_qyweb_canpin',[
            'uid'=>user::uid(),
            'tit'=>$tit,
            'src'=>$dbSrc,
          ]);
         $c ? Rtn::okk() :Rtn::err();
    }
    public function cs(){
        $un=DOC_ROOT.'public/qyweb/images/chanpin/idx31.jpeg';
        unlink($un);
//        echo $un;

    }
}