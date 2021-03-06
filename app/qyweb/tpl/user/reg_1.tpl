<?php
use \proj\mdl\Qyweb as qy;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<title><?=qy::APP_NAME_ZH;?>-用户注册</title>
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
<link type="text/css" href="/static/css/bootstrap.min.css" rel="stylesheet" />
<style type="text/css">
html,body{height:100%;}
*{margin:0; padding:0; }
body{
	display: flex;
	align-items: center;
	background-image: linear-gradient(#3F51B5 50%, #E6E6E6 50%);

}

#box{
	margin: 0 auto;
    width: 450px;
    padding: 2em;
    background-color: #f6f3f3;
    border: 1px solid #CCC;
    border-radius: 5px;
    box-shadow: 1px 1px 4px #bbb;
}

#oth{
	margin:1em 0 2em;
	text-align: right;
}

ul{
	list-style-type:none;
}

#yzm0{
    padding: 6px 10px;
    border: 1px solid #607D8B;
    background-color: #bfc5b7;
}

#yzm{
	width:200px;
}

 .yzmbox .form-control{
	width:auto;
	display: inline-block;
}

.freeze{
	color:#CCC;

}

</style>
</head>
<body>

<section id="box">
	<h4 class="text-center"><?=qy::APP_NAME_ZH2;?>-用户注册(用户名+密码)1</h4>
	<div id="oth">使用 <a href="/yonghu.html">邮箱+验证码</a> 注册</div>

	<ul id="myform">
		<li  class="form-group">
			<input  placeholder="邮箱帐号" class="form-control" id="username" type="text">
		</li>
		<li  class="form-group yzmbox">
			<input placeholder="验证码" class="form-control" id="yzm" type="number">
			<b  class="form-control" id="yzm0">点击取得验证码(<span id="countDown">*</span>)</b>
		</li>
		<li class="text-center">
			<button id="btn" class="btn btn-primary" type="button">注册</button> | <a href="/<?=CTL;?>-login.html">登陆</a>
		</li>
	</ul>	
</section>
<?php include '_footer.tpl';?>

<script type="text/javascript" src="/static/js/axios.min.js"></script>	
<script type="text/javascript" src="/static/js/verify.js"></script>	
<script type="text/javascript">
let F={
	one(slt){
		return document.querySelector(slt);
	},
	all(slt){
		return document.querySelectorAll(slt);
	},

	// 标记发送验证码的状态
	isSendOk:true
}

// 设置重新取验证码的倒计时
F.setCountDown=function(){
	let ctdown = F.one("#countDown");
	let time=30;//30秒
	let t=setInterval(function(){
		ctdown.innerHTML=--time;
		if(time<=0){
			clearInterval(t);
			F.isSendOk=true;
		}
	}, 1000);

}




// 取得邮件验证码
F.one("#yzm0").onclick=function(){
	
			
	if(F.isSendOk==false) return;

	//1. 取得用户输入邮箱帐号（并验证）
	let username = verify.isEmail(F.one("#username").value);
	if(username==false){
		alert("用户名格式错误！");
		return;
	}

	let _this=this;
	this.className='freeze';//屏蔽样式
	F.isSendOk=false;//屏蔽状态
	var mail=F.one('#username').value;
	//2. 通过 后台 将 验证码 发送到 邮箱帐号
	axios({
		url:"yonghu-yzm",
		method:"post",
		data:{mail}
	}).then(function(r){	
		_this.className='';// 取消屏蔽状态	
		F.setCountDown();// 30秒后取消发送限止
		alert('验证码已发送到您的帐户：'+username+'！');

	}).catch(function(e){
		alert(e.response.data.msg);
		console.log(e.response.data);
	});
}


//点击提交
F.one("#btn").onclick=function(){
	var mail = F.one("#username").value;
	console.log(mail);
	// 判断信息填写是否完全及正确
	let yzm=F.one("#yzm").value;
	if(yzm<100000 || yzm>999999){
		alert("验证码填写错误！");
		return;
	}

	let username= verify.isEmail(F.one("#username").value);
	if(mail==false){
		alert("用户名格式错误！");
		return;
	}
	
	// alert("验证信息，并提交信息");
	axios({
		url:"/<?=CTL;?>-reging",
		method:"post",
		data:{yzm, mail}
	}).then(function(r){
		r=r.data;
		if(r.stat==1){
			alert('注册成功！');
		}
		else{
			alert('有问题，请通过控制台查看！');
			console.log(r);
		}
		
	}).catch(function(e){
		alert(e);
		console.log(e.response.data);
	});
	
}

</script>
</body>
</html>
