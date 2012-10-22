<?php


/**
 * 过滤所有请求的参数
 * 
 * @author maojianlw@139.com
 * @since 1.6 - 2011-6-15
 */

class Filter {

	/**
	 * 过滤全部用户输入字符
	 */
	public static function init() 
	{
		$request_method = array (
			'_GET',
			'_POST',
		    '_COOKIE'
		);
		foreach ($request_method as $k => $request)
			foreach ($GLOBALS[$request] as $_k => & $_v)
				$_v = self::runMagicQuote($_v);
	}

	/**
	 * 特殊字符转义
	 */
	public static function runMagicQuote(& $vars) 
	{
		if (!get_magic_quotes_gpc()) 
		{
			if (is_array($vars)) 
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
		}
		return $vars;
	}

	/**
	 * 功能:特殊字符替换
	 * 
	 * @param string $document 要过滤的字符串
	 * @return string 返回过滤后的字符串
	 */
	public static function strReplace($document) {
		$search = array (
			/*'@<script[^>]*?>.*?</script>@si', // Strip out javascript
	'@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags*/
			'@([\r\n])[\s]+@', // Strip out white space
			'@&(quot|#34);@i', // Replace HTML entities
			'@&(amp|#38);@i',
			'@&(lt|#60);@i',
			'@&(gt|#62);@i',
			'@&(nbsp|#160);@i',
			'@&(iexcl|#161);@i',
			'@&(cent|#162);@i',
			'@&(pound|#163);@i',
			'@&(copy|#169);@i',
			'@&#(\d+);@e'
		);
		$replace = array (
			//'',
			//'',
			'\1',
			'"',
			'&',
			'<',
			'>',
			' ',
			chr(161),
			chr(162),
			chr(163),
			chr(169),
			'chr(\1)'
		);

		$text = preg_replace($search, $replace, trim($document));
		return addslashes(htmlspecialchars($text));
	}

}
