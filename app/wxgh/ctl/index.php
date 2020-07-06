<?php namespace proj\ctl;
use \kernel\Rtn as rtn;

class Index extends \kernel\Controler{
    public function index(){
        rtn::e404();
        // echo 'index';
    }
}