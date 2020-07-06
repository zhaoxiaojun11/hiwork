<?php namespace kernel;

// 404错误页
// Route::mine()->set404('\kernel\Rtn@warning');
Route::mine()->set404('\kernel\Rtn@e404');


// 主页
Route::mine()->get('/', function() {
	echo '你好, 欢迎来到 HaiWork!';
});



// 调用视图生成页面
Route::mine()->get('/idx(\.html)?', function() {
	$tpl = new \kernel\View;
	$tpl->assign(['author'=>'chy', 'creatAt'=>'20200412']);
	$tpl->display('index.tpl');
});



// product/分类/id
Route::mine()->get('/product/{group}/(\d{2,5})', function($group, $x) {
	var_dump($group, $x);
});


// about/所有的页面
Route::mine()->get('/about/(.*)', function($x) {
	var_dump($x, 'about page!');
});


// 用户/用户信息
Route::mine()->get('/user/(\w+)', function($username) {
	var_dump('输入的用户名是:'.$username);
	var_dump('输入的用户名是:'.htmlentities($username));//推荐
});


// a/$ax/b/$bx
Route::mine()->get('/a/{ax}/b/{bx}', function($ax, $bx) {
    echo 'Movie #' . $ax . ', photo #' . $bx;
});


// img-hot/123
Route::mine()->get('/img-hot/(\d{3})', function($id) {
    var_dump($id);
});

