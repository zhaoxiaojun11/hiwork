<?php namespace lib;

/* 
 * 数据库操作（自已封装）
 * chy 20200421095438
 */

class Sjk
{
    // pdo实例
    private $pdo;
    // 数据库配置
    private $db=[
		'dbtype'=>'mysql',
		'host'=>'127.0.0.1',
		'username'=>'root',
		'password'=>'123456',
		'port'=>'3306',
		'database'=>'hiwork'
    ];
    
    private $errMsg = ''; 

    public function getErr(){
        return $this->errMsg;
    }

    // 在取得操作库时，就进行数据库的连接
    public function __construct()
    {
        try{
            $this->pdo = $this->getPdo();
        }
        catch(\PDOException $e){
            $this->errMsg=$e->getMessage();
        }
    }

	// 取得pdo的实例
	private function getPdo()
	{
		// 连接数据库
		$pdo = new \PDO("{$this->db['dbtype']}:host={$this->db['host']}; port={$this->db['port']};dbname={$this->db['database']}", $this->db['username'], $this->db['password']);

        //设置所有的pdo操作使用utf8编码
        $pdo->query('set names utf8');

        // $pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		return $pdo;
    }

    // 通过"占位"的SQL,和数据的绑定，返回结果集
    private function getStmt($sql, array $data=[])
    {
        if(!$this->pdo) return false;

        try{
             // 对有占位的语句，进行预处理
            $stmt = $this->pdo->prepare($sql);

            if( isset($data[0]) ){// 数据绑定： 位置号         
                foreach($data as $k=>$v){
                    $stmt->bindValue($k+1, $v);
                }
            }
            else{// 数据绑定: 索引        
                foreach($data as $k=>$v){
                    $stmt->bindValue(':'.$k, $v);
                }
            }
            
            // 执行
            $stmt->execute();

            return $stmt;
        }
        catch(\PDOException $e){
            $this->errMsg=$e->getMessage();
            return false;
        }
       
    }
    
    
    /**
     * 功能：返回查询语句结果
     * $sql [必] string 查询型sql语句 
     * $data [选] array 待绑定的数据
     * $dataType [选] int 约束返回数据的类型：｛0：单值; 1:一维数组; 2:二维数组｝
     * return 单值、一维数组、二维数组、false
     * 注意：一维与二维在数据不匹配时均返回[]
     * 注意：false时，为数据库的配置 或 sql错误
     * chy 20200421100727
     */
	public function get_result($sql, array $data=[], int $dataType=2)
	{
		// 执行查询(返回了包含结果的对象)
        $stmt = $this->getStmt($sql,  $data);//链式调用
        if(!$stmt) return false;

        // var_dump($dataType, $stmt->fetch(\PDO::FETCH_ASSOC));exit;

        switch($dataType){
            case 2:
                //PDOStatement ::fetchAll()：有匹配则为二维数组，无匹配则为[]
                // 整合后：返回二维数组 或 []
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            case 1: 
                //PDOStatement ::fetch()：有匹配则为一维数组，无匹配则为false
                //整合后：返回一维数组 或 []
                $v = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $v===false ? [] : $v;
            case 0:
                //PDOStatement ::fetchColumn() :有匹配则为单值，无匹配则为false
                //整合后：返回单值 或 ''
                $v = $stmt->fetchColumn();
                return $v===false ? '' : $v;
            default:
                //非以上情况，均返回false, 以错误处理
                $this->errMsg='未知数据返回类型';
                return false;
       }

	}


    /**
     * 功能：返回受操作语句影响的行数
     * @sql [必] string 操作型sql语句 
     * return int
     * chy 20200421100727
     */
	public function get_count($sql, array $data=[])
	{
        $stmt = $this->getStmt($sql,  $data);//链式调用
        if(!$stmt) return false;

		// 执行操作语句(返回了包含结果的对象)
		return $stmt->rowCount();
	}

}
