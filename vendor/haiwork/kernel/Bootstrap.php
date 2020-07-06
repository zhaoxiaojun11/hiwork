<?php namespace kernel;

/*
 * 框架启动器
 * chy 20200418105913
 */

class Bootstrap
{
	private static $ins;
	
	private function __construct(){}

	public static function ins()
	{
		if(!static::$ins) static::$ins= new static;
		return static::$ins;
	}

	// 执行
	public static function run(Bootstrap $ins)
	{
		// 配置写入
		$ins->setCfg();
		
		// 注册错误
		\kernel\error\VtpErr::exc();

		// 其它loader载入
		$ins->setLoader();

		// 路由分发
		$ins->setRoute();
	}


	// 合并配置,并生效
	public function setCfg()
	{
		// 取基础配置信息，并转为系统常量
		$cnf = \cnf_merge('conf.php');

		// 按映射要求转换为系统常量
		$map=['err_on', 'log_on', 'route_on', 'debug_on', 'getmap_on'];
		foreach ($cnf as $k => $v) {
			if(!in_array($k, $map)) continue;
			define(strtoupper($k), $v);
		}	
		 
		//配置时区
		date_default_timezone_set($cnf['default_timezone']);
	}

	// 注册项目loader
	public function setLoader()
	{
		$loader = new \Composer\Autoload\ClassLoader();
		$loader->addPsr4('proj\\', \APP_DIR);
		$loader->register();
		$loader->setUseIncludePath(true);
		// return $loader;
	}

	// 注册路由
	public function setRoute()
	{
		if(\ROUTE_ON){
			// 载入路由配置
			load(APP_DIR.'router.php');
			load(DOC_ROOT.'app/router.php');
			// 执行路由
			Route::mine()->run();
		}
		else{
			//不开启路由,通过ctl路由解析加载
			\kernel\Ctlrouter::exc();
		}

	}
	
}
