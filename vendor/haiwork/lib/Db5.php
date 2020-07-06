<?php namespace lib;

/*
 * 数据库的操作类db
 * 说明：完成数据库相关的基础操作
 * ver1.0 初版
 * ver1.1 增加 P()函数 20180713
 * ver1.2 增加 meer\D\D2\I\U\UI函数 20180720
 * ver4.0 增加 继承于baselib基类 20190523
 * ver4.1 
 * 		增加 I1（私有） 实现单数据写入
 * 		增加 I2（私有） 实现多条数据写入
 *   	更改 get_err 为 getErr
 *   	20190815114015 删除lastId属性，getLastid直接取值
 *   	注：I方法为插入数据的公开入口
 *
 * ver4.2
 * 		getStmt的$arr是：一维的关联或索引数组，优化数据绑定：索引数组由之前bindValue绑定，改为execute绑定
 * 		err的默认值由ok更改为空字串 20200504190840
 * 		删除暂存但已无用的方法 20200510155138
 * 		 
 * 注： 在使用dbX时,必需要用 use 引入，如
 	use \Lib\Db4 as DB;
 * 
 */
class Db5
{
	use \kernel\traits\Baselib;

	private $err='';
	private $pdo;
	
	//pdo基础设置
	public function __construct()
	{
		try{
			// 取回数据库配置
			$db = \cnf_merge('db.php');
			//1. 连接数据库
			$this->pdo=new \PDO("{$db['type']}:host={$db['host']};port={$db['port']};dbname={$db['dbname']};", $db['usr'], $db['pwd']);
			$this->pdo->exec('set names utf8');
			
			//开启错误，抛出错误
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		catch( \PDOException $err ){
			$this->err=$err->getMessage();
		}		
	}

	// 取得最后insert的数据ID
	// 注：必需在insert语句立该使用，否则会被其它语句覆盖
	// 20190712160347 LM:20190815114051
	public function getLastid()
	{
		return $this->pdo->lastInsertId();
	}

	/*
	 * 执行操作SQL语句，返回影响条数
	 * @sql [必] string 操作型SQL语句(必需是：update/delete/insert)
	 * @row [选] array 要绑定的数据 
	 * @return int 受操作语句影响的行数: 整数 或 false(sql错误)
	 *
	 * lm: 加入错误判断 20180614
	 */
	public function exec($sql, array $row=[])
	{
		//执行sql语句
		$stmt=$this->getStmt($sql, $row);
		//返回影响条数
		return $stmt ? $stmt->rowCount() : false;
	}

	/*
	 * D方法一
	 * $tblName [必] string 表名
	 * $whr [必] string 删除条件,如 "id between 5 and 10" 或 "tit like '%手机%'" 或 "id=?" 或 "id=:id and stat=:stat"
	 * $row [选] 要绑定的一维数组，没有要绑定的数据则留空
	 * 
		DB::MINE()->D('t_qy_dgtbl', 'id>1000 and stat=0');
		DB::MINE()->D('t_qy_dgtbl', 'id=:id and grp=:grp', ['id'=>9, 'grp'=>14]);
		DB::MINE()->D('t_qy_dgtbl', 'id in (?,?,?)', [101,109,138]);
	 * 
	 * 20180720
	 */
	public function D($tblName, string $whr, array $row=[])
	{		
		$sql="delete from {$tblName} where {$whr};";

		//执行sql语句
		$stmt=$this->getStmt($sql, $row);
		//返回影响条数
		return $stmt ? $stmt->rowCount() : false;
	}


	/*
	 * I方法：插入数据方法
	 * $tblName [必] string 表名
	 * $array [必] array 待插入的数据（一/二维关联数组）
	 * return false/int flase有错误，int受操作影响行数 
	 * 
	 	示例：
		$instance->I('t_qy_dgtbl', [
			'tit'=>'I insert tit',
			'cnt'=>'I insert cnt',
			'grp'=>4
		]);

	 */
	public function I($tblName, array $arr)
	{
		//1. 根据数组维度，判断是一/二维写入
		$ifun=is_array(reset($arr)) ? 'I2' : 'I1';
		$stmt = $this->$ifun($tblName, $arr);

		//4. 返回结果
		return $stmt ? $stmt->rowCount() : false;
	}


	/*
	 * U方法
	 * $param tblName string 表名
	 * $param row array 待更新的一维数组数据
	 * $pk tblName string 主键名称
	 *
	 * 注： U方法只适用于一条数据的更新
	 * 示例：
	 * 	$row = ['id'=>2, 'city'=>'开封', 'age'=>22];
	 *  $c = \lib\db4::mine()->U('t_user_0', $row, 'id');
	 *  var_dump($c, \lib\db4::mine()->getErr());
	 *  
	 */
	public function U($tblName, array $row, $pk='id')
	{
		if(!isset($row[$pk])){
			$this->err='更新数据中未定义主键，请检查数据！';
			return false;
		}

		// 取出索引,并删除数据中的索引单元
		$pk="$pk='{$row[$pk]}'";
		unset($row[$pk]);

		//生成set字段和值
		$d=['sets'=>[], 'vals'=>[]];
		foreach ($row as $k => $v) {
			$d['sets'][]="`$k`=?";
			$d['vals'][]=$v;
		}

		// 生成sql语句
		$sql='update '.$tblName.' set '.implode(',', $d['sets']).' where '.$pk;

		//执行sql语句
		$stmt=$this->getStmt($sql, $d['vals']);
		//返回影响条数
		return $stmt ? $stmt->rowCount() : false;
	}

	/*
	 * IU方法
	 * @param tblName string 表名
	 * @param dArr array 待更新/插入的一维数组数据
	 * @pk tblName string 主键名称
	 * 说明: 如果 pkName在数据中则更新，不在则插入
	 * 
	 	示例：
		$arr=['id'=>'2', 'tit'=>'第2行标题', 'grp'=>1];
		$c=db::U('t_qy_dgtbl', $arr, 'id');
		$c>0 ? rtn::okk() : rtn::err();
		exit;
	 */

	public function IU($tblName, array $row, $pk='id')
	{
		return empty($row[$pk])
			? $this->I($tblName, $row)
			: $this->U($tblName, $row, $pk);
	}



	/*
	 * 取得sql的查询结果
	 * $sql string [必] sql语句
	 * $row array [选] 作为sql语句的数据
	 * retrurn array|false 如果是false，则sql语句错误，如是空数组则代表未查到任何数据
	 * 
	 * 20180601 chy
	 * lm: 加入 错误判断 20180614
	 * lm: 加入 单值的输出 20180715
	 * 注：二维时未查到返回空数组，其它返回false，所以对结果的判断用empty
	 	20181213 一、二维时未查到返回空数组，单值没找到返回空字串，语句错误返回false
	 */
	public function R($sql, array $row=[], $fetchType=2, $rType='array')
	{
		//执行sql语句
		$stmt=$this->getStmt($sql, $row);
		if(!$stmt) return false;
		// var_dump($stmt, $stmt->fetchAll(\PDO::FETCH_OBJ));
		
		switch($fetchType)
		{
			//返回 二维数组
			case 2:
				return $stmt->fetchAll($this->get_rtnType($rType));
			//返回 一维数组
			case 1:
				return $stmt->fetch($this->get_rtnType($rType))?:[];
			//返回 单值
			default:
				return $stmt->fetchColumn()?:'';
		}

	}

	
	/*
	 * 取得分页数据
	 * $sql [必] 查询语句 
	 * $row [选] 要绑定的数据
	 * $pgInf [选] 页码相关的数据，以引用方式存储
	 * 		$pgInf示例：[
	 * 			sh:int//显示条数(传入)
	 * 		 	pn:int//页码数(传入)
	 * 		   	tt:int//总条数(返回)
	 * 		    tp:int//总页数(返回)
	 * 		    key:'pn'//页码key默认为'pn'(传入)
	 * 		]
	 * 
	 * 访问url示例：
	 * http://hw.com/cs-pg?pn=2&sh=10
	 * 
		使用示例一：
		$page=[];//存储页码数据
		$rows=DB::MINE()->P($sql, [], $page);//取出分页数据
		var_dump($page, $rows);//测试输出 分页数据 和 页码
		使用示例二：
		//控制每页显示10条
		$arr=['rows'=>[],'page'=>['sh'=>10]];
		$arr['rows'] = DB::MINE()->P($sql, [], $arr['page']);
		var_dump($arr);

	 * 	优化分页参数的验证，加入最后一位;的处理20200428183219
	 */
	public function P($sql, $row=[], &$pgInf=[], $rType='object')
	{
		// 清除最后一位的分号
		if(substr($sql, -1)==';') $sql=substr($sql, 0,-1);

		// 显示条数
		$pgInf['sh']=\filter_input(\INPUT_GET, 'sh', \FILTER_VALIDATE_INT, [
			'options'=>[
				'min_range'=>1,//最小1条
				'max_range'=>1000,//最小1000条
				'default'=>$pgInf['sh'] ?? 5,//默认5
			]
		]);

		// 当前页码数
		$pnKey = $pgInf['key'] ?? 'pn';
		$pgInf['pn'] = \filter_input(\INPUT_GET, $pnKey, \FILTER_VALIDATE_INT, [
			'options'=>[
				'min_range'=>1,//最小第1页
				// 'max_range'=>1000,//最大1000页
				'default'=>$pgInf['pn'] ?? 1,//默认1
			]
		]);
		
		//总条数
		$sql_t=preg_replace('/^select .* from/i', 'select count(*) as t from', $sql);
		$pgInf['tt']=(int)$this->R($sql_t, $row, 0);//总条数
		//总页数 = 向上取整(总条数/显示条数) 
		$pgInf['tp'] = (int)ceil($pgInf['tt']/$pgInf['sh']);//总页数
		
		//启始条数 = (页数-1)*显示条数
		$limit=($pgInf['pn']-1)*$pgInf['sh'];
		$limit=" limit {$limit}, {$pgInf['sh']}";

		// var_dump($sql.$limit);exit;
		return $this->R($sql.$limit, $row, 2, $rType);
	}




	/*
	 * [I2 二维数组的写入]
	 * 注：I的支持性方法，不公开
	 * @Author   chy
	 * @DateTime 2019-12-02
	 * @param $arr 二维关联格式数组
	 * @return stmt
	 */
	private function I2($tbl, array $arr)
	{
		//1.取出基础数据
		// k:索引, v:值, seats:?占位符
		$d = ['v'=>[], 'seats'=>[]];
		// 1.1 生成索引
		$d['k'] = array_keys(reset($arr));
		// 1.2 取出二维值生成占位符与对应值（一维值数组）
		foreach($arr as $row) {
			$_seats=[];
			foreach ($row as $v) {
				$_seats[]='?'; 
				$d['v'][]=$v;
			}

			$d['seats'][]='('.implode(',', $_seats).')';
		}


		//2. 生成预处理语句
		$sql = 'insert into '.$tbl.' ('.implode(',', $d['k']).") values ".implode(',', $d['seats']).';';

		//3. 执行并返回结果集
		return $this->getStmt($sql, $d['v']);
	}


	/*
	 * [I1 一维数组的写入]
	 * 注：I的支持性方法，不公开
	 * @Author   chy
	 * @DateTime 2019-12-02
	 * @param $arr 一维关联格式数组
	 * @return $stmt
	 */
	private function I1($tbl, array $row)
	{
		//1.取出基础数据
		// k:索引, v:值, seats:?占位符
		$d = ['k'=>[], 'v'=>[], 'seats'=>[]];
		foreach($row as $k =>$v)
		{
			$d['k'][]=$k;
			$d['v'][]=$v;
			$d['seats'][]='?';
		}
	
		//2. 生成预处理语句
		$sql = "insert into {$tbl} (".implode(',', $d['k']).") values (".implode(',', $d['seats']).");";

		//3. 执行并返回结果集
		return $this->getStmt($sql, $d['v']);
	}



	//返回错误信息 20180614
	public function getErr()
	{
		return $this->err;
	}


	/*
	 * 执行sql语句，返回pdo::stmt对象
	 * $row [必] array 要绑定的数据，一维数组: 索引 与 关联均可
	 * return PDOStatment结果集对象
	 * lm:20191202180718
	 */
	private function getStmt($sql, array $row)
	{
		if(!$this->pdo) return false;
		
		try{
			//预执行sql语句
			$stmt=$this->pdo->prepare($sql);

			//绑定数据[]
			if(!!$row){
				//数值型数组
				if(isset($row[0]) && $row[0]===reset($row)){
					$stmt->execute($row);
				}
				//关联型数组
				else{
					foreach( $row as $key => $val){
						$stmt->bindValue(':'.$key, $val);
					}
					$stmt->execute();
				}
			}
			else{
				$stmt->execute();
			}

			return $stmt;		
		}
		catch( \PDOException $err ){
			$this->err=$err->getMessage();
			return false;
		}
		
	}

	/*
	 * 设置结果类型
	 * 20170715
	 */
	private function get_rtnType($rType='object')
	{
		switch($rType)
		{
			case 'object': return \PDO::FETCH_OBJ;
			case 'array': return \PDO::FETCH_ASSOC;
			case 'both': return \PDO::FETCH_BOTH;
			default: return \PDO::FETCH_OBJ;
		}
	}
}


