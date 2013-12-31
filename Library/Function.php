<?php

/**
 * EaglePHP框架系统公共函数库
 *
 * @author maojianlw@139.com
 * @since 2.6 - 2013-06-07
 * @link http://www.eaglephp.com
 */


/**
 * 显示404页面
 *
 * @return void
 */
function show_404()
{
    if(!__CLI__) //非命令行模式
    {
        $html = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
                 <HTML><HEAD>
                 <TITLE>404 Not Found</TITLE>
                 </HEAD><BODY>
                 <H1>Not Found</H1>
                 The requested URL '.HttpRequest::getServer('REQUEST_URI').' was not found on this server.<P>
                 <HR>
                 <ADDRESS>Web Server at '.HttpRequest::getHttpHost().' Port '.HttpRequest::getServerPort().'</ADDRESS>
                 </BODY></HTML>';
        echo $html;
        HttpResponse::sendHeader(404);
    }
    exit('404 Not Found');
}


/**
 * 删除不可见字符
 * @param string $str
 * @param bool $urlEncoded
 * @return string
 */
function removeInvisibleCharacters($str, $urlEncoded = true)
{
    $invisibleArr = array();
    if($urlEncoded)
    {
        $invisibleArr[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
        $invisibleArr[] = '/%1[0-9a-f]/';	// url encoded 16-31
    }
    $invisibleArr[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127
    do
    {
        $str = preg_replace($invisibleArr, '', $str, -1, $count);
    }
    while ($count);

    return $str;
}


/**
 * html特殊字符转码
 * @param mixed $var
 * @return mixed
 */
function htmlEscape($var)
{
    if(is_array($var))
    {
        return array_map('htmlEscape', $var);
    }
    else
    {
        return htmlspecialchars($var, ENT_QUOTES);
    }
}


/**
 * 跳转至指定的路径
 * @param string $url 路径
 * @param int $time 暂停几秒
 * @param string $msg 页面显示的提示信息
 * @param bool $isConvert 是否按照URL_MODEL转换
 * @return void 直接退出
 */
function redirect($url, $time = 0, $msg = '', $result=0, $isConvert=true)
{
    $html = $meta = null;
    $url = ($isConvert && $url) ? url($url) : $url;
    if($url) (!HttpResponse::isSendHeader()) ? HttpResponse::sendContentHeader($time > 0 ? "refresh:{$time};url={$url}" : "Location:{$url}") : $meta = "<meta http-equiv=refresh content={$time};URL={$url}>";
    if ($time > 0) $html = template($msg, $result, $url);
    exit ($html . $meta);
}


/**
 * 提示信息模板
 */
function template($message, $result=0, $url='')
{
    $message = nl2br($message);
    $copyright = getCfgVar('cfg_webname');
    $promptMsg = language('SYSTEM:prompt.msg');
    $promptForward = language('SYSTEM:prompt.forward');
    $image = __SHARE__.'image/msg.png';
	$imageBG = __SHARE__.'image/msg_bg.png';
	switch ($result)
	{
		case 1:
			$cls = 'ok';
			break;
		case 2:
			$cls = 'guery';
			break;
		default:
			$cls = 'no';
			break;
	}
	$forwardText = ($url) ? "<a href='{$url}'>{$promptForward}</a>" : '<a href="javascript:history.back();" >[点这里返回上一页]</a>';
    $html = <<<EOT
    			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=7" />
				<title>{$copyright}-{$promptMsg}</title>
				<style type="text/css">
				<!--
				*{padding:0; margin:0; font-size:12px}
				a:link,a:visited{text-decoration:none;color:#0068a6}
				a:hover,a:active{color:#ff6600;text-decoration: underline}
				.showMsg{border: 1px solid #1e64c8; zoom:1; width:450px; height:172px;position:absolute;top:44%;left:50%;margin:-87px 0 0 -225px}
				.showMsg h5{background-image: url({$image});background-repeat: no-repeat; color:#fff; padding-left:35px; height:25px; line-height:26px;*line-height:28px; overflow:hidden; font-size:14px; text-align:left;}
				.showMsg .content{padding:46px 12px 10px 45px; font-size:14px; height:64px; text-align:left}
				.showMsg .bottom{background:#e4ecf7; margin: 0 1px 1px 1px;line-height:26px; *line-height:30px; height:26px; text-align:center}
				.showMsg .ok, .showMsg .guery, .showMsg .no{background: url({$imageBG}) no-repeat 0px -560px;}
				.showMsg .guery{background-position: left -460px;}
				.showMsg .no{background-position: left -360px;}
				-->
				</style>
				</head>
				<body>
				<div class="showMsg" style="text-align:center">
					<h5>{$promptMsg}</h5>
				    <div class="content {$cls}" style="display:inline-block;display:-moz-inline-stack;zoom:1;*display:inline;max-width:330px">{$message}</div>
				    <div class="bottom">{$forwardText}</div>
				</div>
				</body>
				</html>
EOT;
    return $html;
}


/**
 * 格式化输出，一般用于调试跟踪
 */
function dump($vars, $label = null, $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label !== null && $label !== '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= print_r($vars, true);
        $content .= "\n</pre>\n";
    } else {
        $content = "\n";
        if ($label !== null && $label !== '') {
            $content .= $label . " :\n";
        }
        $content .= print_r($vars, true) . "\n";
    }
    if ($return) {
        return $content;
    }

    echo $content;
    return null;
}


/**
 *
 * 导入文件
 * @param string $path
 * @param bool or string $ext
 * @param string $baseUrl
 * @return mixed
 */
function import($path, $ext = true, $baseUrl = COM_DIR)
{
    $file = $baseUrl.str_replace('.', '/', $path);
    $file .= (is_bool($ext) ? ($ext ? '.class.php' : '.php') : $ext);
    $file = realpath($file);
    static $f_cache = array();
    $env_name = "IMPORT_{$file}";
    if(!isset($f_cache[$env_name]))
    {
        if(is_file($file))
        {
            $f_cache[$env_name] = require ($file);
        }
        else
        {
            return false;
        }
    }
    return $f_cache[$env_name];
}


/**
 * model函数别名
 *
 * @param string $className
 * @param string $dbFlag
 */
function M($className, $dbFlag = __DEFAULT_DATA_SOURCE__)
{
    return Model::getModel($className, $dbFlag);
}


/**
 * 获取模型层对象，在除了控制器类外的对象调用
 *
 * @param string $className
 * @param string $dbFlag
 * @return object
 */
function model($className, $dbFlag = __DEFAULT_DATA_SOURCE__)
{
    return Model::getModel($className, $dbFlag);
}


/**
 * 获取配置文件，config函数别名
 *
 * @param string $file_name
 * @return mixed
 */
function C($file_name)
{
    return config($file_name);
}


/**
 * 获取配置文件
 *
 * @param string $file_name
 * @return mixed
 */
function config($file_name)
{
    static $fileData  = array();
    if(!isset($fileData[$file_name]))
    {
        $file = realpath(APP_CONFIG_DIR . $file_name . '.inc.php');
        if(is_file($file))
        {
            $fileData[$file_name] = require ($file);
        }
        else
        {
            return false;
        }
    }
    return $fileData[$file_name];
}


/**
 * 文件数据读写(简单数据类型、数组、字符串等)，fileRW函数别名
 *
 * @param string $name
 * @param string $value
 * @param string $path
 */
function F($name, $value='', $path=DATA_DIR)
{
    return fileRW($name, $value, $path);
}



/**
 * 文件数据读写(简单数据类型、数组、字符串等)
 *
 * @param string $name
 * @param string $value
 * @param string $path
 */
function fileRW($name, $value='', $path=DATA_DIR)
{
    static $f_cache = array();
    $fileName = $path.$name.'.php';
    if($value !== '')
    {
        if(is_null($value)) return File::del($fileName); // 如果传递的值置为null，将删除文件缓存。
        $dir = dirname($fileName);
        if(!is_dir($dir)) mk_dir($dir);
        return File::write($fileName, "<?php\nreturn ".var_export($value,true).";\n?>", File::WRITE);
    }
    elseif(isset($f_cache[$name]))
    {
        $value = $f_cache[$name];
    }
    elseif(is_file($fileName))
    {
        $value = include $fileName;
        $f_cache[$name] = $value;
    }
    else
    {
        $value = false;
    }
    return $value;
}


/**
 * 缓存数据读取和设置，cache函数别名
 *
 * @param string $name
 * @param string $value
 * @param int $expire
 * @param string $type
 * @return mixed
 */
function H($name, $value='', $expire='', $type='')
{
    return cache($name, $value, $expire, $type);
}


/**
 * 缓存数据读取和设置
 *
 * @param string $name
 * @param string $value
 * @param int $expire
 * @param string $type
 * @param string $dir 缓存目录，仅当缓存类型为file时生效
 * @return mixed
 */
function cache($name, $value='', $expire='', $type='', $dir='')
{
    if(getCfgVar('cfg_open_cache') == 0) return false;
    static $_cache = array();
    $flag = "{$type}_{$name}";
    $cache = Cache::getInstance($type, array('expire'=>$expire, 'dir'=>$dir));
    if($value !== '')
    {
        if(is_null($value))
        {
            $result = $cache->rm($name); // 删除缓存
            if($result) unset($_cache[$flag]); 
            return $result;
        }
        $cache->set($name, $value, $expire);
        $_cache[$flag] = $value;
        return true;
    }
    if(isset($_cache[$flag]))
    {
        return $_cache[$flag];
    }
    else
    {
        return $_cache[$flag] = $cache->get($name);
    }
}



/**
 * 获取客户端IP
 *
 * @return string
 */
function get_client_ip()
{
    if (getenv ('HTTP_CLIENT_IP') && strcasecmp ( getenv ('HTTP_CLIENT_IP'), 'unknown' ))
    $ip = getenv ( 'HTTP_CLIENT_IP' );
    else if (getenv ('HTTP_X_FORWARDED_FOR') && strcasecmp ( getenv ('HTTP_X_FORWARDED_FOR'), 'unknown'))
    $ip = getenv ('HTTP_X_FORWARDED_FOR');
    else if (getenv ('REMOTE_ADDR') && strcasecmp ( getenv ('REMOTE_ADDR'), 'unknown'))
    $ip = getenv ('REMOTE_ADDR');
    else
    $ip = HttpRequest::getServer('REMOTE_ADDR');
    if(strpos($ip, ',')!==false)
    {
    	$ipArr = explode(',', $ip);
    	$ip = $ipArr[0];
    }
    return $ip;
}


/**
 * 获取上传文件地址
 *
 * @return string
 */
function getUploadAddr()
{
    $absolutePath = realpath(PUB_DIR.'share/upload/');
    if($absolutePath === false)
    {
        $absolutePath = realpath(PUB_DIR.__UPLOAD__);
        if($absolutePath === false)
        {
            $arr = array_filter(explode('/',__UPLOAD__));
            $absolutePath = realpath(PUB_DIR.__DS__.$arr[2].__DS__.$arr[3]);
        }
    }
    return $absolutePath.__DS__;
}


/**
 * 截取utf8字符串
 *
 * @return string
 */
function utf8Substr($str,$from,$len)
{
    return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s','$1',$str);
}


/**
 * 抛出异常
 *
 * @return Exception
 */
function throw_exception($message, $code=0, $type='TraceException')
{
    throw new $type($message, $code, true);
}


/**
 * 输出信息，程序终止退出
 *
 * @return void
 */
function halt($data, $attach=null)
{
    // 清楚输出缓存中的内容
    //ob_end_clean();
    if(HttpRequest::isAjaxRequest())
    {
        $data = (is_array($data)) ? implode('<br/>', $data) : $data;
        $output = json_encode(array('statusCode'=>300, 'message'=>$data, 'attach'=>$attach));
    }
    elseif (__CLI__)
    {
        $data = (is_array($data)) ? implode("\n", $data) : $data;
        $output = $data;
    }
    else
    {
        // 下面注释的这两句代码是另外一种错误信息提示方式
        //$data = (is_array($data)) ? implode('<br/>', $data) : $data;
        //$output = template($data, 'Error Info');
        // 错误信息以执行过的函数堆栈信息显示 back trace funcation
        Log::showDebugBackTrace();
    }
    exit($output);
}


/**
 * 自动释放来自客户端的连接
 *
 * @return void
 */
function abortConnect()
{
    set_time_limit(0);
    ignore_user_abort(true);
    $size = ob_get_length();
    header("Content-Length: $size");
    header('Connection: close');
    ob_end_flush();
    ob_flush();
    flush();
    session::writeClose();
}



/**
 *  循环创建目录
 *
 * @param string $dir
 * @param string $mode
 */
function mk_dir($dir, $mode = 0777)
{
    if(empty($dir)) return false;
    if (is_dir($dir)) return true; // || @mkdir($dir,$mode)
    if (!@mk_dir(dirname($dir),$mode)) return false;
    return @mkdir($dir,$mode);
}


/**
 * 递归删除目录
 *
 * @param string $dir
 * @return void
 */
function rm_dir($dir)
{
    if (is_dir($dir))
    {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..')
            {
                $file = $dir.'/'.$object;
                if (filetype($file) == 'dir') rm_dir($file);
                else unlink($file);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}


/**
 * 支持按字段对数组进行排序
 *
 * @param array $list 要排序的数组
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型  asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc')
{
    if(is_array($list))
    {
        $refer = $resultSet = array();
        foreach ($list as $i => $data) $refer[$i] = &$data[$field];
        switch ($sortby)
        {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ( $refer as $key=> $val) $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}



/**
 * 自动转换字符集 支持数组转换
 *
 * @param string $fContents
 * @param string $from
 * @param string $to
 * @return string
 */
function auto_charset($fContents,$from='gbk',$to='utf-8')
{
    $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
    $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
    if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) )
    {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if(is_string($fContents) )
    {
        if(function_exists('mb_convert_encoding'))
        {
            return mb_convert_encoding ($fContents, $to, $from);
        }
        elseif(function_exists('iconv'))
        {
            return iconv($from,$to,$fContents);
        }
        else
        {
            return $fContents;
        }
    }
    elseif(is_array($fContents))
    {
        foreach ( $fContents as $key => $val )
        {
            $_key = auto_charset($key,$from,$to);
            $fContents[$_key] = auto_charset($val,$from,$to);
            if($key != $_key ) unset($fContents[$key]);
        }
        return $fContents;
    }
    else
    {
        return $fContents;
    }
}


/**
 * CURL发送请求
 *
 * @param string $url
 * @param mixed $data
 * @param string $method
 * @param string $cookieFile
 * @param array $headers
 * @param int $connectTimeout
 * @param int $readTimeout
 */
function curlRequest($url,$data='',$method='POST',$cookieFile='',$headers='',$connectTimeout = 30,$readTimeout = 30)
{ 
    $method = strtoupper($method);
    if(!function_exists('curl_init')) return socketRequest($url, $data, $method, $cookieFile, $connectTimeout);

    $option = array(
        CURLOPT_URL => $url,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        CURLOPT_TIMEOUT => $readTimeout
    );

    if($headers) $option[CURLOPT_HTTPHEADER] = $headers;

    if($cookieFile)
    {
        $option[CURLOPT_COOKIEJAR] = $cookieFile;
        $option[CURLOPT_COOKIEFILE] = $cookieFile;
    }

    if($data && strtolower($method) == 'post')
    {
        $option[CURLOPT_POST] = 1;
        $option[CURLOPT_POSTFIELDS] = $data;
    }
	
	if(stripos($url, 'https://') !== false)
    {
    	$option[CURLOPT_SSL_VERIFYPEER] = false;
    	$option[CURLOPT_SSL_VERIFYHOST] = false;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch,$option);
    $response = curl_exec($ch);
    if(curl_errno($ch) > 0) throw_exception("CURL ERROR:$url ".curl_error($ch));
    curl_close($ch);
    return $response;
}


/**
 * socket发送请求
 *
 * @param string $url
 * @param string $post_string
 * @param string $method
 * @param int $connectTimeout
 * @param int $readTimeout
 * @return string
 */
function socketRequest($url, $data, $method, $cookieFile, $connectTimeout) {
    $return = '';
    $matches = parse_url($url);
    !isset($matches['host']) && $matches['host'] = '';
    !isset($matches['path']) && $matches['path'] = '';
    !isset($matches['query']) && $matches['query'] = '';
    !isset($matches['port']) && $matches['port'] = '';
    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;

    $conf_arr = array(
        'limit'=>0,
        'post'=>$data,
        'cookie'=>$cookieFile,
        'ip'=>'',
        'timeout'=>$connectTimeout,
        'block'=>TRUE,
        );

    foreach ($conf_arr as $k=>$v) ${$k} = $v;
    if($post) {
        if(is_array($post))
        {
            $postBodyString = '';
            foreach ($post as $k => $v) $postBodyString .= "$k=" . urlencode($v) . "&";
            $post = rtrim($postBodyString, '&');
        }
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: ".HttpRequest::getServer('HTTP_USER_AGENT')."\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: '.strlen($post)."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
        $out .= $post;
    } else {
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: ".HttpRequest::getServer('HTTP_USER_AGENT')."\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
    }
    $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    if(!$fp) {
        return '';
    } else {
        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);
        $status = stream_get_meta_data($fp);
        if(!$status['timed_out']) {
            while (!feof($fp)) {
                if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                    break;
                }
            }

            $stop = false;
            while(!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        @fclose($fp);
        return $return;
    }
}


/**
 * 把返回的数据集转换成Tree
 *
 * @access public
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0)
{
    // 创建Tree
    $tree = array();
    if(is_array($list))
    {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data)
        {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data)
        {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId)
            {
                $tree[] =& $list[$key];
            }
            else
            {
                if (isset($refer[$parentId]))
                {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 在数据列表中搜索
 *
 * @access public
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
 * @return array
 */
function list_search($list,$condition)
{
    if(is_string($condition))
    parse_str($condition,$condition);
    // 返回的结果集合
    $resultSet = array();
    foreach ($list as $key=>$data)
    {
        $find   =   false;
        foreach ($condition as $field=>$value)
        {
            if(isset($data[$field]))
            {
                if(0 === strpos($value,'/'))
                {
                    $find   =   preg_match($value,$data[$field]);
                }
                elseif($data[$field]==$value)
                {
                    $find = true;
                }
            }
        }
        if($find) $resultSet[] = &$list[$key];
    }
    return $resultSet;
}


/**
 * 检查文件或目录大小
 *
 * @param string $path
 * @return string
 */
function checkFileSize($path)
{
    static $total_size = 0;
    $dir = opendir($path);
    while($file = readdir($dir))
    {
        if(!preg_match('#^\.#', $file))
        {
            $file_path = $path.'/'.$file;
            if(is_dir($file_path)) checkFileSize($file_path);
            else $total_size += filesize($file_path);
        }
    }
    return $total_size;
}


/**
 * 单位自动转换函数
 *
 * @param float $size
 * @return string
 */
function getFileSize($size)
{
    $kb = 1024;         // Kilobyte
    $mb = 1024 * $kb;   // Megabyte
    $gb = 1024 * $mb;   // Gigabyte
    $tb = 1024 * $gb;   // Terabyte

    if($size < $kb)
    {
        return $size.' B';
    }
    else if($size < $mb)
    {
        return round($size/$kb,2).' KB';
    }
    else if($size < $gb)
    {
        return round($size/$mb,2).' MB';
    }
    else if($size < $tb)
    {
        return round($size/$gb,2).' GB';
    }
    else
    {
        return round($size/$tb,2).' TB';
    }
}

/**
 * 获取系统配置文件变量
 */
function getCfgVar($varname = null)
{
    static $config = null;
    if(!$config)
    {
        $config = import('Config.System', false, DATA_DIR);
    }
    return ($varname == null) ? $config : (isset($config[$varname]) ? $config[$varname] : '');
}


/**
 * 发送邮件函数
 *
 * @param string $to 收件人邮箱，多个收件人用逗号分隔
 * @param string $subject 主题
 * @param string $message 消息
 * @param string $attach 附件，多个附件用逗号分隔
 * @param string $username 用户名
 * @param string $password 密码
 * @param string $host 主机
 * @param string $port 端口
 * @return bool
 */
function sendMail($to, $subject, $message, $attach='', $username='', $password='', $host='', $port='')
{
    if(!$to || !$message) return false;
    $mailer = new Mailer();
    $mailer->IsSMTP();
    $mailer->Host = ($host ? $host : getCfgVar('cfg_smtp_server'));
    $mailer->Port = ($port ? $port : getCfgVar('cfg_smtp_port'));
    $mailer->SMTPAuth = getCfgVar('cfg_sendmail_bysmtp');
    $mailer->SMTPDebug = getCfgVar('cfg_debug_mode');
    $mailer->CharSet = 'utf-8';
    $mailer->Username = ($username ? $username : getCfgVar('cfg_smtp_usermail'));
    $mailer->Password = ($password ? $password : getCfgVar('cfg_smtp_password'));
    $to_arr = explode(',', $to);
     
    foreach($to_arr as $email) $mailer->AddAddress($email);
    if(!empty($attach))
    {
        $attach_arr = explode(',', $attach);
        foreach ($attach_arr as $att)
        {
            $mailer->AddAttachment($att);
        }
    }
     
    if($username)
    {
        $name_arr = explode('@', $username);
        $name = $name_arr[0];
    }
    else
    {
        $name = getCfgVar('cfg_smtp_user');
    }
     
    $mailer->SetFrom($mailer->Username, $name);
    $mailer->Subject = $subject;
    $mailer->MsgHTML($message);
    return $mailer->Send();
}


/**
 * 执行系统命令
 *
 * @param string $commond
 * @return string
 */
function execute($commond)
{
    $result = null;
    if ($commond)
    {
        if(function_exists('system'))
        {
            @ob_start();
            @system($commond);
            $result = @ob_get_contents();
            @ob_end_clean();
        }
        elseif(function_exists('passthru'))
        {
            @ob_start();
            @passthru($commond);
            $result = @ob_get_contents();
            @ob_end_clean();
        }
        elseif(function_exists('shell_exec'))
        {
            $result = @shell_exec($commond);
        }
        elseif(function_exists('exec'))
        {
            @exec($commond, $result);
            $result = join("\n", $result);
        }
        elseif(@is_resource($fop = @popen($commond, "r")))
        {
            while(!@feof($fop))
            {
                $result .= @fread($fop, 1024);
            }
            @pclose($fop);
        }
    }
    return $result;
}


/**
 * 获取语言文本，language函数别名
 *
 * @param string $key
 * @param array $params
 * @return string
 */
function L($key, $params = array())
{
    return language($key, $params);
}


/**
 * 获取语言文本
 *
 * @param string $key
 * @param array $params
 * @return string
 */
function language($key, $params = array())
{
    return I18n::getMessage($key, $params);
}


/**
 * 将数组中的某元素组合成一维数组
 * @param 1 : 数组
 * @param 2 : 维数(2维)
 * @param 3 : 取其中的某字段/也可以是取其中两个作为key和value关系array('id'=>'name')
 */
function arr2one($arr,$num=0,$field='')
{
    if(!is_array($arr)) return false;
    if($field=='') return false;
    elseif(is_array($field)) { $kkkkk=$field[0]; $vvvvv=$field[1]; }
    else { $kkkkk=''; $vvvvv=$field; }
    $num=(int)$num;//必须二维数组 或 三维数组
    $result = array();
    if($num==2){
        foreach($arr as $key=>$value){
            if(isset($value[$vvvvv])) {
                if($kkkkk=='')	$result[]=$value[$vvvvv];
                else			$result[$value[$kkkkk]]=$value[$vvvvv];
            }
        }
    }elseif($num==3){
        foreach($arr as $key=>$value){
            foreach($value as $k=>$v){
                if(isset($value[$k][$vvvvv])) $result[$value[$kkkkk]]=$v[$vvvvv];
			}
		}
	}
	return $result;
}


/**
 * 得到数组的标准差
 * 
 * @param float $avg
 * @param array $list
 * @param bool $isSwatch
 */
function getVariance($avg, $list, $isSwatch=false) 
{
    $arrayCount = count($list);
    if($arrayCount == 1 && $isSwatch == true) return FALSE;
    elseif($arrayCount > 0 )
    {
        $total_var = 0;
        foreach ($list as $lv) 
            $total_var += pow(($lv - $avg), 2);
        return $isSwatch ? sqrt($total_var / ($arrayCount - 1 )) : sqrt($total_var / $arrayCount);
    }
    else return false;
}


/**
 * 获取URL地址
 * 
 * @param string $url
 * @param bool $isEncode 是否URL编码
 * @param string $model url模式
 * @return string
 */
function url($url, $isEncode=false, $model=null)
{
    $url = $isEncode ? urlencode($url) : $url;
    return Router::url($url, $model);
}


/**
 * 正则提取文本中的url替换成可点击链接
 * 
 * @param srting $url
 * @return string
 */
function addLink($msg)
{
    // 解析链接
    function parseLink($linkArr)
    {
        $link = $linkArr[0];
        $url = (strpos($link, '://') === false) ? 'http://'.$link : $link;
        return "<a href=\"{$url}\" target=\"_blank\" rel=\"nofollow\">{$link}</a>";
    }
    return preg_replace_callback('#([(http?|ftp)://a-zA-Z0-9]*\.[a-zA-Z0-9\.]*[a-zA-Z])+([a-zA-Z0-9\~\!\@\#\$\%\^\&amp;\*\(\)_\-\=\+\\\/\?\.\:\;\'\,]*)?#', 'parseLink', $msg);
}