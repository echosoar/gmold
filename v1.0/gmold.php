<?php
/* GMold 
 * 
 * Version:1.0 CTime:2015-9-22
 * 
 * Author:EchoSoar 
 *
 * WebSite:http://iwenku.net
 * 
 */
 
class gmold{
	// Left template separetor 
	private $leftSeparator="<\{";
	// Right template separetor 
	private $rightSeparator="\}>";
	// Compile directory name
	private $compileDirName="gmoldCompile";
	// Compile directory address
	private $compileRoot;
	// Template data object
	private $data;
	
	private $changeTime=1;
	
	function __construct(){
		$this->init();
	}
	
	//auto add directory separator(\ for windows or / for linux) if path don't have
	private function gtrim($path){
		return rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	}
	
	// initial (create compile directory;set document root address;clear template data)
	public function init(){
		$this->compileRoot=$this->gtrim($this->compileDirName);
		if(!is_dir($this->compileRoot))
			echo mkdir($this->compileRoot,0777);
		$this->data=new stdClass;
	}
	
	// Add data by array or object or other to template data
	public function set($key,$val=''){
		if(is_array($key)){
			foreach($key as $temKey=>$temVal){
				if(!is_array($temVal)){
					$this->data->$temKey=$temVal;
				}else{
					$this->push($temKey,$temVal);
				}
			}	
		}else if(is_object($key)){
			foreach(get_object_vars($key) as $temKey=>$temVal)
				$this->data->$temKey=$temVal;
		}else{
			$this->data->$key=$val;
		}
	}
	
	// Get template data
	public function getData(){
		return $this->data;
	}
	
	
	private function push($arrName,$arrValue){
		$this->data->$arrName=$this->arrtoobj($arrValue);
	}
	
	//Array to Object
	private function arrtoobj($arr){
		$arr=json_decode(json_encode($arr));
		return $arr;
	}
	
	public function get($templatePath){
		if(file_exists($templatePath)){
			ob_start();
			include($this->complie($templatePath));
			$compileData=ob_get_clean();
			return $compileData;
		}else{
			return 'GMold Runtime Error : Template ['.$templatePath.'] not Exists!';
		}
	}
	
	private function complie($path){
		$compileAddr=$this->compileRoot. md5($path).".php";
		if(file_exists($compileAddr) && filemtime($compileAddr) >= filemtime($path)&&(microtime(true)-filemtime($compileAddr)<$this->changeTime)){return $compileAddr;}
		
		$templateData=file($path);

		$preg_str="/".$this->leftSeparator."\s*(.*?)\s*".$this->rightSeparator."/i";
		
		$compileRes=array();
		foreach($templateData as $line){
			$num=preg_match_all($preg_str,$line,$match);
			if($num>0){
				for($i=0;$i<$num;$i++){
					$line=str_replace($match[0][$i],$this->vartostr($match[1][$i]),$line);
				}
			}
			$compileRes[]=$line;
		}

		$compileData='<?php /* GMold Compile file | CTime:'.date("Y-n-j").'*/ ?>'.implode(" ",$compileRes);
		
		$fp=fopen($compileAddr,"w");
		fwrite($fp,$compileData);
		fclose($fp);
		
		return $compileAddr;
	}
	
	private function vartostr($string){
		preg_match("/^(.*?)(\..*?){0,}$/i",$string,$match);
		if(property_exists($this->data,$match[1])){
			$resStr='<?php echo $this->data->'.str_replace($match[0],$match[1].$match[2],$string).';?>';
			$resStr=str_replace(".","->",$resStr);
		}else{
			$from=array(
				'/\./i',
				'/^if:(\w+)/i',
				'/^elseif:(\w+)$/i',
				'/^else$/i',
				'/^endif$/i',
				'/^switch:(\w+)$/i',
				'/^case:([\w\']+)$/i',
				'/^endswitch$/i',
			);
			$to=array(
				'->',
				'if($this->data->$1){',
				'} else if($this->data->$1){',
				'} else {',
				'} ',
				'switch($this->data->$1){default:',
				'break;case $1:',
				'break;}',
			);
			$resStr="<?php ".preg_replace($from,$to,$string)." ?>";
		}
		return $resStr;
	}
}
?>