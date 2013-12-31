<?php
class Router
{

    protected static $isRewrite = null;
    
    
    /**
     * URL解析
     * 
     * @param string $url
     * @param string $model
     * @return string
     */
    public static function url($url='', $model=null)
    {
        $model = $model ? $model : URL_MODEL;
        switch ($model)
        {
            case 1:
                return $url;
                break;
            case 2:
                return self::pathinfoUrl($url);
                break;
            case 3:
                return self::htmlUrl($url);
                break;
        }
    }
    
    /**
     * URL pathifo 模式
     * 
     * @param string $url
     * @return $url
     */
    public static function pathinfoUrl($url)
    {
        $queryArr = explode('?', $url);
        if(isset($queryArr[1]))
        {
            $url = $queryArr[1];
            $url = str_replace(array('&amp;', '+'), array('&', '%2B'), $url);
            $urlArr = $urlArr2 = array();
            parse_str($url, $urlArr);
            if(isset($urlArr['c']))
            {
                $urlArr2[] = $urlArr['c'];
                unset($urlArr['c']);
            }
            /*
            else
            {
                $urlArr2[] = 'index';
            }*/
            if(isset($urlArr['a']))
            {
                $urlArr2[] = $urlArr['a'];
                unset($urlArr['a']);
            }
            /*
            else
            {
                $urlArr2[] = 'index';
            }*/
            if(is_array($urlArr))
            {
                foreach ($urlArr as $k=>$v) if(!empty($v)) $urlArr2[] = $k.'/'.$v;
                $url = implode('/', $urlArr2);
            }
            $url = rtrim($queryArr[0],'/').'/'.$url; 
        }
        return $url;
    }
    
    
	/**
	 * pathinfo URL模式
	 * 
	 * @return void
	 */
	public static function pathinfoParse() 
	{
	    //if(HttpRequest::getServer('QUERY_STRING')) return self::ordinaryParse();
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
			    $num = ++$i;
			    $_GET[$pathinfoArr[$i-1]] = isset($pathinfoArr[$num]) ? $pathinfoArr[$num] : '';
			}
			$_GET['c'] = $controller;
		    $_GET['a'] = $action;
			$_REQUEST = array_merge($_REQUEST, $_GET);
		}
		self::defineConst($controller, $action);
	}
    
	
    /**
     * url html模式
     * 
     * @param string $url
     * @return string
     */
    public static function htmlUrl($url)
    {
        $queryArr = explode('?', $url);
        if(isset($queryArr[1]))
        {
            $url = $queryArr[1];
            $url = str_replace(array('&amp;', '+'), array('&', '%2B'), $url);
            $urlArr = $urlArr2 = array();
            parse_str($url, $urlArr);
            if(isset($urlArr['c']))
            {
                $urlArr2[] = $urlArr['c'];
                unset($urlArr['c']);
            }
            if(isset($urlArr['a']))
            {
                $urlArr2[] = $urlArr['a'];
                unset($urlArr['a']);
            }
            if(is_array($urlArr))
            {
                foreach ($urlArr as $k=>$v) if(!empty($v)) $urlArr2[] = $k.'-'.$v;
                $url = count($urlArr2) ? implode('/', $urlArr2).'.html' : '';
            }
            $url = rtrim($queryArr[0],'/').'/'.$url; 
        }
        return $url;
    }
    
    
    /**
     * html url模式解析
     * 
     * @return void
     */
    public static function htmlParse()
	{
	    $url = HttpRequest::getServer('PATH_INFO');
	    $url = str_replace(array('.html'), array(''), $url);
	    $url = trim($url, '/');
	    $urlArr = array_filter(explode('/', $url));
	    $controller = $action = null;
		if($queryStr = HttpRequest::getServer('QUERY_STRING'))
		{
			parse_str($queryStr, $tempArr);
			$_REQUEST = array_merge($_REQUEST, $tempArr);
		}
	    if(is_array($urlArr) && count($urlArr))
	    {
	        foreach ($urlArr as $k=>$v)
	        {
	            if($pos = strpos($v, '-'))
	            {
	                $key = substr($v, 0, $pos);
	                $val = substr($v, $pos+1);
	                $_GET[$key] = $val;
	            }
	            elseif($k == 0) $_GET['c'] = $controller = $v;
	            elseif($k == 1) $_GET['a'] = $action = $v;
	        }
	        $_REQUEST = array_merge($_POST, $_GET);
	    }
	    self::defineConst($controller, $action);
	}
    
   
	/**
	 * 普通URL模式
	 * 
	 * @return void
	 */
	public static function ordinaryParse() 
	{
		$controller = HttpRequest::getRequest('c');
	    $action = HttpRequest::getRequest('a');
		self::defineConst($controller, $action);
	}
	
	
	/**
	 * 命令行模式支持
	 * 
	 * @return void
	 */
	public static function cliParse()
	{
	    $controller = $_SERVER['argv'][1];
		$action = $_SERVER['argv'][2];
		self::defineConst($controller, $action);
	}

    
    /**
	 * 定义常量
	 * 
	 * @param string $controller
	 * @param string $action
	 * 
	 * @return void
	 */
	public static function defineConst($controller, $action) 
	{
	    self::$isRewrite = getCfgVar('cfg_open_rewrite');
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
	    if(self::$isRewrite)
	    {
	        $root = str_replace(array('/Public', '/index.php'), array('', ''), $scriptName).'/';
	    }
	    else
	    {
	        $root = $scriptName;
	    }
	    
        define('__PROJECT_NAME__', $projectName);
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
		
		// 当前模块地址
        define('__URL__',  __ROOT__.'?c='. CONTROLLER_NAME);
    	// 当前操作地址 
    	define('__ACTION__', __URL__ .'&a='. ACTION_NAME);
    	
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
     * Smarty模版中编译前的URL正则替换回调函数
     * 
     * @param array $arr
     * @return string
     */
    public static function tplReplace($arr)
    {
        $text = $arr[0];
        $data = array(
    				'$smarty.const.__URL__' => __URL__, 
    				'$smarty.const.__ACTION__' => __ACTION__, 
    				'$smarty.const.__ROOT__' => __ROOT__, 
    				'$smarty.const.__PUB__' => __PUB__, 
    				'$smarty.const.__APP_RESOURCE__' => __APP_RESOURCE__,
                    '$smarty.const.__PROJECT__' => __PROJECT__,
                    '$smarty.const.__UPLOAD__' => __UPLOAD__,
                    '$smarty.const.__PROJECT_NAME__' => __PROJECT_NAME__,
                    '$smarty.const.__SHARE__' => __SHARE__,
                );
        if(strpos($text, '|url}}') !== false)
        {
            $text = str_replace(array_keys($data), array_values($data), $text);
            $text = preg_replace_callback('#\{\{(.*?)\|url\}\}#i', array(__CLASS__, 'urlReplace'), $text);
        }
        else
        {
            foreach ($data as $k=>$v)
            {
                if(strpos($text, $k) !== false)
                {
                    $text = str_replace($k, $v, $text);
                    $text = ltrim($text, '{{');
                    $text = rtrim($text, '}}');
                }
            }
        }
        return $text;
    }
    
    
    public static function urlReplace($arr)
    {
        $text = $arr[1];
        $isIndex = false;
        $search = '/index.php';
        if(($isIndex = strpos($text, $search)) !== false)
        {
            $sArr = explode($search, $text);
            $search = $sArr[0].$search;
            $text = str_replace($search, '', $text);
        }
        if(strpos($text, '=') !== false)
        {
            $qArr = explode('?', $text);
            $paramsArr = explode('&', $qArr[1]);
            foreach ($paramsArr as &$v)
            {
                if(strpos($v, '.') !== false || strpos($v, '[') !== false || strpos($v, '->') !== false)
                {
                    if(strpos($v, '?') !== false)
                    {
                        $v = '`'.str_replace('?', '`?', $v);
                    }
                    else
                    {
                        $v = (strpos($v, '=') !== false) ? str_replace('=', '=`', $v).'`' : "`{$v}`";
                    }
                } 
            }
            $text = $qArr[0].'?'.implode('&', $paramsArr);
        }
        if($isIndex !== false) $text = $search.$text;
        $text = self::url($text);
        $text = '{{"'.$text.'"}}';
        return $text;
    }
    
}