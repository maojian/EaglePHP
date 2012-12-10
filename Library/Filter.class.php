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
	    $val = Security::xssClean($val);
		$val = (!get_magic_quotes_gpc()) ? addcslashes($val, "\000\n\r\\'\"\032") : $val;
        $val = htmlspecialchars($val);
		return $val;
	}
	

    /**
	 * 
	 * XSS攻击过滤
	 * 
	 * @param mixed $val 需要过滤的值
	 * @return mixed
	 */
    public static function removeXSS($val) {
        
        if(empty($val)) return '';
	    
	    if (is_array($val))
		{
			while (list($key) = each($val))
			{
				$val[$key] = self::removeXSS($val[$key]);
			}
			return $val;
		}
        
	   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	   // this prevents some character re-spacing such as <java\0script>
	   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
	
	   // straight replacements, the user should never need these since they're normal characters
	   // this prevents like <IMG SRC=@avascript:alert('XSS')>
	   $search = 'abcdefghijklmnopqrstuvwxyz';
	   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	   $search .= '1234567890!@#$%^&*()';
	   $search .= '~`";:?+/={}[]-_|\'\\';
	   for ($i = 0; $i < strlen($search); $i++) {
		  // ;? matches the ;, which is optional
		  // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
	
		  // @ @ search for the hex values
		  $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		  // @ @ 0{0,7} matches '0' zero to seven times
		  $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
	   }
	
	   // now the only remaining whitespace attacks are \t, \n, and \r
	   $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	   $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	   $ra = array_merge($ra1, $ra2);
	
	   $found = true; // keep replacing as long as the previous round replaced something
	   while ($found == true) {
		  $val_before = $val;
		  for ($i = 0; $i < sizeof($ra); $i++) {
			 $pattern = '/';
			 for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
				   $pattern .= '(';
				   $pattern .= '(&#[xX]0{0,8}([9ab]);)';
				   $pattern .= '|';
				   $pattern .= '|(&#0{0,8}([9|10|13]);)';
				   $pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			 }
			 $pattern .= '/i';
			 $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
			 $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			 if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			 }
		  }
	   }
	   return $val;
	}
    
}