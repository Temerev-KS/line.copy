<?php
class Controller{
  public $aPath=array();
  public $nFilial='';
  public $cpath='';
  public $bd=null;
  public $aAccessUrl=array('getPage', 'getInfoblock', 'registerUser', 'getUser', 'getUserList', 'getAllUserList', 'setUserStatus');
  public function __construct($nFilial, $aPath=array()){
    $this->aPath=$aPath;
    $this->setCpath(SITE_DIR.'/'.strtolower(addslashes($this->aPath[0])));
    $this->nFilial=$nFilial;
    $this->bd=&$GLOBALS['_bd_'];
  }
  public function getPage(){
    die($this->html());
    return false;
  }
  public function setCpath($v){$this->cpath=$v;}
  public function getCpath(){return $this->cpath;}
  public function html($tpl=null){
    if(is_null($tpl)){
      $tpl=$this->getCpath().'/templates/main.phtml';
      if(!file_exists($this->getCpath()))Site::badUrl();
    }elseif(!file_exists($tpl))Site::badUrl();
    ob_start();
    require($tpl);
    $ret=ob_get_clean();
    return $ret;
  }
  public function getInfoblock(){
    $ib=$this->aPath[count($this->aPath)-1];
    die($this->ib($ib));
  }
  public function ib($ibName, $path=null){
    if(is_null($path))$path=SITE_DIR.'/core/infoblocks/';
    $_p=$path.$ibName.'.phtml';
    echo $this->html($_p);
  }
  public function getCurrentPosition(){
    if(isset($_SESSION['oq_current_position'][$this->nFilial]))return $_SESSION['oq_current_position'][$this->nFilial];
    else return '0';
  }
  public function setCurrentPosition($n=0){
    if(empty($n) and isset($_POST['oq_current_position'])){
      $n=intval($_POST['oq_current_position']);
    }
    if(!empty($n))$_SESSION['oq_current_position'][$this->nFilial]=$n;
    elseif(isset($_SESSION['oq_current_position'][$this->nFilial]))unset($_SESSION['oq_current_position'][$this->nFilial]);
    return $this->getSettings();
  }
  public function getFilials(){
    return $GLOBALS['filials'];
  }
  public function getSettings($key=''){
    $GLOBALS['settings']['nFilial']=$this->nFilial;
    $GLOBALS['settings']['position']=$this->getCurrentPosition();
    $_settings=$GLOBALS['settings'];
    if(file_exists(SITE_DIR.'/config_'.$this->nFilial.'.php')){
      include(SITE_DIR.'/config_'.$this->nFilial.'.php');
      $_settings=array_merge($_settings, $GLOBALS['settings']);
    }
    if(!empty($key) and isset($_settings[$key]))return $_settings[$key];
    return $_settings;
  }
  public function getTitle($_type, $_count){
    $_n=$_count+1;
    $_n=strval(str_pad($_n,3,"0",STR_PAD_LEFT));
    if($_type==1)return 'O-'.$_n;
    if($_type==2)return 'Z-'.$_n;
  }
  public function getTypeTitle($_type){
    switch($_type) {
      case '1':return $this->getSettings('type_1_title');
      case '2':return $this->getSettings('type_2_title');
    }
  }
  public function registerUser(){
    if(!isset($_REQUEST['type']))$type=1;else $type=intval($_REQUEST['type']);
    $ret=array('error'=>'', 'data'=>array());
    $countAll=$this->bd->getArr($q='SELECT COUNT(*) as c FROM users WHERE Filial='.$this->nFilial.' AND `Type`="'.$type.'" AND (`Created`>=CURDATE() OR (`Status`<>"closed" AND `Created`<CURDATE()))');
    $countAll=intval($countAll[0]['c']);
    $title=$this->getTitle($type, $countAll);
    if($this->bd->query($q='INSERT INTO users SET Title="'.$title.'", Filial="'.$this->nFilial.'", CountAll="'.$countAll.'", `Type`="'.$type.'"')){
      $ret['data']=array('ID'=>$this->bd->lastInsertId(), 'Title'=>$title, 'Filial'=>$this->nFilial, 'Created'=>date("Y-m-d H:i:s"), 'CountAll'=>$countAll, 'Type'=>$type);
    }else $ret['error']='y'.$q;
    if(isset($_REQUEST['ajax']))die(json_encode($ret));
    return $ret;
  }
  public function getUser($id=''){
    $ret=array('error'=>'', 'data'=>array());
    if(empty($id) and isset($_REQUEST['id']))$id=intval($_REQUEST['id']);
    $_user=$this->bd->getArr($q='SELECT * FROM users WHERE Filial='.$this->nFilial.' AND ID='.$id.' ORDER BY Created');
    if(isset($_user[0]))$ret['data']=$_user[0];
    if(isset($_REQUEST['ajax']))die(json_encode($ret));
    return $ret;
  }
  public function getAllUserList(){
    $ret=array('error'=>'', 'data'=>array(), 'date'=>'<div>'.date('d').' '.getMonthName(date('n')).'</div><div>'.date('H:i').'</div>');
    $_data=$this->bd->getArr($q='SELECT * FROM users WHERE Filial='.$this->nFilial.' ORDER BY Created');
    foreach($_data as $row){
      if(!isset($ret['data'][$row['Status']]))$ret['data'][$row['Status']]=array();
      $ret['data'][$row['Status']][]=$row;
    }
    if(isset($_REQUEST['ajax']))die(json_encode($ret));
    return $ret;
  }
  public function getUserList($_status=''){
    if(empty($_status) and isset($_REQUEST['_status']))$_status=addslashes($_REQUEST['_status']);
    $ret=array('error'=>'', 'data'=>array());
    $ret['data']=$this->bd->getArr($q='SELECT * FROM users WHERE Filial='.$this->nFilial.' AND Status="'.$_status.'"'.' ORDER BY Created');
    if(isset($_REQUEST['ajax']))die(json_encode($ret));
    return $ret;
  }
  public function checkStatus($userId, $newStatus){
    $a=$this->bd->getArr('SELECT `Status` FROM users WHERE ID='.intval($userId));
    if(isset($a[0]['Status'])){
      $oldStatus=$a[0]['Status'];
      if($newStatus=='process' and $oldStatus=='new')return true;
      if($newStatus=='closed' and $oldStatus=='process')return true;
    }elseif($newStatus=='new')return true;
    return false;
  }
  public function setUserStatus($userId='', $_status='', $_position=''){//Status - new, process, closed
    $error='y';
    if(empty($userId) and isset($_REQUEST['userId']))$userId=intval($_REQUEST['userId']);    
    if(empty($_status) and isset($_REQUEST['_status']))$_status=addslashes($_REQUEST['_status']);
    if(empty($_position) and isset($_REQUEST['_position']))$_position=intval($_REQUEST['_position']);
    if($this->checkStatus($userId, $_status)){
      if($_status=='process')$_add=', TimeStart=NOW(), `TimeWait`=TIMESTAMPDIFF(SECOND, Created, NOW())';
      elseif($_status=='closed')$_add=', TimeEnd=NOW()';
      else $_add='';
      if($this->bd->query($q='UPDATE users u SET Position="'.$_position.'", Status="'.$_status.'" '.$_add.' WHERE ID='.$userId))$error='';
    }
    if(isset($_REQUEST['ajax']))die($error);
    return $error;
  }
}
?>