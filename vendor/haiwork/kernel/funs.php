<?php
/*
 * vit 框架助理Assistant/Asst 
 * 系统函数集合 
 * 20161101 LM:20200410141507 仅保留功能函数
*/


//== 支持方法 ===================================
/*
 * vendor第三方项目载入方法
 * 说明：所有项目规则： /pub/vendor/包名packageName/autoload.php
 * @$packageName packagist包的名称
 * 20190518
 * 
 	源代码示例:
  	require_once (FR_PUB."vendor/phpoffice/autoload.php");
	$spreadsheet = new Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setCellValue('A1', 'Hello World !');
	 
	$writer = new Xlsx($spreadsheet);
	$writer->save(FR_PUB.'cs_2018.xlsx');
 */
function vendor($packageName)
{
	require_once DOC_ROOT.'vendor/'.$packageName.'/vendor/autoload.php';
}


/*
 * 变量输出函数，替代var_dump、print_r等
 * 20200510170413
 */
function seevar($var, $notEcho=false)
{
    $r = \sprintf('<pre><code>%s</pre></code>', \var_export($var, true));
    echo $notEcho ? '' : $r;
    return $r;
}

// 合并配置: 用户(优先)+系统
// 注：只合并app下的配置
// chy 20200416130257
function cnf_merge($fileNameExt)
{
    $cnf = load(APP_DIR.$fileNameExt);
    $cnf += load(DOC_ROOT.'app/'.$fileNameExt);
    return $cnf;
}


/*
 * 载入配置文件
 * @access public
 * @param  string $file 配置文件名
 * @param  string $name 一级配置名
 * @return array
 */
function load($file)
{
    if(!is_file($file)) return [];

    $type   = pathinfo($file, PATHINFO_EXTENSION);
    $config = [];
    switch ($type) {
        case 'php':
            $config = include $file;
            break;
        case 'yml':
        case 'yaml':
            if (function_exists('yaml_parse_file')) {
                $config = yaml_parse_file($file);
            }
            break;
        case 'ini':
            $config = parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [];
            break;
        case 'json':
            $config = json_decode(file_get_contents($file), true);
            break;
    }


    return $config;
}
