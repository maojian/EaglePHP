<?php


/**
 * 过滤请求的参数
 * 
 * @author maojianlw@139.com
 * @since 2.3 - 2012-12-1
 */

class Filter 
{


	/**
	 * 过滤全部用户输入字符
	 * 
	 * @return void
	 */
	public static function init() 
	{
		$request_method = array ('_GET', '_POST', '_COOKIE');
		foreach ($request_method as $k => $request)
		{
			foreach ($GLOBALS[$request] as $_k => & $_v)
			{
				$_v = self::runMagicQuote($_v);
			}
		}
	}

	
	/**
	 * 特殊字符转义
	 * 
	 * @param mixed $vars
	 */
	public static function runMagicQuote(& $vars) 
	{
	    if(empty($vars))
	    {
	        return $vars;    
	    }
	    else if (is_array($vars)) 
		{
			foreach ($vars as $_k => $_v) 
			{
				$vars[$_k] = self::runMagicQuote($_v);
			}
		} 
		else 
		{
			$vars = self::strReplace($vars);
		}
		return $vars;
	}
	

	/**
	 * 功能:特殊字符替换
	 * 
	 * @param string $val 要过滤的字符串
	 * @return string 返回过滤后的字符串
	 */
	public static function strReplace($val) 
	{
		// 替换全角空格，并去掉前后空格。 
		$val = trim(str_replace('　', ' ', $val));
	    if(!empty($val))
	    {
	        $val = Security::xssClean($val);
		    $val = htmlspecialchars(addslashes($val));
	    }
        return $val;
	}
	

    
    
}