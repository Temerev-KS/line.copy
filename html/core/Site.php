<?php
/*
$GLOBALS['_c_'] - текущий экземпляр Controller
$GLOBALS['_s_'] - текущий экземпляр Site
$GLOBALS['_bd_'] - БД
Пример доступа к дир. /info/
/info/12/getInfoblock/userList/ - должен быть файл /core/infoblocks/userList.phtml
/info/12/ - должен быть файл /info/templates/main.phtml
/info/12/getPage2 - должен быть файл /info/templates/main.phtml и определен метод getPage2 в /info/Info.php
если в info есть файл Info.php с классом Info, то создастся его экземпляр
в info должны быть:
  - /templates/main.phtml
12 - номер филиала (-1 - если без филиала)
*/
session_start();
class Site{
  public $aPath=array();
  static function getACurrentPath(){
    $requestUri=addslashes($_SERVER['REQUEST_URI']);
    if(($pos=strpos($requestUri,"?"))!==false)$requestUri=substr($requestUri,0,$pos);
    $requestUri=preg_replace("/(\\.)(\\.[\\\\\\/])/is", "\\1 \\2", $requestUri);
    $requestUri=preg_replace("/[\\.\\/\\\\\\x20\\x22\\x3c\\x3e\\x5c]{30,}/", " X ", $requestUri);
    $requestUri=explode('/', $requestUri);
    $requestUri=array_diff($requestUri, array(''));
    return array_values($requestUri);
  }
  static function start(){
    require_once('config.php');
    require_once('func.php');
    require_once('LeaBd.class.php');
    require_once('Controller.php');
    $GLOBALS['_bd_']=new LeaBd(BD_BASE, BD_USER, BD_PASS, BD_HOST);
    $GLOBALS['_s_']=new Site(self::getACurrentPath());
  }
  static function badUrl(){
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');		
    die();
  }
  public function __construct($aPath){
    if(SHOW_ERROR){
      error_reporting(E_ALL);
      ini_set('display_errors','On');    
    }else{
      error_reporting(0);
      ini_set('display_errors','off');    
    }
    $this->aPath=$aPath;
    $this->route();
  }
  public function route(){
    $_controller='';
    $_filial='-1';
    $_action='getPage';
    if(isset($this->aPath[0]))$_controller=addslashes(ucfirst($this->aPath[0]));
    if(isset($this->aPath[1]))$_filial=addslashes($this->aPath[1]);
    if(isset($this->aPath[2]))$_action=addslashes($this->aPath[2]);
    if(!empty($_controller) and !empty($_filial)){
      $_p=SITE_DIR.'/'.strtolower($_controller).'/'.$_controller.'.php';
      if(file_exists($_p)){
        include_once($_p);
        $GLOBALS['_c_']=new $_controller($_filial, $this->aPath);
        if(method_exists($GLOBALS['_c_'], $_action) and in_array($_action, $GLOBALS['_c_']->aAccessUrl))$GLOBALS['_c_']->$_action();
        die();
      }else{
        $GLOBALS['_c_']=new Controller($_filial, $this->aPath);
        if(method_exists($GLOBALS['_c_'], $_action) and in_array($_action, $GLOBALS['_c_']->aAccessUrl))$GLOBALS['_c_']->$_action();
        die();        
      }
    } 
    self::badUrl();    
  }
}
?>