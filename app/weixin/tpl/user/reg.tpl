<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<title>万科会员管理系统-用户注册</title>
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
<link type="text/css" href="/static/css/bootstrap.min.css" rel="stylesheet" />
<style type="text/css">

body{
	/* background:url("/weixin/img/loginbg.jpg"); */
    background-color: #EEE;
}

.tit{color:#000;}

#box{
	padding:2em;
	margin:0 auto;
	width:450px;
}

ul{
	display: block;
	
	padding:2em 1em 1em 1em;
	list-style-type:none;
	background-color: #FFF;
	border:1px solid #ccc;
	
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

</style>
</head>
<body>

<section id="box">
	<h4 class="tit">用户注册</h4>
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
	let username = verify.isEmail(F.one("#username").value);
	if(username===false){
		alert(verify.getErr());
		return;
	}

	//验证密码
	let password = verify.isPwd(F.one("#password").value, 3, 15);
	if(password===false){
		alert(verify.getErr());
		return;
	}

	// 验证重复密码值
	if(F.one("#repassword").value!=password){
		alert('二次输入密码不一致！');
		return;
	}


	console.log({username, password, yzm});
	// return;

	//发送数据
	axios({
		url:"/user-reging",
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
		alert(e);
		
	});

	


	
	
}




</script>
</body>
</html>
