<?php namespace lib;

use \lib\Db5 as db;
use \kernel\Rtn as rtn;

/*
 * 数据表操作链式调用
 * 示例：
    $dtbl = new \lib\Dtbl('t_usr');
    $dtbl->where('id>1010 and id<1200')
        ->where(['stat'=>2])
        ->order('id asc')
        ->limit('30')
        ->select('id,tit as title,y,m,d,creat_at,stat');
    var_dump($r, childModel::mine()::getErr());
 *
 * 20190816172903
 *
 * 加入引用Baselib，以实现单态 20200119144713
 */

class Dtbl
{
    // use \kernel\traits\Baselib;

    protected $where = 'WHERE 1';//where条件
    protected $order = '';//order排序
    protected $limit = 'LIMIT 500';//limit限定查询,默认500条
    protected $group = '';//group分组
    protected $rType = 'array';//查询返回类型，同db::R
    protected $dType = 2;//查询数据类型，同db::R
    protected $dArr=[];//与占位符对应的数据
    protected $err='ok';

    public function __construct($table)
    {
        $this->table=$table;
    }
    
    /*
     * 实现查询操作
     * 在已有子查询的基础上返回结果
     *
     * $md = new \lib\Model();
        $r = $md->where('id>1010 and id<1200')
            ->where(['stat'=>2])//复加条件
            ->order('id asc')
            ->limit('5')
            ->select('id,tit as title,y,m,d,creat_at,stat');
        var_dump($r, $md::getErr());
     * 
     * 20190816165620
     */
    public function select($fields="*")
    {       
        $r = db::mine()->R("
            SELECT {$fields} FROM {$this->table} 
                {$this->where} 
                {$this->group} 
                {$this->order} 
                {$this->limit}"
            , $this->dArr, $this->dType, $this->rType);

        $this->init();
        return $r;

    }

    
    /*
     * 插入数据
     * $data [必] array 要插入的数据(必需是一维)
     * $lastid [选] int 引用返回插入数据的ID
     *
     * 示例
        $ls = new \mdl\Lishi();
        $id=0;
        $r = $ls->insert([
                'tit'=>'new mdl creat',
                'y'=>2019,
                'm'=>8,
                'd'=>16,
            ], $id);

        var_dump($r, $id, $ls->getErr());
     * 
     * 20190816171736
     */
    public function insert(array $dArr, &$lastid=0)
    {
    	$c = db::mine()->I($this->table, $dArr);
        $lastid = db::mine()->getLastid();

        $this->init();
    	return $c;
    }

 
    /*
     * 更新数据
     * $dArr [必] array 要更新的数据
     * 注意：必需指定where条件
     *
        $ls = new \mdl\Lishi();
        $r = $ls->where(['id'=>10975])
                ->update([
                    'tit'=>'new mdl creat5555',
                    'stat'=>0,
                ]);

        var_dump($r, $ls->getErr());
     *
     * 
     * 20190816172208
     */
    public function update(array $dArr)
    {
        if($this->where=='WHERE 1'){
            $this->err='必需先设置where后再执行update';
            return false;
        }

        $arr=['sets'=>[], 'data'=>[]];

    	foreach ($dArr as $k => $v) {
    		$arr['sets'][]="$k=?";
    		$arr['data'][]=$v;    		
    	}

    	$sets = implode(',', $arr['sets']);
        //注意：此处$data必需在前
    	$this->dArr=array_merge($arr['data'], $this->dArr);

        $c = db::mine()->exec("update {$this->table} set {$sets} {$this->where}", $this->dArr);

        $this->init();
    	return $c;
    }
 
    /*
     * 删除数据
     * 注意：必需指定where条件
     *
        $ls = new \mdl\Lishi();
        $r = $ls->where(['id'=>10975])->delete();
        var_dump($r, $ls->getErr());
     * 
     * 20190816172215
     */
    public function delete()
    {
        if($this->where=='WHERE 1'){
            $this->err='必需先设置where后再执行delete';
            return false;
        }
        $c = db::mine()->exec("DELETE FROM {$this->table} {$this->where}", $this->dArr);

        $this->init();
        return $c;
    }


    //设置数据类型：默认数组 20190816164923
    //注意：此项非必需,默认由R或P方法控制
    public function rType($rType)
    {  
        $this->rType=$rType;
        return $this;
    }

    //设置数据类型：默认数组 20190816164923
    //注意：此项非必需,默认由R或P方法控制
    public function dType($dType)
    {  
        $this->dType=$dType;
        return $this;
    }
 

    /*
     * order排序
     * @order string 排序字串
     * 
     * 示例：
     * self::order('id desc,ord asc');
     * 
     * 20190816164737
     */
    public function order(string $order='')
    {
        $this->order = $order ?  ' ORDER BY '.$order : '';
        return $this;
    }


    /*
     * 设置条件子句：字串条件
     * 注： 不直接使用，仅供where调用的方法
     * $where [选] string 以？占位的条件字串,如： '(grade=? and city=?) or stat>?'
     * $data [选] array 索引数组,如对应上面的数据 [1801, 'zhengzhou', 2]
     * 20190816170339
     */
    protected function whereStr(string $where='', $data=[])
    {
		$this->dArr = array_merge($data, $this->dArr);
		$this->where .= ' AND '.$where; 
    }


    /*
     * 设置条件子句：数组条件
     * 注意： 不直接使用，仅供where调用的方法
     * 注意：$mk推荐用and，其它可能导致错误
     * 
     * $where array 条件数组,如：[id=>15, stat=>1, grp=2]
     * $mk string 条件的连接符, 如 and，则上面各条件以and相连
     * 
     * 注意：数组型应针对简单索引与值相等的处理，不应包含复杂的条件格式
     * 
     * 20190816170336
     */
    protected function whereArr(array $where=[], $mk='and')
    {
    	//将条件数据合并到数据: 注意顺序不可错
        $this->dArr = array_merge($this->dArr, array_values($where));

        $whr=[];
        foreach ($where as $key => $value) {
            $whr[] = "{$key}=?";
        }

        $where = implode(" {$mk} ", $whr);

        $this->where .= ($this->where=='WHERE 1' ? ' AND ':" {$mk} ")."({$where})";
    }
 

    /*
     * sql执行时传入的 where条件
     * $where 条件 默认应以字符串为主（或键值对的条件组合）
     * $mkOrData 要绑定的值（或数组单元拼接的或且关系）
     * @return $this
     * 20190816153504
     * 
     	示例一：字串型
     	$md = new \lib\Model();
		$r = $md->where('id<? and zuohao<=?', [10,2])->select();
		var_dump($r, $md::getErr());
		
		示例二：数组型
		$md = new \lib\Model();
		$r = $md->where(['zuohao'=>4, 'banji'=>'web1703'], 'or')->select();
		//或 $r = $md->where(['zuohao'=>4], 'or')->where(['banji'=>'web1703'], 'or')->select();
        var_dump($r, $md::getErr());

        注意：数组型应针对简单索引与值相等的处理，不应包含复杂的条件格式
     */
    public function where($where='', $mkOrData=[])
    {
    	if($where){
    		if(is_array($where)){
    			$mkOrData = empty($mkOrData) ? 'and' : $mkOrData;
				$this->whereArr($where, $mkOrData);
    		}
    		else{   			
    			$this->whereStr($where, $mkOrData);
            }
            
        	return $this;
    	}        
    }

 
    /*
     * 设置group分组
     * 20190816170708
     */
    public function group($group='')
    {
        if($group){
            if(is_array($group)) $group = implode(',',$group);
            $this->group = ' GROUP BY '.$group;
        }
        
        return $this;
    }
 

    /*
     * 设置limit限定
     * 20190816170733
     */
    public function limit($limit='')
    {
        if($limit) $this->limit = " LIMIT ".$limit;
        return $this;
    }
 


    // 取得sql错误信息
    // 20190816172602
    public function getErr()
    {
        return $this->err!='ok' ? $this->err : db::mine()->getErr();
    }

    
    // 初始化各参数 20200423114432
    // 注意：执行后各参数必需恢复初始化，否则参数累加导致不可预知的错误
    protected function init(){
        $cnf=[
            'where' => 'WHERE 1',
            'order' => '',
            'limit' => 'LIMIT 500',
            'group' => '',
            'rType' => 'array',
            'dType' => 2,
            'dArr'=>[],
            'err'=>'ok',
        ];

        foreach($cnf as $k => $v){
            $this->$k=$v;
        }
    }

    
    // 设置返回信息，有错误则取回错误信息
    protected function setRtn($v)
    {
        if($v===false)
            $this->err = DB::mine()->getErr();

        return $v;
    }

    /*    
    // 设置为当前表 20190816165218
    // 注意此项非必需，默认使用 $this->$table
    //20200423135927 禁止切换表
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }
    */ 
}
