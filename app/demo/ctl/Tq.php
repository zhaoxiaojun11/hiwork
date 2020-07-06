<?php namespace proj\ctl;
use \kernel\Rtn as R;
use \lib\Mail as mail;
use \kernel\Request as rqt;

class Tq extends \kernel\Controler{
    public function play(){
        $this->display('tq.tpl');
    }

    public function axios(){
        $rqt = new rqt();
        $city=$rqt->input("city");
        $email=$rqt->input("mail");
        //获取api接口
        $json=\lib\Curl::mine()->exc("https://way.jd.com/he/freeweather?city=$city&appkey=759c9dddc0507cc46b32fc3d04b4dbc6");
        //对json解析成对象
        $obj = json_decode($json);
        //获取城市
        $chengshi=$obj->result->HeWeather5[0]->basic->city;
        //天气
        $cond=$obj->result->HeWeather5[0]->daily_forecast[0]->cond->txt_d;
        //日期
        $date=$obj->result->HeWeather5[0]->daily_forecast[0]->date;
        //温度
        $tmp=$obj->result->HeWeather5[0]->daily_forecast[0]->tmp;
        //
        $mail = new mail();
        $b = $mail->sendMail(["$email"=>'aa'],"天气预报","$chengshi"."$date"."$cond".'最高温度'."$tmp->max".'最低温度'."$tmp->min");
        $b ? R::okk() : R::err();
    }
}