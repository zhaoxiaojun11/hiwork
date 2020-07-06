<?php
use \proj\mdl\Qyweb as qy;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<title><?=qy::APP_NAME_ZH;?>-用户登陆</title>
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
	cursor: pointer;
}

#yzm{
	width:200px;
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
	<h4 class="text-center"><?=qy::APP_NAME_ZH2;?>-用户登陆(用户名+密码)</h4>
	<div id="oth">使用 <a href="/yonghu.html">邮箱+验证码</a> 登陆</div>
	<ul id="myform">
		<li  class="form-group">
			<input placeholder="注册的邮箱帐号" class="form-control" id="username" type="text">
		</li>
		<li  class="form-group">
			<input placeholder="3-6位字母或数字" class="form-control" id="password" type="password">
		</li>
		<li  class="form-group yzmbox">
			<input placeholder="输入取得的验证码" class="form-control" id="yzm" type="number">
			<b class="form-control" id="yzm0">点击取得验证码</b>
		</li>
		<li class="text-center">
			<button id="btn" class="btn btn-primary" type="button">登陆</button> | <a href="/user-reg.html">注册</a>
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
F.one("#yzm0").onclick = function(){
	// 点击后屏蔽
	if(this.dataset.ing == 1) return;
	this.dataset.ing = 1;
	var mail = F.one("#username").value; 
	axios({
		url:"user-yzm",
		// method:'post',
		// data:{mail},
	}).then((r)=>{
		this.dataset.ing = 0;
		r=r.data;
		yzm0.innerHTML=r.data;
	}).catch(function(e){
		alert("取验证码失败"+e);
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
	let us = verify.isEmail(ipts[0].value);
	if(us===false){
		alert(verify.getErr());
		return;
	}

	//验证密码
	let pws = verify.isPwd(ipts[1].value, 3, 6);
	if(pws===false){
		alert(verify.getErr());
		return;
	}
	var password = ipts[1].value;
	//console.log(username, password);
	var username = ipts[0].value;
	//发送数据
	axios({
		url:"/user-loging.html",
		method:"post",
		data:{username, password, yzm},		
	}).then(function(r){
		r=r.data;

		if(r.stat==1){
			alert('登陆成功');
			location="/admin,index";
		}
	
	}).catch(function(e){
		alert(e.response.data.msg);
		
	});

	


	
	
}




</script>
</body>
</html>
