<?php
class Stat extends Controller{
  function __construct($_filial, $_path){
    if(!$this->checkLogin()){
      $this->aAccessUrl=array_merge($this->aAccessUrl, array('login'));
      if(!isset($_REQUEST['ajax']))die($this->html(SITE_DIR.'/core/infoblocks/login.phtml'));
    }else parent::__construct($_filial, $_path);
  }
  public function login($pass=''){
    if(empty($pass) and isset($_REQUEST['oq_pass']))$pass=addslashes($_REQUEST['oq_pass']);
    $ret=false;
    $pass=md5($pass);
    if($pass==$this->getSettings('pass')){
      $_SESSION['oqLogin']=$pass;
      $ret=true;
    }elseif(isset($_SESSION['oqLogin']))unset($_SESSION['oqLogin']);
    if(isset($_REQUEST['ajax']))die(($ret)?'y':'n');
    else return $ret;
  }
  public function checkLogin(){
    if(isset($_SESSION['oqLogin']) and $_SESSION['oqLogin']==$this->getSettings('pass'))return true;
    else return false;
  }
}
?>