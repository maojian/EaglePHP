<?php
class Controller extends Delegate {
	
	public static function init() {
		self::addObject(new View());
		//self::addObject(new Model());
	}

	/**
	 * 路径跳转
	 */
	public function redirect($url, $time=0, $message='') {
		if (empty ($url))
			$url = CONTROLLER_NAME;
		redirect(__ROOT__ . $url, $time, $message);
	}

	/**
	 * {"statusCode":"200", "message":"操作成功"}
	 * {"statusCode":"300", "message":"操作失败"}
	 * {"statusCode":"301", "message":"会话超时"}
	 * 
	 */
	function ajaxReturn($statusCode, $message, $navTabId = '', $callbackType = '', $forwardUrl = '', $data='') {
		if($statusCode == 200 && $navTabId == null){
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
	 * @param string $type
	 * @param int $key
	 * @return array | string
	 */
	public function getData($type, $key='')
	{
	    $data = C('data');
	    if(array_key_exists($type, $data)){
	        if($key && array_key_exists($key, $data[$type])){
	           return $data[$type][$key];
	        }
	        return $data[$type];
	    }else{
	        return false;
	    }
	}
	
	
	/**
	 * 获取客户端提交的参数
	 * @param string | array $name 参数名称或者多个参数名称组成的数组格式
	 * @param string $type 请求方法
	 * @param string $defaultVal 默认值 
	 * @param string $callback 回调函数
	 * @return mixed
	 */
	public function getParameter($name, $type = null, $defaultVal=null, $callback = null)
	{
	    if(is_array($name))
	    {
	        $data = array();
	        foreach ($name as $key => $val)
	        {
	            $data[$key] = $this->getParameter($val, $type, $callback);
	        }
	        return $data;
	    }else{
	        $value = null;
    	    switch (strtoupper($type))
    	    {
    	        case 'GET':
    	            $value = HttpRequest::getGet($name, $defaultVal);
    	            break;
    	        case 'POST':
    	            $value = HttpRequest::getPost($name, $defaultVal);
    	            break;
    	        case 'COOKIE':
    	            $value = HttpRequest::getCookie($name, $defaultVal);
    	            break;
    	        default:
    	            $value = HttpRequest::getRequest($name, $defaultVal);
    	            break;
    	    }
    	    return $callback ? array($value, call_user_func_array($callback, array($value))) : $value;
	    }
	}
	

}