<?php
ini_set('default_charset', 'utf-8');
mb_internal_encoding('utf-8');
//export
//$oBd=new LeaBd('bd', 'usr', 'pass');
/*
lastInsertId()
query()
getArr()
setCachePath()
getArrCache()
cacheClear()
getLastQueryTime()
msg()
setConnect()
*/
class LeaBd{
	private $db=null;
	private $MYSQL_BASE=null;
	private $MYSQL_USER=null;
	private $MYSQL_PASSWORD=null;
	private $MYSQL_HOST=null;
	private $_lastQueryTime=0;
	private $_cachePath='';
	private $_filePerm=777;
	function __construct($MYSQL_BASE, $MYSQL_USER, $MYSQL_PASSWORD, $MYSQL_HOST='localhost'){
		$this->setConnect($MYSQL_BASE, $MYSQL_USER, $MYSQL_PASSWORD, $MYSQL_HOST);
	}
	public function setConnect($MYSQL_BASE, $MYSQL_USER, $MYSQL_PASSWORD, $MYSQL_HOST='localhost'){
		$this->db=null;
		$this->MYSQL_BASE=$MYSQL_BASE;
		$this->MYSQL_USER=$MYSQL_USER;
		$this->MYSQL_PASSWORD=$MYSQL_PASSWORD;
		$this->MYSQL_HOST=$MYSQL_HOST;		
	}
	public function setCachePath($path){$this->_cachePath=$path;}
	public function getLastQueryTime(){return $this->_lastQueryTime;}
	public function lastInsertId(){
		if($this->connect())return $this->db->lastInsertId();
		else return false;
	}
	public function query($q){
		$time1 = explode(" ", microtime());
		$ret=false;
		if($this->connect()){
			if($this->db->query($q))$ret=true;
			$time2=explode(" ", microtime());
			$this->_lastQueryTime=round((float)$time2[0]+(float)$time2[1]-(float)$time1[0]-(float)$time1[1], 3);
		}
		return $ret;
	}
	public function getArr($sql, $fieldKey=''){
		$time1 = explode(" ", microtime());
		$arr=array();
		if($this->connect()){
			$q=$this->db->prepare($sql);
			$q->execute();
			$time2 = explode(" ", microtime());
			$this->_lastQueryTime=round((float)$time2[0]+(float)$time2[1]-(float)$time1[0]-(float)$time1[1], 3);
			if($q){
				while($a=$q->fetch(PDO::FETCH_ASSOC)){
					if(empty($fieldKey))$arr[]=$a;
					else $arr[$a[$fieldKey]]=$a;
				}
			}
		}
		return $arr;
	}
	public function getArrCache($name, $sql, $fieldKey=false){
		if($fieldKey)$name.='_'.$fieldKey;
		if($this->arrCacheCheck($name)){
			return $this->arrCacheGet($name);
		}else{
			$ret=$this->getArr($sql,$fieldKey);
			$this->arrCacheSet($name,$ret);
			return $ret;
		}
	}	
	protected function arrCacheCheck($name){
		$fileName=$this->translit($name);
		if(file_exists($this->_cachePath.$fileName)){
			return true;
		}else return false;
	}
	protected function arrCacheSet($name,$a){
		$fileName=$this->translit($name);
		if($cache=serialize($a)){
			file_put_contents($this->_cachePath.$fileName,$cache);
			chmod($this->_cachePath.$fileName, $this->_filePerm);
		}	
		else return false;
		return true;
	}
	protected function arrCacheGet($name){
		$fileName=$this->translit($name);
		if(file_exists($this->_cachePath.$fileName)){
			$cache=file_get_contents($this->_cachePath.$fileName);
			$ret=unserialize($cache);
			if(is_array($ret))return $ret;
			else return false;
		}
		return false;
	}
	public function cacheClear($name){
		$fileName=$this->translit($name);
		if(file_exists($this->_cachePath.$fileName)){
			if(unlink($this->_cachePath.$fileName))return true;
		}
		return false;
	}		
	public function translit($text,$wordCount=null){
		$tmp=array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya');		$k = array_keys($tmp);
		$v=array_values($tmp);
		$tmp = str_replace($k, $v, mb_convert_case($text, MB_CASE_LOWER));
		$tmp = preg_replace('/\W/', '_', $tmp);
		while (strpos($tmp, '__') !== false) {
			$tmp = str_replace('__', '_', $tmp);
		}
		$_ret='';
		return $tmp;
	}	
	public function msg($exp,$die=false,$echo=false){
		if(!$echo){
			$dbg=debug_backtrace();
			ob_start();
			echo '<pre class="debug_panel" style="top:20px;left:20px;width:800px;height:500px;overflow:scroll;background:#d5e7c8;position:fixed;z-index:9999;padding:10px;-moz-box-shadow: 0 0 10px rgba(0,0,0,0.5);-webkit-box-shadow: 0 0 10px rgba(0,0,0,0.5);box-shadow: 0 0 20px rgba(0,0,0,0.5);color:gray;">';
			echo '<div>';
			var_dump($exp);
			echo '</div>';
			if(isset($dbg[0]['function']))echo('<hr/><small style="float:right;color:black;font-size:9px;">Вызов из '.$dbg[0]['file'].':'.$dbg[0]['line'].'</small>');
			echo '</pre>';
		}else{
			echo '<div class="debug_panel" style="top:20px;left:20px;width:800px;background:#d5e7c8;position:fixed;z-index:9999;padding:10px;-moz-box-shadow: 0 0 10px rgba(0,0,0,0.5);-webkit-box-shadow: 0 0 10px rgba(0,0,0,0.5);box-shadow: 0 0 20px rgba(0,0,0,0.5);color:gray;">';
			echo '<center style="overflow-y:hidden;overflow-x:auto;margin-bottom:30px;font-size:18px">';
			ob_start();
			echo $exp;
			echo '</center></div>';
		}
		echo '
		<script>
			var dp=document.getElementsByClassName("debug_panel");
			dp[0].innerHTML=dp[0].innerHTML.trim();
			var lnkClose=document.getElementsByClassName("debug_lnk_close");
			if(lnkClose.length==0)dp[0].innerHTML=\'<a href="#" style="position:fixed;top:3px;left:850px;padding:5px;border-radius:90;color:white;background:black;text-decoration:none;" class="debug_lnk_close" onclick="adp=document.getElementsByClassName('.chr(92).'\'debug_panel'.chr(92).'\');adp[0].parentNode.removeChild(adp[0]);return false;">X</a><br clear="all"/>\'+dp[0].innerHTML;
			if(dp.length==2){
				dp[0].innerHTML=dp[0].innerHTML.trim()+dp[1].innerHTML.trim();
				dp[1].parentNode.removeChild(dp[1]);
			}
		</script>';
		echo ob_get_clean();
		if($die)die();
	}
	private function connect(){
		$ret=false;
		if(!is_null($this->db) and get_class($this->db)=='PDO'){
			$ret=true;
		}else{
			if($this->MYSQL_BASE!='' and $this->MYSQL_USER!=''){
				if($db=new PDO('mysql:host='.$this->MYSQL_HOST.';dbname='.$this->MYSQL_BASE.';charset=utf8;',$this->MYSQL_USER,$this->MYSQL_PASSWORD)){
					$this->db=$db;
					$ret=true;
				}
			}
		}
		return $ret;
	}	
}
?>