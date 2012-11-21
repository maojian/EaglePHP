<?php

/**
 * 路由器应用管理
 * @author maojianlw@139.com
 * @since 2012-08-03
 */

class Application {
    
    public static function init()
    {
        Behavior::checkRefresh();
        if (URL_MODEL == 1 && !(__CLI__)) 
        {
			self::pathinfoURL();
		} 
		else 
		{
			self::usuallyURL();
		}
    }
 
	/**
	 * 执行控制器的方法
	 */
	public function run() 
	{
		$controller = CONTROLLER_NAME . 'Controller';
		$action = ACTION_NAME . 'Action';
		
		$controller_obj = MagicFactory :: getInstance($controller);
		$parent_name = array_shift(class_parents($controller));
		$parent_obj = new $parent_name ();

		$method = '_initialize';
		if (method_exists($parent_obj, $method))
			$parent_obj-> $method (); // 执行父类的初始化方法
		$controller_obj-> $action ();
	}

	/**
	 * 定义常量
	 * 
	 * @param string $controller
	 * @param string $action
	 */
	private static function defineConst($controller, $action) 
	{
	    $scriptName = $_SERVER['SCRIPT_NAME'];
	    $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
	    $scriptFileName = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
	    $appName = strtolower(APP_NAME);
	    $projectName = $project = $root = null;
	    
	    // 域名指向环境
	    if(stripos($documentRoot, '/public') !== false)
	    {
	        $projectName = '';
	        $project = $pub = '/';
	    }
	    else  // 本地配置环境
	    {
	        $baseName = basename($scriptName);
	        $scriptNameArr = array_filter(explode('/', str_replace($baseName, '', $scriptName)));
	        $projectName = isset($scriptNameArr[1]) ? $scriptNameArr[1] : '';
	        $pos = stripos($scriptName, '/public/');
	        if($pos !== false)
	        {
	            $project = substr($scriptName, 0, $pos+1);
	        }
	        else
	        {
	            $pos2 = stripos($scriptName, $baseName);
	            $project = substr($scriptName, 0, $pos2);
	        }
	        $pub = $project.'Public/';
	    }
	    
	    // 开启伪静态
	    if(getCfgVar('cfg_open_rewrite'))
	    {
	        $root = str_replace(array('/Public', '/index.php'), array('', ''), $scriptName).'/';
	    }
	    else
	    {
	        $root = $scriptName.'/';
	    }
	    
        define('PROJECT_NAME', $projectName);
        define('__PROJECT__', $project);
        define('__ROOT__', $root);
        define('__PUB__', $pub);
        define('__APP_RESOURCE__', $pub.$appName.'/');
        define('__SHARE__',  __PUB__.'share/');
        define('__UPLOAD__', __SHARE__.'upload/');
	    
		// 当前控制器名称
		define('CONTROLLER_NAME', (empty ($controller) ? 'Index' : ucfirst($controller)));
		// 当前操作名称
		define('ACTION_NAME', (empty ($action) ? 'index' : $action));
		
		if(URL_MODEL == 1) // pathinfo模式 
		{
    		// 当前模块地址
    		define('__URL__',  __ROOT__. CONTROLLER_NAME . '/');
    		// 当前操作地址 
    		define('__ACTION__', __URL__ . ACTION_NAME . '/');
		}
		else  // 普通参数url模式
		{
		    // 当前模块地址
    		define('__URL__',  rtrim(__ROOT__, '/').'?c='. CONTROLLER_NAME);
    		// 当前操作地址 
    		define('__ACTION__', __URL__ .'&a='. ACTION_NAME);
		}
        
        /*
		echo '<pre>'; 
        print_r($_SERVER);
        $a = get_defined_constants(true);
        print_r($a['user']);
        echo '</pre>';
        
        exit;
        */
        
      
	}

	/**
	 * 普通URL模式
	 */
	private static function usuallyURL() 
	{
		// 命令行模式支持
		if (__CLI__) 
		{
			$controller = $_SERVER['argv'][1];
			$action = $_SERVER['argv'][2];
		} 
		else 
		{
			$controller = $_REQUEST['c'];
			$action = $_REQUEST['a'];
		}
		self::defineConst($controller, $action);
	}

	/**
	 * pathinfo URL模式
	 */
	private static function pathinfoURL() 
	{
	    $controller = $action = null;
		if (isset($_SERVER['PATH_INFO'])) 
		{
		    $pathinfo = $_SERVER['PATH_INFO'];
		    // 此处解决Nginx上pathinfo对自带的script name进行替换
		    if(stripos($pathinfo, '.php') !== false)
		    {
		        $pathinfo = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $pathinfo);
		    }
			$pathinfoArr = explode('/', rtrim($pathinfo, '/'));
			$controller = isset($pathinfoArr[1]) ? $pathinfoArr[1] : '';
			$action = isset($pathinfoArr[2]) ? $pathinfoArr[2] : '';
			$len = count($pathinfoArr);
			for ($i = 3; $i < $len; $i++)
			{
			    $_GET[$pathinfoArr[$i]] = $pathinfoArr[++$i];
			}
			$_GET['c'] = $controller;
		    $_GET['a'] = $action;
			$_REQUEST = array_merge($_REQUEST, $_GET);
		}
		self::defineConst($controller, $action);
	}

}
