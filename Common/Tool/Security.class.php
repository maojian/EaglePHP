<?php
/**
 * 安全类
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 * @since 2012-11-28
 */

class Security
{
    
    /**
     * xss hash
     * 
     * @var string
     */
    protected static $_xss_hash = '';
    
    
    /**
     * 不允许的字符串
     * 
     * @var array
     */
    protected static $_never_allowed_str = array(
					'document.cookie'	=> '[removed]',
					'document.write'	=> '[removed]',
					'.parentNode'		=> '[removed]',
					'.innerHTML'		=> '[removed]',
					'window.location'	=> '[removed]',
					'-moz-binding'		=> '[removed]',
					'<!--'				=> '&lt;!--',
					'-->'				=> '--&gt;',
					'<![CDATA['			=> '&lt;![CDATA[',
					'<comment>'			=> '&lt;comment&gt;'
	);
	
	
	/**
	 * 不允许正则表达式替换
	 *
	 * @var array
	 */
	protected static $_never_allowed_regex = array(
					"javascript\s*:"			=> '[removed]',
					"expression\s*(\(|&\#40;)"	=> '[removed]', // CSS and IE
					"vbscript\s*:"				=> '[removed]', // IE, surprise!
					"Redirect\s+302"			=> '[removed]'
	);
	
	
	/**
	 * 随机散列保护URL
	 *
	 * @return string
	 */
	public static function xssHash()
	{
		if (self::$_xss_hash == '')
		{
			mt_srand();
			self::$_xss_hash = md5(Date::getTimeStamp() + mt_rand(0, 1999999999));
		}

		return self::$_xss_hash;
	}
    
    
	/**
	 * 验证URL实体
	 *
	 * @param 	string
	 * @return 	string
	 */
	public static function validateEntities($str)
	{
		/*
		 * Protect GET variables in URLs
		 */

		 // 901119URL5918AMP18930PROTECT8198

		$str = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', self::xssHash()."\\1=\\2", $str);

		/*
		 * 验证标准字符实体
		 *
		 * 加上分号失踪。我们这样做使转化为后来的实体。
		 */
		$str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);

		/*
		 * 验证utf16 2字节编码（x00）
		 *
		 * Just as above, adds a semicolon if missing.
		 *
		 */
		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;", $str);

		/*
		 * Un-Protect GET variables in URLs
		 */
		$str = str_replace(self::xssHash(), '&', $str);

		return $str;
	}
	
	
	/**
	 * 属性转换
	 *
	 * @param	array $match
	 * @return	string
	 */
	protected static function _convertAttribute($match)
	{
		return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
	}
	
	
	/**
	 * 文本实体解码
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function entityDecode($str, $charset='UTF-8')
	{
		if (stristr($str, '&') === FALSE)
		{
			return $str;
		}
		$str = html_entity_decode($str, ENT_COMPAT, $charset);
		$str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
		return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
	}
	
	/**
	 * 网页实体解码回调
	 * 
	 * @param	array $match
	 * @return	string
	 */
	protected static function _decodeEntity($match)
	{
		return self :: entityDecode($match[0]);
	}
	
	
	/**
	 * 删除一个单词字母之间的空格
	 * 
	 * @param array $matches
	 * @return $str
	 */
	protected static function _compactExplodedWords($matches)
	{
		return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
	}
	
	
	/**
	 * 过滤器属性，保证过滤器标签属性的一致性和安全性
	 *
	 * @param string $str
	 * @return string
	 */
	protected static function _filterAttributes($str)
	{
		$out = '';
		if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
		{
			foreach ($matches[0] as $match)
			{
				$out .= preg_replace("#/\*.*?\*/#s", '', $match);
			}
		}
		return $out;
	}
	
	
	/**
	 * 移除js链接
	 *
	 * @param array  $match
	 * @return string
	 */
	protected static function _removeJsLink($match)
	{
		$attributes = self::_filterAttributes(str_replace(array('<', '>'), '', $match[1]));
		return str_replace($match[1], preg_replace("#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
	}
	
	/**
	 * 移除图片js脚本
	 *
	 * @param array $match
	 * @return string
	 */
	protected static function _removeJsImage($match)
	{
		$attributes = self::_filterAttributes(str_replace(array('<', '>'), '', $match[1]));
		return str_replace($match[1], preg_replace("#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
	}
	
	
	/**
	 * 移除恶意的属性
	 * 
	 * @param string $str
	 */
	protected static function _removeEvilAttributes($str)
	{
		$evil_attributes = array('on\w*','xmlns', 'formaction'); //remove style
		
		do {
			$count = 0;
			$attribs = array();
			
			preg_match_all("/(".implode('|', $evil_attributes).")\s*=\s*([^\s]*)/is",  $str, $matches, PREG_SET_ORDER);
			
			foreach ($matches as $attr)
			{
				$attribs[] = preg_quote($attr[0], '/');
			}
			
			preg_match_all("/(".implode('|', $evil_attributes).")\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is",  $str, $matches, PREG_SET_ORDER);

			foreach ($matches as $attr)
			{
				$attribs[] = preg_quote($attr[0], '/');
			}

			if (count($attribs) > 0)
			{
				$str = preg_replace("/<(\/?[^><]+?)([^A-Za-z\-])(".implode('|', $attribs).")([\s><])([><]*)/i", '<$1$2$4$5', $str, -1, $count);
			}
			
		} while ($count);
		
		return $str;
	}
	
	
	/**
	 * 消除顽皮的HTML
	 *
	 * @param array $matches
	 * @return string
	 */
	protected static function _sanitizeNaughtyHtml($matches)
	{
		$str = '&lt;'.$matches[1].$matches[2].$matches[3];
		$str .= str_replace(array('>', '<'), array('&gt;', '&lt;'),$matches[4]);
		return $str;
	}
	
	
	/**
	 * 不允许
	 *
	 * @param string $str
	 * @return 	string
	 */
	protected static function _doNeverAllowed($str)
	{
		foreach (self::$_never_allowed_str as $key => $val)
		{
			$str = str_replace($key, $val, $str);
		}

		foreach (self::$_never_allowed_regex as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		return $str;
	}
	
	
	/**
	 * 清除XSS
	 * 
	 * @param mixed $str 需要转换的字符串
	 */
	public static function xssClean($str)
	{
	    if (is_array($str))
		{
			while (list($key) = each($str))
			{
				$str[$key] = self::xssClean($str[$key]);
			}
			return $str;
		}
		
		$str = removeInvisibleCharacters($str);
		
		$str = self::validateEntities($str);
		
		$str = rawurldecode($str);
		
        $str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array(__CLASS__, '_convertAttribute'), $str);
        
		$str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array(__CLASS__, '_decodeEntity'), $str);
		
		$str = removeInvisibleCharacters($str);
		
	    if (strpos($str, "\t") !== FALSE)
		{
			$str = str_replace("\t", ' ', $str);
		}
		
		$str = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);

		$words = array(
				'javascript', 'expression', 'vbscript', 'script',
				'applet', 'alert', 'document', 'write', 'cookie', 'window'
			);

		foreach ($words as $word)
		{
			$temp = '';
			for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++)
			{
				$temp .= substr($word, $i, 1)."\s*";
			}
			$str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', array(__CLASS__, '_compactExplodedWords'), $str);
		}

		do
		{
			$original = $str;
            /*
			if (preg_match("/<a/i", $str))
			{
				$str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", array(__CLASS__, '_removeJsLink'), $str);
			}
            
			if (preg_match("/<img/i", $str))
			{
				$str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", array(__CLASS__, '_removeJsImage'), $str);
			}
			*/
			if (preg_match("/script/i", $str) OR preg_match("/xss/i", $str))
			{
				$str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
			}
		}
		while($original != $str);

		unset($original);
		
		$str = self::_removeEvilAttributes($str);
		
		// delete embed| 2013.1.8
		$naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		$str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', array(__CLASS__, '_sanitizeNaughtyHtml'), $str);
		
		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
		$str = self::_doNeverAllowed($str);
        
		return $str;
	}

}