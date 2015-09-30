<?php
/* GMold - OpenSource PHP Template Engine
 * 
 * Version:2.0
 * 
 * Author:EchoSoar 
 *
 * © 2013-2015 IWenKu.net
 * 
 */

class gmold{
	// 编码 encode
	const charset="UTF-8";
	// GMold 根目录 root content
	private $gmoldRoot='';
	// 默认左模板变量标示符 default the left moulding board variety mark 
	private $leftSeparator="<\{";
	// 默认右模板变量标示符 default the right moulding board variety mark 
	private $rightSeparator="\}>";
	// 默认编译文件存储目录名 default the name of storage content of translating and editing document
	private $compileDirName="gmoldCompile";
	// 默认编译文件存储目录地址 default the address of storage content of translating and editing document
	private $compileRoot;
	// 模板数据存储对象 storage object of moulding board datas
	private $data;
	
	private $changeTime=1;
	
	private $isUseCatch=true;
	
	function __construct(){
		mb_internal_encoding(self::charset);
		$this->gmoldRoot=dirname(__FILE__);
		$this->init();
	}
	
	// 自动添加文件地址分隔符(\ for windows and / for linux) add the compart mark of file address automatically
	private function gtrim($path){
		return rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	}
	
	// 模板引擎初始化 initialize the moulding board engine
	public function init(){
		$this->compileRoot=$this->gtrim($this->gmoldRoot).$this->gtrim($this->compileDirName);
		if(!is_dir($this->compileRoot))
			mkdir($this->compileRoot,0777);
		$this->data=new stdClass;
	}
	
	// 以键值对、数组或对象的方式添加变量到模板数据存储对象 add varieties to storage objects of moulding board datas by key values couples,dada groups or objects
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
	
	// 获取模板数据存储对象内容 seize the contents of storage objects of moulding board datas
	public function getData(){
		return $this->data;
	}
	
	// 添加数组到模板数据存储对象 add data groups to storage objects of moulding board datas
	private function push($arrName,$arrValue){
		$this->data->$arrName=$this->arrtoobj($arrValue);
	}
	
	// 将数组转化为对象 transform data groups into objects
	private function arrtoobj($arr){
		$arr=json_decode(json_encode($arr));
		return $arr;
	}
	
	// 模板引擎内容解析完成出口  the exit of accomplishment of analysis of contents of moulding board engine 
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
	
	// 模板编译处理  processing of translating and editing of the moulding board 
	private function complie($path){
		$compileAddr=$this->compileRoot. md5($path).".php";
		if($this->isUseCatch && file_exists($compileAddr) && filemtime($compileAddr) >= filemtime($path)&&(microtime(true)-filemtime($compileAddr)<$this->changeTime)){return $compileAddr;}
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
	
	// 模板变量转换 converting the moulding board varieties 
	private function vartostr($string){
		$string=$this->forext($string);
		
		preg_match("/^(.*?)(\..*?){0,}$/i",$string,$match);
		if(property_exists($this->data,$match[1])){
			$resStr='<?php echo $this->data->'.str_replace($match[0],$match[1].$match[2],$string).';?>';
			$resStr=str_replace(".","->",$resStr);
		}else{
			$from=array(
				'/\./i',
				'/^if\s*:\s*(\w+)/i',
				'/^elseif\s*:\s*(\w+)$/i',
				'/^else$/i',
				'/^endif$/i',
				'/^switch\s*:\s*(\w+)$/i',
				'/^case\s*:\s*([\w\']+)$/i',
				'/^endswitch$/i',
				'/^endfor$/i'
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
				'} '
			);
			$pregRes=preg_replace($from,$to,$string);
			if($string!=$pregRes){
				$resStr='<?php '.$pregRes.' ?>';
			}else{
				$resStr=$pregRes;
			}	
		}
		return $resStr;
	}
	
	// 模板for循环处理 processing of for recycling of the moulding board
	private function forext($string){
		
		$froextPreg='/^for\[(\w+)\s*(>|>=|<|<=|==)\s*(\w+)\]$/i';
		
		preg_match_all($froextPreg,$string,$formatch);
		
		$forextNum=count($formatch[1]);
		for($i=0;$i<$forextNum;$i++){
			if(property_exists($this->data,$formatch[1][$i])){
				$start='$i=$this->data->'.$formatch[1][$i];
			}else{
				$start='$i='.$formatch[1][$i];
			}
			
			if(property_exists($this->data,$formatch[3][$i])){
				$end='$this->data->'.$formatch[3][$i];
			}else{
				$end=$formatch[3][$i];
			}
			$temForResStr='<?php for('.$start.';$i'.$formatch[2][$i].$end.';$i++){ ?>';
			$string=str_replace($formatch[0][$i],$temForResStr,$string);
		}
		if($forextNum==0){
			$forExtVarPreg='/(.*?)\.\$i$/i';
			$num=preg_match($forExtVarPreg,$string,$varmatch);
			if($num>0){
				if(property_exists($this->data,$varmatch[1])){
					$string=str_replace($varmatch[0],'<?php echo $this->data->'.$varmatch[1].'[$i];?>',$string);
				}
			}
			$forExtVarPreg='/^\$i$/i';
			$string=preg_replace($forExtVarPreg,'<?php echo $i;?>',$string);
				
		}
		return $string;
	}
	
	// 配置编译文件更新时间 set the updating time of translating and editing file
	public function setUpdateTime($time){
		$time=$time+1-1;
		if($time>=0){
			$this->changeTime=$time;
			return true;
		}
		return false;
	}
	
	// 配置编译文件存储目录 set the storage content of translating and editing file
	public function setCompileDir($dirname){
		if($this->createDir($this->dirAddrToLocal($dirname))){
			$this->compileDirName=$this->dirAddrToLocal($dirname);
			return true;
		}
		return false;
	}
	
	// 配置左模板变量标示符 set the left moulding board variety mark
	public function setLeftSeparetor($sep){
		$this->leftSeparator=preg_quote($sep);
		return true;
	}
	
	// 配置右模板变量标示符 set the right moulding board variety mark
	public function setRightSeparetor($sep){
		$this->rightSeparator=preg_quote($sep);
		return true;
	}
	
	// 配置是否开启模板编译缓存 set whether or not open cache of translating and editing for mouding board	
	public function setCatch($trOrFl){
		if($trOrFl===false){
			$this->isUseCatch=false;
		}else{
			$this->isUseCatch=true;
		}
		return true;
	}
	
	// 目录名称本地化处理 process of localizing the content name 
	private function dirAddrToLocal($dirname){
		$temdir=str_replace('\\',DIRECTORY_SEPARATOR,str_replace('/',DIRECTORY_SEPARATOR,$dirname));
		$temdir=ltrim($this->gtrim($temdir),DIRECTORY_SEPARATOR);
		return $temdir;
	}
	
	// 递归创建目录 recursion of creating the content
	private function createDir($dirname,$permissions = 0777){
		if (is_dir($dirname))
            return true;
        $_path = dirname($dirname);
        if ($_path !== $dirname)
            $this->createDir($_path, $permissions);
        return @mkdir($dirname, $permissions);
	}
}
?>