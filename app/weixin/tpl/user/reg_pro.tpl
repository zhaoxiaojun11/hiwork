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
			<input  placeholder="邮箱帐号" class="form-control" id="username" type="text">
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
	//1. 取得用户输入邮箱帐号（并验证）
	let username = verify.isEmail(F.one("#username").value);
	if(username==false){
		alert("用户名格式错误！");
		return;
	}

	//2. 通过 后台 将 验证码 发送到 邮箱帐号
	axios({
		url:"/user-yzmPro?mail="+username,
		method:"get",
	}).then(function(r){
		// r=r.data;
		alert('验证码已发送到您的帐户：'+username+'！');
		// console.log(r);

	}).catch(function(e){
		alert(e);
		console.log(e.response.data);
	});
}


//点击提交
F.one("#btn").onclick=function(){
	// 判断信息填写是否完全及正确
	let yzm=F.one("#yzm").value;
	if(yzm<100000 || yzm>999999){
		alert("验证码填写错误！");
		return;
	}

	let mail = verify.isEmail(F.one("#username").value);
	if(mail==false){
		alert("用户名格式错误！");
		return;
	}

	// alert("验证信息，并提交信息");
	axios({
		url:"/user-regingPro",
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
