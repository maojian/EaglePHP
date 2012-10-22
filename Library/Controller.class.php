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
	public function getData($type, $key=''){
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
	

}