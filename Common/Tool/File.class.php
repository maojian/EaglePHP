<?php
/**
 * 文件工具类
 * @author maojianlw@139.com
 *
 */
class File 
{
    
	/**
	 * 以读的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const READ = 'rb';
	
	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const READWRITE = 'rb+';
	
	/**
	 * 以写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const WRITE = 'wb';
	
	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const WRITEREAD = 'wb+';
	
	/**
	 * 以追加写入方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const APPEND_WRITE = 'ab';
	
	/**
	 * 以追加读写入方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const APPEND_WRITEREAD = 'ab+';
	
	
	/**
	 * 删除文件
	 * 
	 * @param string $filename 文件名称
	 * @return boolean
	 */
	public static function del($filename) 
	{
		return @unlink($filename);
	}

	/**
	 * 写文件
	 *
	 * @param string $fileName 文件绝对路径
	 * @param string $data 数据
	 * @param string $method 读写模式,默认模式为rb+
	 * @param bool $ifLock 是否锁文件，默认为true即加锁
	 * @param bool $ifCheckPath 是否检查文件名中的“..”，默认为true即检查
	 * @param bool $ifChmod 是否将文件属性改为可读写,默认为true
	 * @return int 返回写入的字节数
	 */
	public static function write($fileName, $data, $method = self::WRITE, $ifLock = true, $ifCheckPath = true, $ifChmod = true) 
	{
		//touch($fileName);
		if (!$handle = fopen($fileName, $method)) return false;
		$ifLock && flock($handle, LOCK_EX);
		$writeCheck = fwrite($handle, $data);
		$method == self::READWRITE && ftruncate($handle, strlen($data));
		fclose($handle);
		//$ifChmod && chmod($fileName, 0777);
		return $writeCheck;
	}

	/**
	 * 读取文件
	 *
	 * @param string $fileName 文件绝对路径
	 * @param string $method 读取模式默认模式为rb
	 * @return string 从文件中读取的数据
	 */
	public static function read($fileName, $method = self::READ) 
	{
		$data = '';
		if (!$handle = fopen($fileName, $method)) return false;
		while (!feof($handle))
			$data .= fgets($handle, 4096);
		fclose($handle);
		return $data;
	}

	/**
	 * @param string $fileName
	 * @return boolean
	 */
	public static function isFile($fileName) 
	{
		return $fileName ? is_file($fileName) : false;
	}

	/**
	 * 取得文件信息
	 * 
	 * @param string $fileName 文件名字
	 * @return array 文件信息
	 */
	public static function getInfo($fileName) 
	{
		return self::isFile($fileName) ? stat($fileName) : array();
	}

	/**
	 * 取得文件后缀
	 * 
	 * @param string $filename 文件名称
	 * @return string
	 */
	public static function getSuffix($filename) 
	{
		if (false === ($rpos = strrpos($filename, '.'))) return '';
		return substr($filename, $rpos + 1);
	}
	
	
	/**
	 * 获取文件类型
	 * 
	 * @param string $mime
	 * @return array
	 */
	public static function getMimes($mime = null)
	{
	    $mimes = array(	
    	    		'hqx'	=>	array('application/mac-binhex40'),
    				'cpt'	=>	array('application/mac-compactpro'),
    				'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
    				'bin'	=>	array('application/macbinary'),
    				'dms'	=>	array('application/octet-stream'),
    				'lha'	=>	array('application/octet-stream'),
    				'lzh'	=>	array('application/octet-stream'),
    				'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
    				'class'	=>	array('application/octet-stream'),
    				'psd'	=>	array('application/x-photoshop'),
    				'so'	=>	array('application/octet-stream'),
    				'sea'	=>	array('application/octet-stream'),
    				'dll'	=>	array('application/octet-stream'),
    				'oda'	=>	array('application/oda'),
    				'pdf'	=>	array('application/pdf', 'application/x-download'),
    				'ai'	=>	array('application/postscript'),
    				'eps'	=>	array('application/postscript'),
    				'ps'	=>	array('application/postscript'),
    				'smi'	=>	array('application/smil'),
    				'smil'	=>	array('application/smil'),
    				'mif'	=>	array('application/vnd.mif'),
    				'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
    				'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
    				'wbxml'	=>	array('application/wbxml'),
    				'wmlc'	=>	array('application/wmlc'),
    				'dcr'	=>	array('application/x-director'),
    				'dir'	=>	array('application/x-director'),
    				'dxr'	=>	array('application/x-director'),
    				'dvi'	=>	array('application/x-dvi'),
    				'gtar'	=>	array('application/x-gtar'),
    				'gz'	=>	array('application/x-gzip'),
    				'php'	=>	array('application/x-httpd-php'),
    				'php4'	=>	array('application/x-httpd-php'),
    				'php3'	=>	array('application/x-httpd-php'),
    				'phtml'	=>	array('application/x-httpd-php'),
    				'phps'	=>	array('application/x-httpd-php-source'),
    				'js'	=>	array('application/x-javascript'),
    				'swf'	=>	array('application/x-shockwave-flash'),
    				'sit'	=>	array('application/x-stuffit'),
    				'tar'	=>	array('application/x-tar'),
    				'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
    				'xhtml'	=>	array('application/xhtml+xml'),
    				'xht'	=>	array('application/xhtml+xml'),
    				'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
    				'mid'	=>	array('audio/midi'),
    				'midi'	=>	array('audio/midi'),
    				'mpga'	=>	array('audio/mpeg'),
    				'mp2'	=>	array('audio/mpeg'),
    				'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
    				'aif'	=>	array('audio/x-aiff'),
    				'aiff'	=>	array('audio/x-aiff'),
    				'aifc'	=>	array('audio/x-aiff'),
    				'ram'	=>	array('audio/x-pn-realaudio'),
    				'rm'	=>	array('audio/x-pn-realaudio'),
    				'rpm'	=>	array('audio/x-pn-realaudio-plugin'),
    				'ra'	=>	array('audio/x-realaudio'),
    				'rv'	=>	array('video/vnd.rn-realvideo'),
    				'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
    				'bmp'	=>	array('image/bmp', 'image/x-windows-bmp'),
    				'gif'	=>	array('image/gif'),
    				'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
    				'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
    				'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
    				'png'	=>	array('image/png',  'image/x-png'),
    				'tiff'	=>	array('image/tiff'),
    				'tif'	=>	array('image/tiff'),
    				'css'	=>	array('text/css'),
    				'html'	=>	array('text/html'),
    				'htm'	=>	array('text/html'),
    				'shtml'	=>	array('text/html'),
    				'txt'	=>	array('text/plain'),
    				'text'	=>	array('text/plain'),
    				'log'	=>	array('text/plain', 'text/x-log'),
    				'rtx'	=>	array('text/richtext'),
    				'rtf'	=>	array('text/rtf'),
    				'xml'	=>	array('text/xml'),
    				'xsl'	=>	array('text/xml'),
    				'mpeg'	=>	array('video/mpeg'),
    				'mpg'	=>	array('video/mpeg'),
    				'mpe'	=>	array('video/mpeg'),
    				'qt'	=>	array('video/quicktime'),
    				'mov'	=>	array('video/quicktime'),
    				'avi'	=>	array('video/x-msvideo'),
    				'movie'	=>	array('video/x-sgi-movie'),
    				'doc'	=>	array('application/msword'),
    				'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    				'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
    				'word'	=>	array('application/msword', 'application/octet-stream'),
    				'xl'	=>	array('application/excel'),
    				'eml'	=>	array('message/rfc822'),
    				'json'  =>  array('application/json', 'text/json')
			);
			    
			if($mime && isset($mimes[$mime]))
			{
			    return $mimes[$mime];
			}
			
			return $mimes;
	}
	
	

    /**
     *  PHP-HTTP断点续传实现
     *  
     *  @param string $file 文件路径
     *  @param string $name 文件名称
     *  @return void
     */
    public static function download($file, $name='') 
    {
        $name = $name ? $name : basename($file);
        if(!file_exists($file)) return false;
        $size = filesize($file);
        $size2 = $size-1;
        $range = 0;
        if(isset($_SERVER['HTTP_RANGE'])) 
        {
            header('HTTP /1.1 206 Partial Content');
            $range = str_replace('=','-',$_SERVER['HTTP_RANGE']);
            $range = explode('-',$range);
            $range = trim($range[1]);
            header('Content-Length:'.$size);
            header('Content-Range: bytes '.$range.'-'.$size2.'/'.$size);
        } 
        else 
        {
            header('Content-Length:'.$size);
            header('Content-Range: bytes 0-'.$size2.'/'.$size);
        }
        header('Accenpt-Ranges: bytes');
        header('application/octet-stream');
        header("Cache-control: public");
        header("Pragma: public");
        
        //解决在IE中下载时中文乱码问题
        $ua = $_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/MSIE/',$ua)) 
        {
            $ie_filename = str_replace('+','%20',urlencode($name));
            header('Content-Disposition:attachment; filename='.$ie_filename);
        }  
        else 
        {
            header('Content-Disposition:attachment; filename='.$name);
        }
        $fp = fopen($file,'rb+');
        fseek($fp,$range);
        while(!feof($fp)) 
        {
            set_time_limit(0);
            print(fread($fp,1024));
            flush();
            ob_flush();
        }
        fclose($fp);
    }

	
	
}