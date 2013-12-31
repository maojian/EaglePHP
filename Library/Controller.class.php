<?php
/**
 * 控制器基类
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 */

class Controller extends Delegate
{   
    
    /**
     * 当未设置get、post、request、cookie、session、env、server中的值时默认值为_NO_CHANGE_VAL_
     * 
     * @var string
     */
    const _NO_CHANGE_VAL_ = '/*#EAGLE#*/';
    
    
    /**
     * 初始化控制器基类
     * 
     * @return void
     */
	public static function init() 
	{
		self::addObject(new View());
		//self::addObject(new Model());
	}

	/**
	 * 路径跳转
	 * 
	 * @param string $url
	 * @param int $time
	 * @param string $message
	 * @param bool $isConvert
	 * @return void
	 */
	public function redirect($url, $time=0, $message='', $isConvert=true) 
	{
		if (empty($url)) $url = CONTROLLER_NAME;
		redirect(__ROOT__ . $url, $time, $message, $isConvert);
	}
	

	/**
	 * 200=>操作成功,300=>操作失败,301=>会话超时
	 * 
	 * @param int $statusCode
	 * @param string $message
	 * @param string $navTabId
	 * @param string $callbackType
	 * @param string $forwardUrl
	 * @param array $data
	 * @return void
	 */
	function ajaxReturn($statusCode, $message, $navTabId = '', $callbackType = '', $forwardUrl = '', $data='') 
	{
		if($statusCode == 200 && $navTabId == null)
		{
			$navTabId = CONTROLLER_NAME;
		}
		$jsonData = array (
        			'statusCode' => $statusCode,
        			'message' => $message,
        			'navTabId' => $navTabId,
        			'callbackType' => $callbackType,
        			'forwardUrl' => $forwardUrl,
        			'data' => $data
        		);
		exit (json_encode($jsonData));
	}
	
	
	/**
	 * 获取配置文件数据
	 * 
	 * @param string $type
	 * @param int $key
	 * @return array | string
	 */
	public function getData($type, $key='')
	{
	    $data = config('data');
	    if(isset($data[$type]))
	    {
	        if($key && isset($data[$type][$key]))
	        {
	           return $data[$type][$key];
	        }
	        return $data[$type];
	    }
	    return false;
	}
	
	
	/**
	 * 从get方式中获取或者设置数据
	 * 
	 * @param mixed $name
	 * @param string $value
	 * @param bool $cleanXss
	 * @return mixed
	 */
	public function get($name=null, $value=self::_NO_CHANGE_VAL_, $cleanXss=true)
	{
	    if($value == self::_NO_CHANGE_VAL_) return HttpRequest::getGet($name, null, $cleanXss);
	    return HttpRequest::setGet($name, $value);
	}
	
	
	/**
	 * 从post方式中获取或设置数据
	 * 
	 * @param mixed $name
	 * @param string $value
	 * @param bool $cleanXss
	 * @return mixed
	 */
	public function post($name=null, $value=self::_NO_CHANGE_VAL_, $cleanXss=true)
	{
	    if($value == self::_NO_CHANGE_VAL_) return HttpRequest::getPost($name, null, $cleanXss);
	    return HttpRequest::setPost($name, $value);
	}
	
	
	/**
	 * 从request方式中获取或设置数据
	 * 
	 * @param mixed $name
	 * @param string $val
	 * @param bool $cleanXss
	 * @return mixed
	 */
	public function request($name=null, $value=self::_NO_CHANGE_VAL_, $cleanXss=true)
	{
	    if($value == self::_NO_CHANGE_VAL_) return HttpRequest::getRequest($name, null, $cleanXss);
	    return HttpRequest::setRequest($name, $value);
	}
	
	
	/**
	 * 从cookie方式中获取或设置数据
	 * 
	 * @param mixed $name
	 * @param string $val
	 * @param bool $cleanXss
	 * @return mixed
	 */
	public function cookie($name=null, $value=self::_NO_CHANGE_VAL_, $cleanXss=true)
	{
	    if($value == self::_NO_CHANGE_VAL_) return HttpRequest::getCookie($name, null, $cleanXss);
	    return HttpRequest::setCookie($name, $value);
	}
	
	
	/**
	 * 获取上传的文件数组
	 * @param string $name
	 * @return array
	 */
	public function file($name=null)
	{
	    return HttpRequest::getFile($name);
	}
	
	
	/**
	 * 从session方式中获取或设置数据
	 * 
	 * @param mixed $name
	 * @param string $value
	 * @return mixed
	 */
	public function session($name=null, $value=self::_NO_CHANGE_VAL_)
	{
	    if($value == self::_NO_CHANGE_VAL_) return HttpRequest::getSession($name);
	    return HttpRequest::setSession($name, $value);
	}
	
	
	/**
	 * 从server方式中获取或设置数据
	 * 
	 * @param mixed $name
	 * @param string $value
	 * @return mixed
	 */
	public function server($name=null, $value=self::_NO_CHANGE_VAL_)
	{
	    if($value == self::_NO_CHANGE_VAL_) return HttpRequest::getServer($name);
	    return HttpRequest::setServer($name, $value);
	}
	
	
	/**
	 * 从env方式中获取或设置数据
	 * 
	 * @param mixed $name
	 * @param string $value
	 * @return mixed
	 */
	public function env($name=null, $value=self::_NO_CHANGE_VAL_)
	{
	    if($value == self::_NO_CHANGE_VAL_) return HttpRequest::getEnv($name);
	    return HttpRequest::setEnv($name, $value); 
	}
	
	
	/**
	 * 是否是post方式提交
	 * 
	 * @return bool
	 */
	public function isPost()
	{
	    return HttpRequest::isPost();auto_charset();
	    
	}
	
	
	/**
	 * 是否是get方式提交
	 * 
	 * @return bool
	 */
	public function isGet()
	{
	    return HttpRequest::isGet();
	}
	
	
}