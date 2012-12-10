<?php
/**
 * Http 响应类
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 * @since 2012-11-28
 */

class HttpResponse
{
    
    
    /**
     * 发送HTTP头信息
     * 
     * @param int $code 响应码
     * @param string $text 响应文本
     * @param bool $isExit 是否退出
     * @return void
     */
    public static function sendHeader($code = 200, $text = '', $isExit = true)
    {
        $codeArr = self::getHttpCodes();
        if(isset($codeArr[$code]) && $text == '')
        {
            $text = $codeArr[$code];
        }
        if($text == '') throw_exception('Status text is empty.');
        $protocol = HttpRequest::getProtocol();
        
        if(substr(php_sapi_name(), 0, 3) == 'cgi')  // 当CGI模式时使用
        {
            header("Status: {$code} {$text}", true);
        }
        else // 当mod_php时使用
        {
            header("{$protocol} {$code} {$text}", true, $code);
        }
        if($isExit) exit;
    }
    
    
    /**
     * 获取所有HTTP响应头信息
     * 
     * @return array
     */
    public static function getApaceResponseHeader()
    {
        if(function_exists('apache_response_headers'))
        {
            return apache_response_headers();
        }
        return false;
    }
    
    
    /**
     * 是否已经发送头信息
     * 
     * @return bool
     */
    public static function isSendHeader()
    {
        return headers_sent();
    }
    
    
    /**
     * 发送内容头信息 
     * 类似：Content-Type: text/html; charset=utf-8
     * 
     * @param string $content 发送内容
     */
    public static function sendContentHeader($content)
    {
        header($content);
    }
    
    
    /**
     * 获取Http响应状态码
     * 
     * @param int $code
     * @return array | text
     */
    public static function getHttpCodes($code = null)
    {
        $codes = array(
                    100 => 'Continue',
                    101 => 'Switching Protocols',
                    
    				200	=> 'OK',
    				201	=> 'Created',
    				202	=> 'Accepted',
    				203	=> 'Non-Authoritative Information',
    				204	=> 'No Content',
    				205	=> 'Reset Content',
    				206	=> 'Partial Content',
    
    				300	=> 'Multiple Choices',
    				301	=> 'Moved Permanently',
    				302	=> 'Found',
    				304	=> 'Not Modified',
    				305	=> 'Use Proxy',
    				307	=> 'Temporary Redirect',
    
    				400	=> 'Bad Request',
    				401	=> 'Unauthorized',
    				403	=> 'Forbidden',
    				404	=> 'Not Found',
    				405	=> 'Method Not Allowed',
    				406	=> 'Not Acceptable',
    				407	=> 'Proxy Authentication Required',
    				408	=> 'Request Timeout',
    				409	=> 'Conflict',
    				410	=> 'Gone',
    				411	=> 'Length Required',
    				412	=> 'Precondition Failed',
    				413	=> 'Request Entity Too Large',
    				414	=> 'Request-URI Too Long',
    				415	=> 'Unsupported Media Type',
    				416	=> 'Requested Range Not Satisfiable',
    				417	=> 'Expectation Failed',
    
    				500	=> 'Internal Server Error',
    				501	=> 'Not Implemented',
    				502	=> 'Bad Gateway',
    				503	=> 'Service Unavailable',
    				504	=> 'Gateway Timeout',
    				505	=> 'HTTP Version Not Supported'
    			);
    	if($code && isset($codes[$code])) return $codes[$code];
    	return $codes;
    }
    
    
}