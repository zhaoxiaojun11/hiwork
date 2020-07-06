<?php namespace kernel;

use \lib\Db5 as DB;
use \kernel\Rtn as rtn;

/*
 * 数据表模型类
 * 说明：此类是模型基类，所有模型必需继承此类
 * 方法：
    class childModel extends \kernel\Model
    {
        protected $table='tableName';
        protected $fixeds=['id'];       
    }  

    * 示例一：
        //将id=56的nick设置新值并保存
        $mdl = new self(56);
        var_dump($mdl);
        $mdl->nick='王三小';
        $mdl->save();
    
    * 示例二：
        $mdl = new slef;
        $r = $mdl -> add([
            'username'=>'zhao',
            'password'=>password_hash('123456', \PASSWORD_DEFAULT),
        ], $id);
        var_dump($r, $mdl->getErr(), $id);
 *
 * chy 20200423170614
 */

class Model
{
    use traits\Baselib;
	protected $table='undefined';//当前表名，继承时必需被覆盖
    protected $fixeds=['id'];//定义模型中不可被修改的字段，继承时应根据情况被覆盖
    protected $key=[];//唯一的索引标记
    protected $row=[];//模型的单行值，由getRow取得，用于字段映射操作
    protected $err='';//错误信息

    /* 
     * $keyVal [必] array|int 数据的 主键/唯一索引 名称
     * $fields [选] string 待返回的字段
     * 
     * 示例：
     * $mdl=new Model(3);//取ID为3的行数据，同下面例子
     * $mdl=new Model(['id'=>3]);//id主键:可以只填入值，也可以填入数组形式
     * $mdl=new Model(['username'=>'chy']);//通过唯一索引username取行，必需是关联数组
     * $mdl=new Model(['id'=>5, 'username'='abc@me.com']);//联合式唯一索引
     * var_dump($r, $md::getErr());
     * 
     * 20200509164006
     */
    public function __construct($keyVal=null, $fields='*')
    {
        if(!$this->table) rtn::err('未定义表名');

        if($keyVal!==null){
            $this->map($keyVal, $fields);
        }
    }

    // 取回模型对应的数组（一行数据）
    // 20200509165346
    public function getRow(){
        return $this->row;
    }

    
    // 整体更改模型对应的数组（一行数据）
    // 20200509165346 注意：此方法很危险，会批量修改数据
    public function setRow(array $row){
        return $this->row=$row;
    }

    /*
     * 设定并取得一行数据 （必需是通过主键或唯一索引取）
     * 注：此方法仅作为__construct的支持函数，规定不可外部调用
     * 20190816165444
     * 
     * LM:有结果则合并查询结果与索引的值，无则返回空数组 20200509164938
     */
    protected function map($keyVal, $fields='*')
    {
        // 取回唯一索引
        $this->key = is_array($keyVal) ? $keyVal : ['id'=>$keyVal];
        // 以索引合成查询条件
        $whr = $this->whereArr($this->key);

        // 返回查询结果
        $r = DB::mine()->R("SELECT {$fields} from {$this->table} WHERE {$whr} limit 1", $this->key, 1);

        if($r===false) rtn::err('单行语句错误！'.DB::mine()->getErr());
        // var_dump($r);exit;

        // 有结果则合生成行数组，无则返回空数组
        $this->row = $r ? $r+$this->key : [];
        // var_dump($this->row);exit;

        return $this;
    }

    /* 
     * 保存对模型数据的修改
     *  $mdl = (new \mdl\Goods)->getRow(3);
     *  $mdl->nick ='xiaoming'; 
     *  $mdl->age ='16'; 
     *  $mdl->save();//将id=3的nick和age进行修改
     */
    public function save()
    {
        if(empty($this->row))
            rtn::err('没有数据无法保存！');

        $c = DB::mine()->IU($this->table, $this->row);

        return $this->setRtn($c);
    }


    /* 
     * 删除一行数据
     * $mdl = (new \mdl\Goods)->getRow(1005)->del();//删除id为1005的行
     * chy 20200423165311
     */
    public function del()
    {
        $whr = $this->whereArr($this->key);
        // var_dump($whr);
        $c = DB::mine()->D($this->table, $whr, $this->key);

        // 删除后清除数据和索引
        $this->row=[];

        return $this->setRtn($c);
    }

    /*
     * 创建一行数据
     * $row [必] 一维字段名称的关联数据
     * $insertId [选] 新增行的id
     * 
     *  $mdl = new \mdl\Goods;
        $c = $mdl->add([
            'username'=>'tyx',
            'password'=>password_hash('123456', \PASSWORD_DEFAULT),
        ], $id);
        var_dump($c, $id, $mdl->getErr()); 
     * chy 20200423165332
     */
    public function add(array $row, &$insertId=null)
    {
        if(empty($row)){
            $this->err = '添加的数据必需是一维数组';
            return false;
        }

        // 写入
        $c = DB::mine()->I($this->table, $row);
        // 取得加入数据的ID
        $insertId=DB::mine()->getLastid();
        return $this->setRtn($c);
    }

    // 取字段对应的值
    public function __get($k){
        if(isset($this->row[$k])) return $this->row[$k];
    }
   
    // 设置字段对应的值
    public function __set($k, $v){
        if(isset($this->row[$k]) && !\in_array($k,$this->fixeds))
            $this->row[$k]=$v;
    }


    // 将索引转化为条件 20200423162649
    protected function whereArr(array $where=[])
    {
        $whr=[];
        foreach ($where as $k => $v) {
            $whr[] = "`{$k}`=:{$k}";
        }
        return implode(" and ", $whr);
    }

    // 设置返回信息，有错误则取回错误信息
    protected function setRtn($v)
    {
        if($v===false)
            $this->err = DB::mine()->getErr();

        return $v;
    }

    // 设置返回信息，有错误则取回错误信息
    public function getErr()
    {
        return $this->err;
    }
}
