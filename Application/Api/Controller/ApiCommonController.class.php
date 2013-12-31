<?php
class ApiCommonController extends Controller
{
	
	public static $api_codes = null;
	
	/**
	 * 初始化
	 */
	public function _initialize()
	{
		self::$api_codes = config('api_error_code');
	}
	
    /**
     * json格式返回
     */
    public function jsonReturn($data)
    {
    	exit(json_encode($data));
    }
    
    /**
     * xml格式返回
     */
    public function xmlReturn($data)
    {
    	$data = array('root'=>$data);
    	HttpResponse::sendContentHeader('Content-Type:text/xml;charset=utf-8');
    	exit(XML::XML_serialize($data));
    }
    
    /**
     * 按照某种格式返回数据
     */
    public function formatReturn($code, $list='')
    {
    	$message = self::$api_codes[$code];
    	$data = array('statusCode'=>$code, 'message'=>$message);
    	if(!empty($list)) $data['data'] = $list;
    	($this->request('format') === 'xml') ? $this->xmlReturn($data) : $this->jsonReturn($data);
    }
       
}