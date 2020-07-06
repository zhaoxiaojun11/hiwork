l<?php
use \proj\mdl\Qyweb as qy;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?=qy::APP_NAME_ZH;?> - 新闻管理 - 回收站</title>
<base href="/<?=APP_NAME;?>/">
<link type="text/css" href="/static/css/bootstrap.min.css" rel="stylesheet" />
<style type="text/css">
body{padding:2em;}
#btnbox{margin-bottom:.5em; float:right; text-align: right;}

.titWdt{width:30%; overflow:hidden;}
</style>
</head>
<body>
	<div>
		<div id="btnbox">
			<a href="?repush" class="btn btn-primary">刷新</a>
		</div>
		<h4>新闻管理 / 新闻回收站</h4>
	</div>
	<hr/>
	
	
	
	<div id="tblBox">
	  <table class="table">
		<thead class="thead-light">
		  <tr>
			<th scope="col">id</th>
			<th scope="col">标题</th>
			<th scope="col">组别</th>
			<th scope="col">创建时间</th>
			<th scope="col">操作</th>
		  </tr>
		</thead>
		<tbody id="tblbody">
		  <tr>
			<th scope="row">1</th>
			<td>标题</td>
			<td>组别</td>
			<td>创建时间</td>
			<td>删除 更新</td>
		  </tr>
		</tbody>
	  </table>
	</div>

	<div id="pgbox">

	</div>

	<script src='/static/js/vue.min.js'></script>
	<script src='/static/js/vuePager-master/vuePager.min.js'></script>
	<script type="text/javascript" src="/static/js/axios.min.js"></script>	
<script>
var F={
	one(slt){return document.querySelector(slt);},
	all(slt){return document.querySelectorAll(slt);},
	hasPager:false,
}

setList(1);

	
// 生成表格和分页
function setList(pn){
	// 生成表内容
	axios({
		url:"/admin,news-alling",
		method:"get",
		params:{pn, recycle:1}
	}).then(function(r){
		r=r.data;
		if(r.stat==1){
			// console.log(r.data);

			// 生成表格列表
			setRows(r.data.rows);

			// 生成页码（注意页码只生成一次）
			if(F.hasPager==false){
				setPager(r.data.page.pn, r.data.page.tp);
				F.hasPager=true;
			}

		}
	}).catch(function(e){
		alert(e);
	})
}


// 生成表格的行
function setRows(rows){
	let html="";
	for(let row of rows){
		html +=`<tr data-nid="${row.id}">
			<th scope="row">${row.id}</th>
			<td class="titWdt">${row.tit}</td>
			<td>${row.grp}</td>
			<td>${row.ctime}</td>
			<td>
				<a href="javascript:void(0)" onclick="huifu(this)">恢复</a>
				<a href="javascript:void(0)" onclick="del(this)">永久删除</a>
			</td>
		  </tr>`;
	}

	F.one("#tblbody").innerHTML=html;

}


// 生成页码
function setPager(pn, tp){
	// console.log("生成页码");
	//生成页码
	new vuePager("#pgbox",{
		pn:pn,//页码,[必],int,默认：第1页
		tp:tp,//总页数,[必],int,默认：共100页
		offset:5,//显示页码个数,[选],int,默认5,注：此处最好是奇数
		pinf:true,//是否显示分页信息,[选],bool,默认true显示
		firstText:"首页",//首页显示文字,[选],string,默认：« ,注：假值为不显示，不设置则使用默认值
		preText:"上页",//上页显示文字,[选],string,默认：› ,注：假值为不显示，不设置为则用默认值
		posText:"下页",//下页显示文字,[选],string,默认：‹ ,注：假值为不显示，不设置为则用默认值
		lastText:"末页",//尾页显示文字,[选],string,默认：« ,注：假值为不显示，不设置为则用默认值
	}).build(function(_pn, _tp, _offset){
			console.log(_pn, _tp, _offset);
			setList(_pn);//根据当前页码生成页面
	});
}
// 恢复功能
function huifu(pn){
	let nid=pn.parentNode.parentNode.getAttribute('data-nid');//获取文章ID
	axios({
		url:"/admin,news-recycle_hf",
		method:"get",
		params:{nid}
	}).then(function(r){
		r=r.data;
		alert(r.msg);
	}).catch(function(e){
		alert(e.response.data.msg);
	})
}
// 恢复功能
function del(pn){
	let nid=pn.parentNode.parentNode.getAttribute('data-nid');//获取文章ID
	axios({
		url:"/admin,news-recycle_dl",
		method:"get",
		params:{nid}
	}).then(function(r){
		r=r.data;
		alert(r.msg);
	}).catch(function(e){
		alert(e.response.data.msg);
	})
}




</script>
</body>
</html>
