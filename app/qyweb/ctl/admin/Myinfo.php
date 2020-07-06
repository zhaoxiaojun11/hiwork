<?php namespace proj\ctl\admin;

use \kernel\Rtn as rtn;
use \lib\Cookie as ck;
use \proj\ctl\Yonghu as user;
use \lib\Db5 as db;
use \kernel\Request;
class Myinfo extends \kernel\Controler
{
	// 在构造函数中统一对用户权限进行控制
	public function __construct()
	{
		if(user::uid()==null){
			rtn::warning("请登陆之后再访问！<a href='/yonghu.html'>登陆</a>");
		}
    }
    //处理信息的修改
    public function infoChg(){
        $rqt=new \kernel\Request(1,1);
        $nick=$rqt->input('nick');
        $uid = $rqt->input('uid', 'int',[
			'options' =>[
				'min_range'=>1,
			]
		]);
        $d= db::mine()->exec('update t_myh_web set nick=? where id=?',[$nick,$uid ]);
        $d===false ? rtn::err(db::mine()->getErr()) : rtn::okk();
    }
    // 用户信息页面
    public function info()
    {
        $uinf = db::mine()->R('select id,usr,nick,last,ctime  from t_myh_web where id=?', [user::uid()], 1);
        $this->assign($uinf);
        $this->display('admin/info.html');
  
    }
    //处理密码页面
    public function xgpwd(){
        $rqt=new \kernel\Request(1,1);
        $dqpws = $rqt->input('dqpws');//旧密码
        $pwd = $rqt->input('pws');//新密码
        $uinf = db::mine()->R('select pwd  from t_myh_web where id=?', [user::uid()], 1);
        if(\password_verify($dqpws,$uinf['pwd'])==false){
            rtn::err('当前密码错误');
        }
        $d= db::mine()->exec('update t_myh_web set pwd=? where id=?',[password_hash($pwd,\PASSWORD_BCRYPT),user::uid()]);
        $d===false ? rtn::err('提交失败') : rtn::okk('修改成功');
    }
    // 修改密码页面
    public function chgPwd()
    {
        $uinf = db::mine()->R('select id,pwd,ctime  from t_myh_web where id=?', [user::uid()], 1);
        // $this->assign($uinf);
        $this->display('admin/chaPwd.html');   
    }
    //修改头像页面
    public function txinfo(){
        $this->display('admin/txinfo.html');
    }
    //上传头像页面
    //数据：传入头像图片base64
    public function uploadtx(){
        $rqt=new \kernel\Request(1,1);
        $img=$rqt->input('img');
        // 取回输入的json数据
        $post = json_decode(file_get_contents('php://input'), true);
        if(empty($post))  exit('未传入文件');

        // 1.取回post过来的base64值
        // $img = $post['img'];//图片base64值

        // 2.取回 base64 文件源值
        $arr = explode(',', $img);
        $img=base64_decode($arr[1]);

        // 3.取回 文件类型$type
        preg_match('/\/(.*);/', $arr[0], $r);
        $type=$r[1];
        // var_dump($type);exit;

        // 4.生成图片的保存路径
        $imgSrc=DOC_ROOT.'public/qyweb/admin/tx/'.user::uid().'.'.$type;
        var_dump($imgSrc);
        // 5.生成文件
        $length = file_put_contents($imgSrc, $img);

        // 6.判断图片是否生成成功，并返回结果
        if($length===false)
        {
            rtn::err('头像上传失败，请联系管理员！');
        }
        else
        {
            //图片地址是:{$imgSrc}
            rtn::okk("头像修改成功，刷新页面即可更新");
        }
    }
}