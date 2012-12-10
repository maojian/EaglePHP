<?php

/**
 * 生成chm格式的开发帮助文档
 * @copyright Copyright @2011, MAOJIAN
 * @author maojianlw@139.com
 * @since 1.2 - 2011-11-1
 */

class GenController extends Controller{
	
	public  $hhw_dir = 'D:\Program Files\HTML Help Workshop\hhc.exe';	
	
    private $manual_dir = 'manual/';
    
    private $compiled_file = '';
    
    private $default_topic = 'index.html';
    
    private $contents_file = 'index.hhc';
    
    private $hhp_file = 'index.hhp';
    
    private $hhk_file = 'index.hhk';
    
    private $project_dir = '';
    
    private $reflect_cls = '';
    
    private $auto_load_dir = '';
    
    private $cur_dir = '';
    
    
    /**
     * 函数库文件名称
     */
    private $funs_file = 'functions.php'; 
    
    
    /**
     * 初始化相关数据
     */
    public function __construct(){
    	set_time_limit(0);
    	spl_autoload_register(array($this, 'classAutoLoader'));
    	$this->cur_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    	if(is_dir($this->manual_dir))
    	{
    		rm_dir($this->manual_dir);
    	}
    	mk_dir($this->manual_dir);
    }
    
    
    /**
     * 首页
     */
    public function indexAction(){
        $hhc = $project_dir = $project_name = null;
    	if($this->isPost()){
    		$hhc = $this->post('hhc');
    		$project_dir = $this->post('project_dir');
    		$project_name = $this->post('project_name');
    		
    		if($hhc == '' || $project_dir == '' || $project_name == ''){
    			$this->assign('err_msg', '请填写完整。');
    		}elseif(!file_exists($hhc)){
    			$this->assign('err_msg', 'hhc.exe文件错误。');
    		}elseif(!is_dir($project_dir)){
    			$this->assign('err_msg', 'WEB工程无效。');
    		}else{
    			$this->hhw_dir = '"'.$hhc.'"';
    			$this->project_dir = realpath($project_dir);
    			$this->compiled_file = "{$project_name}.chm";
    			define('__PROJECT_NAME__', $project_name);
    			
    			$this->prepAction();
    			$this->classAction();
    			$this->execAction();
    			
    		}
    	}
    	$this->assign('hhc', $hhc ? $hhc : $this->hhw_dir);
    	$this->assign('project_dir', $project_dir ? $project_dir : str_replace('\\', '/', ROOT_DIR));
    	$this->assign('project_name', $project_name ? $project_name : basename(ROOT_DIR));
    	$this->display();
    }
    
    
    /**
     * 【第一步】预先处理相关文件，做好准备工作
     */
    public function prepAction(){
    	$class_arr = $this->readDirect($this->project_dir);
    	$this->createHHC($class_arr);
    	$class_arr = $this->getFileList($class_arr);
    	$_SESSION['__CLASS__LIST__'] = $class_arr;
    	$this->createHTML($class_arr);
    	$this->createHHP();
    	$this->createHHK();
    }
    
    
    /**
     * 【第二步】生成类与方法文件
     */
    public function classAction(){
    	$class_arr = $_SESSION['__CLASS__LIST__'];
    	if(is_array($class_arr)){
    		foreach($class_arr as $class){
				foreach($class as $class_file){
		    		$this->genClass($class_file);
				}
    		}
    	}
    }
      
    
    /**
     * 【第三步】生成CHM文档
     */
    public function execAction(){
    	$cur_dir = $this->cur_dir.'/'.$this->manual_dir;
    	$hhp_file = $cur_dir.$this->hhp_file;
    	$hhp_file = str_replace('/', '\\', $hhp_file);
    	$return_str = $default_topic = null;
    	if(file_exists($hhp_file)){
    		$return_arr = array();
    		$return_var = 0;
    		exec("{$this->hhw_dir} {$hhp_file}", $return_arr, $return_var);
			foreach($return_arr as $return){
				$return_str .= mb_convert_encoding($return, 'utf-8', 'gbk').'<br/>';
			}
			//if($return_var === 1){
				$return_str .= "<a href='".__APP_RESOURCE__."manual/{$this->compiled_file}' target='_blank'>CHM文档</a>&nbsp;&nbsp;<a href='../{$this->manual_dir}{$default_topic}' target='_blank'>HTML文档</a>";
			//}
    		exit(template($return_str, 'CHM执行结果'));
    	}else{
    		throw_exception("{$hhp_file} not exists.");
    	}
    }
    
    
	/**
	 * 解析类
	 */
    public function genClass($file_path){
		// 编译类文件
		$class_name = '';
		$dir_name = $this->getDirname($file_path, $class_name);
		$is_fun_file = ($this->funs_file == "$class_name.php");
		if(class_exists($class_name) || $is_fun_file){
			try{
				if($is_fun_file){
					$reflect = new Reflect();
			    	$methods = $reflect->getFunctions($file_path);
			    	$class = array('name'=>'Functions', 'comment'=>array('用户自定义函数.'));
				}else{
					$this->reflect_cls = new Reflect($class_name, file($file_path));
			    	$class = $this->reflect_cls->getDocComment($this->reflect_cls->reflect);
			    	$methods = $this->reflect_cls->getMethods();
				}
				
		    	if(is_array($methods)){
		    		$this->assign('class_name', $class_name);
		    		foreach($methods as &$m){
		    			$m['link'] = $this->getFileName("{$class_name}__{$m['name']}", 'method');
	    				$this->assign('method', $m);
	    				$this->createFile($this->manual_dir.$dir_name.'/'.$m['link'], $this->fetch('method'));
		    		}
		    	}
		    	
		    	$file_name = "{$dir_name}/".$this->getFileName($class_name);
		    	$class['link'] = $this->getFileName($class_name, 'source');
		    	$this->assign('class', $class);
		    	$this->assign('methods', $methods);
		    	$content = $this->fetch('methods');
		    	$this->createFile($this->manual_dir.$file_name, $content);
		    	$_ENV['files_indexs'][] = $file_name;
		    	$class['link'] = $file_name;
		    	return $class;
			}catch(ReflectionException $e){
				throw_exception($e);
			}
		}
		return false;
    }
    
    
    public function testAction(){
    	$include_fun = 'D:\www\album\LIB\System.function.php';
    	include $include_fun;
    	$reflect = new Reflect();
    	$fun_arr = $reflect->getFunctions($include_fun);
    	
    	$class = array('name'=>'Functions', 'comment'=>array('用户自定义函数.'));
    	$this->assign('class', $class);
		$this->assign('methods', $fun_arr);		
		$this->display('gen/methods');
    }
  
 	
 	public function methodsAction(){
 		try{
 			$name = $this->post('name');
	    	$this->reflect_cls = new Reflect('filter',  file('D:\www\Hi\Lib\filter.class.php'));
	    	
	    	$class = $this->reflect_cls->getDocComment($this->reflect_cls->reflect);
	    	$methods = $this->reflect_cls->getMethods();
	    	foreach($methods as $m){
	    		if($m['name'] == $name){
	    			$method = $m;
	    			break;
	    		}
	    	}

	    	//$this->assign('class', $class);
		    //$this->assign('methods', $methods);
	    	
	    	$this->assign('class_name', $this->reflect_cls->getName());
	    	$this->assign('method', $method);
	    	$this->display('gen/method');
    	}catch(ReflectionException $e){
			throw_exception($e);
		}
 	}
 
    
    
    /**
     * 读取目录下的class文件
     */
    public function readDirect($dir, $class_arr = ''){
    	$dir_handle = opendir($dir);
    	while($file = readdir($dir_handle)){
    		if($file == '.' or $file == '..' or (substr($file,0,1) == '.')){
    			continue;
    		}
    		$path = $dir.'\\'.$file;
    		if(is_dir($path)){
    			//chdir($path);
    			$file_arr = $this->readDirect($path);
    			if(is_array($file_arr)){
    				$class_arr[$file] = $file_arr;
    			}
    		}else if(($lower_class = strtolower(substr($file, -10))) == '.class.php' || $file == $this->funs_file){
    			$this->auto_load_dir = $dir; // 设置自动加载目录
    			$class_name = substr($file, 0, -10);
    			/*
    			echo $dir.'<br/>';
    			echo $this->auto_load_dir.'\\';
    			echo $class_name.'<br/><br/>';
    			*/
    			if(!class_exists($class_name)){
    			     eval("\$temp_cls = {$class_name}->__toString();");
    			}
    			$class_arr[] = $path;
    		}
    	}
    	closedir($dir_handle);
    	return $class_arr;
    }
    
    
    /**
     * 自动加载文件
     */
    public function classAutoLoader($class_name){
    	$class_dir = $this->auto_load_dir;
    	$file_path = realpath("{$class_dir}/{$class_name}.class.php");
    	if(file_exists($file_path)){
    		return (include_once $file_path);	
    	}else{
    		throw_exception($class_dir);
    		return false;
    	}
    }
   
    
    /**
     * 创建 HELP HHC文件
     */
    protected function createHHC($class_arr){
    	
    	function setObjectParam($name, $value){
			$object = "<LI><OBJECT type=\"text/sitemap\"><param name=\"Name\" value=\"{$name}\"><param name=\"Local\" value=\"{$value}\"></OBJECT></LI>";
			return $object;
    	}
	
    	$GLOBALS['curObj'] = $this;
    	function createTree($class_arr){
    		global $curObj;
    		$tree = '<UL>';
    		$size = count($class_arr);
    		foreach($class_arr as $k=>$class){
	    		if(is_array($class)){
	    			$tree .= setObjectParam($k, '');
	    			$tree .= createTree($class);
	    		}else{
	    			$class_name = '';
	    			$value = $curObj->getDirname($class, $class_name);
	    			$file_name = $curObj->getFileName($class_name);
	    			$tree .= setObjectParam($class_name, $value.'/'.$file_name);
	    		}
	    	}
	    	return $tree.'</UL>';
    	}
    	
    	$tree = createTree($class_arr);
    	$this->assign('tree', $tree);
    	$content = $this->fetch('hhc');
    	return $this->createFile($this->manual_dir.$this->contents_file, $content);
    }
    
    
    /**
     * 创建 HELP HHP文件
     */
    protected function createHHP(){
    	$this->assign('compiled_file', $this->compiled_file);
    	$this->assign('contents_file', $this->contents_file);
    	$this->assign('hhk_file', $this->hhk_file);
    	$this->assign('default_topic', $this->default_topic);
    	$this->assign('files', $_ENV['files_indexs']);
    	$content = $this->fetch('hhp');
    	return $this->createFile($this->manual_dir.$this->hhp_file, $content);
    }
    
    
    /**
     * 创建CHM索引文件
     */
    protected function createHHK(){
    	return $this->createFile($this->manual_dir.$this->hhk_file, '');
    }
    
    
   
   /**
    * 创建关联文件
    */
    protected function createHTML($class_arr){
    	if(is_array($class_arr)){
    		$dir_indexs = array();
    		foreach($class_arr as $class_arr2){
    			$size = count($class_arr2);
    			$file_indexs = array();
				foreach($class_arr2 as $k=>$class){
					$class_name = '';
					$dir_name = $this->getDirname($class, $class_name);
					$dir = $this->manual_dir.$dir_name;
					if(!file_exists($dir)){
						$dir_indexs[] = array('name'=>$dir_name, 'link'=>"{$dir_name}/index.html");
						mkdir($dir);
					}
					
					$basename = $this->getFileName($class_name, 'source');
					$file_name = "{$dir}/$basename";
					$this->assign('title', $dir_name.$class_name);
					$this->assign('content', highlight_file($class, true));
					
					// 生成源码文件
					if($this->createFile($file_name, $this->fetch('highlight')) > 0){
						$class_link = $dir_name.'/'.$this->getFileName($class_name, 'class');
						$file_indexs[] = array('name'=>$class_name, 'link'=>basename($class_link));
						$_ENV['files_indexs'][] = $class_link;
					}
	
					// 生成对应目录的索引文件列表
					if(($k+1) == $size){
						$this->createIndexsFile($dir.'/index.html', $dir_name, $file_indexs);
					}
					$_ENV['files_indexs'][] = "{$dir_name}/{$basename}";
				}
    		}
    	
    		// 生成目录索引文件
    		$this->createIndexsFile($this->manual_dir.'index.html', basename($this->project_dir), $dir_indexs);
    	}
    	
    	$copy_dir = "{$this->manual_dir}css/";
    	if(!is_dir($copy_dir)){
    		mkdir($copy_dir);
    	}
    	copy('css/style.css', "{$copy_dir}style.css");
    }
    
    
   	/**
   	 * 获得文件名称
   	 */
    public function getFileName($name, $type = 'class'){
    	switch($type){
    		case 'class':
    			$file_name = "__class__info__{$name}.html";
    			break;
    		case 'method':
    			$file_name = "__method__info__{$name}.html";
    			break;
    		case 'source':
    			$file_name = "__class__source__{$name}.html";
    			break;
    	}
    	return $file_name;
    }
    
    
    
   
   
   
    /**
     * 生成html文件
     */
    protected function getFileList($class_arr, &$temp_class_arr=''){
    	if(is_array($class_arr)){
    		foreach($class_arr as $k=>$class){
    			if(is_array($class)){
    				$this->getFileList($class, $temp_class_arr);
    			}else{
    				$dir_name = $this->getDirname($class);
					if(!isset($temp_class_arr[$dir_name])){
						$temp_class_arr[$dir_name] = array();
					}
					$temp_class_arr[$dir_name][] = $class;
    			}
    		}
    	}
    	return $temp_class_arr;
    }
    
    
    /**
     * 获取class目录名称
     */
    public function getDirname($class, &$class_name=''){
    	$dir = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname($class));
	   	$dir_name = ltrim(str_replace('\\', '.', $dir), '.');
	   	
	   	$basename = basename($class);
		$basename_arr = explode('.', $basename);
		$class_name = $basename_arr[0];
	   	return $dir_name;
    }
    
    
    /**
     * 创建索引文件
     */
    protected function createIndexsFile($path, $title, $indexs){
    	$this->assign('title', $title);
		$this->assign('indexs', $indexs);
		$context = $this->fetch('class');
		return $this->createFile($path, $context);
    }
    
    
    /**
     * 创建文件
     */
    protected function createFile($file, $data){
    	return file_put_contents($file, $data);
    }
    
    
}
?>