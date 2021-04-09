<?php
function getMonthName($n){
  $m=array('', 'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');  
  if(isset($m[$n]))return $m[$n];
  else return false;
}
?>