<?php namespace proj\ctl;


class Cs 
{
	public function index()
	{
		var_dump($abc);
		echo 'this is cs::index';
	}

	public function list()
	{
		$dir = 'C:/Users/86199/Videos';
		$list = scandir($dir);
		foreach ($list as $f) {
			if($f=='.' || $f=='..' || substr($f,-3)!='mp4' ) continue;

			$date = substr($f, 0, 4);
			// var_dump($date);continue;
			$date = substr($date,0,2).'月'.substr($date,-2).'日';

			$t = str_replace(['-1-','-2-','-3-','-4-', ], ['-09:30~10:15-第1节-','-10:30~11:15-第2节-','-14:30~15:15-第3节-','-15:30~16:15-第4节-',], substr($f, 4, -4));

			echo '<li>'.$date.$t.'</li>';
		}


	}
}