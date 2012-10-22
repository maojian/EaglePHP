<?php
/**
 * 文件工具类
 * @author maojianlw@139.com
 *
 */
class File {
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
	public static function del($filename) {
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
	public static function write($fileName, $data, $method = self::WRITE, $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
		//touch($fileName);
		if (!$handle = fopen($fileName, $method)) return false;
		$ifLock && flock($handle, LOCK_EX);
		$writeCheck = fwrite($handle, $data);
		$method == self::READWRITE && ftruncate($handle, strlen($data));
		fclose($handle);
		$ifChmod && chmod($fileName, 0777);
		return $writeCheck;
	}

	/**
	 * 读取文件
	 *
	 * @param string $fileName 文件绝对路径
	 * @param string $method 读取模式默认模式为rb
	 * @return string 从文件中读取的数据
	 */
	public static function read($fileName, $method = self::READ) {
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
	public static function isFile($fileName) {
		return $fileName ? is_file($fileName) : false;
	}

	/**
	 * 取得文件信息
	 * 
	 * @param string $fileName 文件名字
	 * @return array 文件信息
	 */
	public static function getInfo($fileName) {
		return self::isFile($fileName) ? stat($fileName) : array();
	}

	/**
	 * 取得文件后缀
	 * 
	 * @param string $filename 文件名称
	 * @return string
	 */
	public static function getSuffix($filename) {
		if (false === ($rpos = strrpos($filename, '.'))) return '';
		return substr($filename, $rpos + 1);
	}
}