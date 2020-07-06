<?php namespace proj\ctl;

use \kernel\Request;
use \kernel\Rtn as R;
use \lib\Cookie as ck;
use \lib\Db5 as db;
use \lib\Sjk;
use \lib\Session as ss;
use \proj\mdl\User as mdl;
use \lib\Mail as mail;
class Cookie extends \kernel\Controler{
  
    //用户标记
    private const UKEY='usname';
    private const YZMKEY='yzm';
    private const EYZM='eyzm';
    //返回当前登录用户的id
    public static function uid(){
        return ck::get(self::UKEY);
    }
    //视图页面
    public function dl(){
        $this->display('dl.html');
    }

    //验证页面
    public function login(){

        $rqt =  new \kernel\Request(true,true);
        $username=$rqt->input('username');
        $password = $rqt->input('password');
        $yzm = $rqt->input('yzm', 'int',[
			'options'=>[
				'min_rang'=>'1000',
				'max_rang'=>'9999',
			]
		]);
        
        if($yzm!=ck::get(self::YZMKEY)){
			R::err('验证码错误！');
		}
        $sjk= new \lib\Sjk();

        $r = $sjk->get_result('SELECT username,password FROM t_myh_user WHERE username=? ', [$username],1);
        if(empty($r)){
            R::err('用户名不存在！');
        }
        if(\password_verify($password,$r['password']==false)){
            R::err('用户名与密码不匹配');
        } 
        ck::set(self::UKEY,$username,180);
        R::okk('登陆成功');
    }

    //验证码生成页面
    public function yzm(){
        // 1.生成验证码 
        $n = rand(1000,9999);
        //2.写入会话
        ck::set(self::YZMKEY,$n,1000);   
        R::okk('ok',$n);
    }

    //用户注册
    public function zc(){
        $this->display('zc.html');
    }
    //忘记密码
    public function mima(){
        $this->display('mima.html');
    }
    public function wjmm(){
        $rqt = new \kernel\Request;
        $username=$rqt->input("username");
        $password=$rqt->input("password");
        $email=$rqt->input("email");
        $mdl=new mdl();
        $us=$mdl->__get("$username");
        $em=$mdl->__get('email');
        var_dump($us);
        //   if($username!=$us&&$email!=$em){
        //         R::err("用户名或邮箱不存在");
        //         return;
        //   }else{
        //       $mdl->__set("password",$password);
        //   }
    }
    //用户注册验证
    public function login_z(){
        $rqt = new \kernel\Request;
        $username=$rqt->input('username');
        $password = $rqt->input('password');
        $email=$rqt->input('email');
        $yzm_v=$rqt->input('yzm_v');

        
       //验证邮箱验证码是否正确
        $code=ck::get(self::EYZM);

 
        if($yzm_v!==$code){
            R::err('验证码错误');
        }
        ck::del(self::EYZM);

       //验证该用户名是否被注册 
         $mdl = new mdl();
        $p=$mdl->getRow(['username'=>$username]);
        if($p->username==null){
            $r=$mdl->add([
                'username'=>$username,
                'password'=>password_hash($password,\PASSWORD_BCRYPT),
                'email'=>$email
            ]);
        }else{
            R::err('用户名已被注册');
        }
        
        //发送验证码
        //如果该用户名没被注册则把 验证码和用户名写入会话
        if(!empty($r)){
            ck::set(self::UKEY,$username,380*2);
        }
        R::okk('注册成功');
    }
    //邮箱验证
    public function email_z(){
        $rqt = new \kernel\Request();
        $email=$rqt->input('email');
        //生成验证码
        $yzm = rand(100000, 999999);
        //验证码写入会话
        ck::set(self::EYZM,$yzm,300);
        //发送验证码
        $mail=new mail();
        $b = $mail->sendMail([$email=>'aa'],'您的验证码是','验证码'.$yzm);
        $b ? R::okk() : R::err();
    }
    //忘记密码验证


    //退出页面
    public function logout(){
        //清楚会话标记
        ck::del(self::UKEY);

        //转向登录
        header('location: /cookie-dl');
    }

    //超时页面
    public static function isLogined(){

        if( ck::get(self::UKEY)===null ){

            exit('未登录 或登录超时,请访问<a href="cookie-dl.html">登录页面</a>');

        }
    }
    // public function admin(){
    //     self::isLogined();
    //     echo "欢迎用户：".$_COOKIE[self::UKEY]."来到后台";
    //     echo '<a href="/cookie-dl.html">退出</a>';
    // }
    public function cs(){
        $rqt = new \kernel\Request;
        $email=$rqt->get('email');
        // $username=$rqt->get('username');
        //生成验证码
        // var_dump($email);
        $yzm = rand(100000, 999999);
        //验证码写入会话
        //发送验证码
        $mail=new mail();
        $b = $mail->sendMail([$email=>"aa"],'后台注册验证码', '您在'.date('Y-m-d H:i:s').'注册了后台系统，您的验证码是：'.$yzm);
        $b ? R::okk() : R::err();
        var_dump($b);
        // return $yzm;
    }
}
    