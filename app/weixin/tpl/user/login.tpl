<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<title>万科会员管理系统-用户登陆</title>
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
<link type="text/css" href="/static/css/bootstrap.min.css" rel="stylesheet" />
<style type="text/css">

body{
	background:url("/weixin/img/loginbg.jpg");

}

.tit{color:#FFF;}

#box{
	margin:5em auto;
	width:380px;
}

ul{
	display: block;
	
	padding:2em 1em 1em 1em;
	list-style-type:none;
	background-color: #EEE;
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
	<h4 class="tit">用户登陆</h4>
	<ul id="myform">
		<li  class="form-group">
			<label for="username">邮箱</label>
			<input placeholder="注册的邮箱帐号" class="form-control" id="username" type="text">
		</li>
		<li  class="form-group">
			<label for="password">密码</label>
			<input placeholder="3-6位字母或数字" class="form-control" id="password" type="password">
		</li>
		<li  class="form-group yzmbox">
			<label for="yzm">验证码</label><br/>
			<input class="form-control" id="yzm" type="number">
			<b  class="form-control" onclick="F.update()" id="yzm0">点击取得验证码</b>
		</li>
		<li class="text-center">
			<button id="btn" class="btn btn-primary" type="button">登陆</button> | <a href="#">忘记密码</a> | <a href="/user-reg.html">注册</a>
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

	let yzm = ipts[2].value;

	if(yzm<1000 || yzm>9999){
		alert('验证码不合格错误');
		return;
	}

	//验证用户名
	let username = verify.isEmail(ipts[0].value);
	if(username===false){
		alert(verify.getErr());
		return;
	}

	//验证密码
	let password = verify.isPwd(ipts[1].value, 3, 6);
	if(password===false){
		alert(verify.getErr());
		return;
	}

	//console.log(username, password);

	//发送数据
	axios({
		url:"/user-loging.html",
		method:"post",
		data:{username, password, yzm},		
	}).then(function(r){
		r=r.data;

		if(r.stat==1){
			location = '/user-admin.html'
		}
	
	}).catch(function(e){
		alert(e.response.data.msg);
		
	});

	


	
	
}




</script>
</body>
</html>
