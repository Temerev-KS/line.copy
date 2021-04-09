<?php
$GLOBALS['settings']=array(
  'pass'=>md5('123'),
	'zvuk1'=>'/pics/zvuk1.mp3',
  'position_count'=>9,//рабочих мест
  'time_refrash'=>1000,//время обновления экранов ms
  'count_info_inwork'=>'12',
  'count_info_wait'=>'120',
  'type_1_title'=>'Оформление заказа',
  'type_2_title'=>'Получение готового заказа',
  'video_html'=>'<iframe width="100%" height="100%" src="https://www.youtube.com/embed/P4k2LNPRV-0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
);
$GLOBALS['filials']=array(
  1=>'Василеостровский',
  2=>'Московские ворота',
  3=>'Невский',
  4=>'Петроградский'
);
define('SITE_DIR', dirname(dirname(__FILE__)));
define('BD_USER', 'rpgserv_line');
define('BD_BASE', 'rpgserv_line');
define('BD_PASS', 'asdjlk%$*zc');
define('BD_HOST', 'localhost');
define('SHOW_ERROR', false);
?>