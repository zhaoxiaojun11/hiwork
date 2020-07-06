<?php namespace proj\ctl;
use \kernel\Rtn;
use \kernel\Request;
use \lib\DB5 as DB;
use Intervention\Image\ImageManagerStatic as Image;

class Cs extends \kernel\Controler{
    public function img(){
     //引入插件
      vendor('simplesoftwareio-simple-qrcode');
      $qr=new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator();
      $img = $qr
      ->format('png')
      ->size(400)
      ->encoding('UTF-8')
      ->generate('赵小军');
      
      $img = base64_encode($img);
      echo "<img src='data:image/png;base64,{$img}'>";
    }
    public function img2(){
        vendor('intervention-image');
  
        
        Image::configure(array('driver' => 'gd'));
        // 画布
        $img = Image::canvas(120, 80, [255,255,255,1]);
        $img->insert('https://ss0.bdstatic.com/94oJfD_bAAcT8t7mm9GUKT-xh_/timg?image&quality=100&size=b4000_4000&sec=1589191318&di=74f37c3fc1f1f2d817662085710cbf4f&src=http://a3.att.hudong.com/14/75/01300000164186121366756803686.jpg');
        echo $img->response('jpg',100);
    }
}