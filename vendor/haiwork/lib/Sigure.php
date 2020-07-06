<?php namespace lib;

/*
 * url签名/验证库
 * 20190521
 */

class Sigure
{
	use \kernel\traits\Baselib;

    /*
     * 生成签名字串
     * $get [必] array|queryStr 生成签名的原始数据
     * return queryStr
     *
     * 示例：
        $get=['uid'=>4,'nick'=>'aabc',];
        //$get='gid=10547&cp=del&uid=106';
        var_dump(generate($get));
     */
    function generate($get='')
    {
        if($get==false)
            $get=$_GET;
        else if(is_string($get))
            parse_str($get, $get);

        // var_dump($get);
        if(empty($get) || count($get)<1) return '';

        // 计算签名值
        $sigure = $this->calculate($get);
        // 加入queryStr
        $get['sigure']=$sigure;
        // 返回签名queryStr
        return http_build_query($get);
    }


    /*
     * 验证签名
     * $get [必] array|queryStr 要验证的数据（含sigure）
     * return queryStr
     *
     * 示例：
        $get=['uid'=>4,'nick'=>'aabc',];
        //$get='gid=10547&cp=del&uid=106';
        var_dump(generate($get));
     */
    function validate($get='')
    {
        if($get==false)
            $get=$_GET;
        else if(is_string($get))
            parse_str($get, $get);

        // var_dump($get);
        // 未传入
        if(empty($get['sigure'])) return false;

        // 取出传入的签名，删除原有sigure单元（用于计算验证）
        $sigure = $get['sigure'];
        unset($get['sigure']);

        // 传入比较
        return $sigure == $this->calculate($get);
    }

    /*
     * 计算签名
     * $get [必] array 要签名的数据
     * return sigureStr
     *
     * 示例：
        $get=['uid'=>4,'nick'=>'aabc',];
        //$get='gid=10547&cp=del&uid=106';
        var_dump(generate($get));
     */
    function calculate($get)
    {
        // 加入私钥
        $get['psk']=PSK;
        // 排序
        ksort($get);
        // 加密与返回
        return sha1(http_build_query($get));
    }	
 
}
