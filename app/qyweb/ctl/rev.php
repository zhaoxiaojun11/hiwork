<?php

// 取回输入的json数据
$post = json_decode(file_get_contents('php://input'), true);
if(empty($post))  exit('未传入文件');

// 1.取回post过来的base64值
$img = $post['img'];//图片base64值

// 2.取回 base64 文件源值
$arr = explode(',', $img);
$img=base64_decode($arr[1]);

// 3.取回 文件类型$type
preg_match('/\/(.*);/', $arr[0], $r);
$type=$r[1];
// var_dump($type);exit;

// 4.生成图片的保存路径
$imgSrc='./uploads/'.time().'.'.$type;

// 5.生成文件
$length = file_put_contents($imgSrc, $img);

// 6.判断图片是否生成成功，并返回结果
if($length===false)
{
	exit('图片保存失败！');
}
else
{
	exit("图片保存成功, 图片地址是:{$imgSrc}");
}



