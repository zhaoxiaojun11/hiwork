<?php namespace proj\ctl;

use \kernel\Rtn;
use \kernel\Request;
use \lib\DB5 as DB;
use Intervention\Image\ImageManagerStatic as Image;

/*
 * 图片处理模块-功能展示
 * chy 20190927110246
 */

class Img extends \kernel\Controler
{
	public function index()
	{
		// Rtn::alert(new \Exception('此页面需要开启：ImageMagick+intervention_image，如有请注释本行！'));

		// return '欢迎来到图片测试模块！';
		//传入数据
		$this->assign(['title'=>'欢迎来到图片测试模块！', 'src'=>'/'.CTL.'-myImg']);
		$this->display('imgIdx.tpl');
	}


	/*
	 * 二维码应用案例 simple-qrcode
	 * getUrlQrcode生成当前页面的URL的二维码
	 * 注：用于提供页面的 二维码图片
	 * 返回图片的base64值
	 * 
	 * 20190926154713
	 */
	public function getUrlQrcode()
	{
		Rtn::alert('此页面需要开启：ImageMagick+intervention_image，如有请注释本行！');

		$url = $_GET['url'] ?? '://localhost/';
		vendor('simplesoftwareio_simple-qrcode');

		// 取得bacon-qr工具
		$qrcode = new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
		$r =  $qrcode
			// 输出文件类型： 默认输出SVG格式的图片
			->format('png')
			//容错级别：{L:7%的字节码恢复率，M:15%，Q:25%，H:30%}
			->errorCorrection('H')
			// 大小
			->size(200)
			//颜色:必须是RBG格式
			->color(0,rand(100,255),rand(100,255))
			//背景色:必须是RBG格式
			->backgroundColor(rand(230, 255),rand(230, 255),rand(230, 255))
			// 边距
			->margin(1)
			// 编码：默认使用 ISO-8859-1，也可改为 UTF-8、ASCII、GBK等
			->encoding('UTF-8')
			// 生成generate(要写入的文字, 生成图片的地址, 是否是绝对地址=默认否)
			->generate($url);

		return $r;
	}


	/**
	 * 图像库使用案例
	 * 说明：画“小人儿”表情
	 * chy 20190811115307
	 */
	public function demo1()
	{
		//https://imagine.readthedocs.io/en/stable/usage/effects.html
		vendor('intervention_image');
		
		// 创建画布
		Image::configure(array('driver' => 'gd'));

		// 画布
		$image = Image::canvas(120, 120, '#0F0');

		// 画圆
		$image->circle(100, 60, 60, function ($draw) {
		    $draw->background('FFF');
		    $draw->border(1, '#000');
		});

		// 模糊
		// $image->blur(3);

		// 旋转
		// $image->rotate(45);

		// 输出图片
		echo $image->response('jpg', 70);
	}


	/**
	 * 图像库使用案例
	 * 说明：画“伞”
	 * 
	 * 注：php需安装gd/imagick库(gd是php默认的，imagick需另外安装)
	 * 注：gd不支持线宽设定 20200416
	 * chy 20190811115307
	 */
	public function demo2()
	{
		// Rtn::alert('此页面需要开启：ImageMagick/GD+intervention_image，如有请注释本行！');

		//引入图像库
		vendor('intervention_image');
		
		// 创建画布
		Image::configure(array('driver' => 'gd'));

		// 画布
		$image = Image::canvas(120, 80, [255,255,255,1]);


		// 画三角－左
		$image->line(60, 0, 0, 40, function ($draw) {
		    $draw->color([200,2000,0,1]);
		    // $draw->width(5);
		});

		// 画三角－右
		$image->line(60, 0, 120, 40, function ($draw) {
		    $draw->color([200,2000,0,1]);
		    // $draw->width(5);
		});

		// 画三角－下
		$image->line(0, 40, 120, 40, function ($draw) {
		    $draw->color([200,2000,0,1]);
		    // $draw->width(5);
		});

		// 画竖线
		$image->line(60, 40, 60, 80, function ($draw) {
		    $draw->color([200,200,60,1]);
		    // $draw->width(5);
		});


		// 模糊
		// $image->blur(3);

		// 旋转
		// $image->rotate(45);

		// 输出图片
		//header('content-type: text/html;');
		echo $image->response('jpg', 70);
	}


	/*
	 * 图像库使用案例
	 * 说明：为图片加入“文字水印”
	 * chy 20190927115002
	 */
	public function demo3()
	{

	}


	/*
	 * 图像库使用案例
	 * 说明：图片+图片合成 案例
	 * 
	 * chy 20190927115002
	 */
	public function demo4()
	{

	}


}





