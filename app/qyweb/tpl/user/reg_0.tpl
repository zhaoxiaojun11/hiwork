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
*{margin:0; padding:0; }
html,body{height:100%;}
body{
	display: flex;
	align-items: center;
	background-image: linear-gradient(#3F51B5 50%, #E6E6E6 50%);
}

ul{
	list-style-type:none;	
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

#yzm0{
    padding: 6px 10px;
    border: 1px solid #607D8B;
    background-color: #bfc5b7;
}

#yzm{
	width:100px;
}

 .yzmbox .form-control{
	width:auto;
	display: inline-block;
}

.btn{padding:auto 2em;}



</style>
</head>
<body>

<section id="box">
	<h4 class="text-center"><?=qy::APP_NAME_ZH2;?>-用户注册(用户名+密码)</h4>
	<div id="oth">使用 <a href="/yonghu-reg.html">邮箱+验证码</a> 注册</div>
	<ul id="myform">
		<li  class="form-group">
			<input placeholder="邮箱帐号" class="form-control" id="username" type="text">
		</li>
		<li  class="form-group">
			<input placeholder="密码" class="form-control" id="password" type="password">
		</li>		<li  class="form-group">
			<input placeholder="重复密码" class="form-control" id="repassword" type="password">
		</li>
		<li  class="form-group yzmbox">
			<input placeholder="验证码" class="form-control" id="yzm" type="number">
			<b  class="form-control" onclick="F.update()" id="yzm0">点击取得验证码</b>
		</li>
		<li class="text-center">
			<button id="btn" class="btn btn-primary" type="button">注册</button> | <a href="/user-login.html">登陆</a>
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
}


// 更新验证码
F.update = function(){
	axios({
		url:"/user-yzm"
	}).then(function(r){
		r=r.data;

		yzm0.innerHTML=r.data;

	}).catch(function(e){
		alert("取验证码失败");
	});
}


//点击提交
F.one("#btn").onclick=function(){
	let ipts = F.all("input");

	// 取验证码输入值
	let yzm = F.one("#yzm").value;
	if(yzm<1000 || yzm>9999){
		alert('验证码不合格错误');
		return;
	}

	//验证用户名
	let use = verify.isEmail(F.one("#username").value);
	if(use===false){
		alert(verify.getErr());
		return;
	}

	//验证密码
	let psw = verify.isPwd(F.one("#password").value, 3, 15);

	if(psw===false){
		alert(verify.getErr());
		return;
	}
	var password=F.one("#password").value;
	var username=F.one("#username").value;

	// 验证重复密码值
	if(F.one("#repassword").value!=password){
		alert('二次输入密码不一致！');
		return;
	}


	// return;

	//发送数据
	axios({
		url:"user-reging",
		method:"post",
		data:{username, password, yzm},		
	}).then(function(r){
		r=r.data;
		console.log(r);
		if(r.stat==1){
			alert('恭喜您注册成功！点击确定进行登陆！');
			location='/user-login';
		}
	}).catch(function(e){
		alert(e.response.data.msg);
		
	});

	


	
	
}




</script>
</body>
</html>
