<?php

/**
 * 
 * EaglePHP FTP操作类
 * 
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 * @since 2013-06-18
 */

class Ftp
{
	
	/**
	 * 
	 * 用户名
	 * 
	 * @var string
	 */
	private static $username = null;
	
	
	/**
	 * 
	 * 密码
	 * 
	 * @var string
	 */
	private static $password = null;
	
	
	/**
	 * 
	 * 连接的服务器
	 * 
	 * @var string
	 */
	private static $host = null;
	
	/**
	 * 
	 * 连接资源句柄
	 * 
	 * @var resource
	 */
	private static $resource = null;
	
	
	/**
	 * 
	 * 连接并登陆ftp服务器
	 * 
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @param int $port
	 * @param int $timeout
	 * @param bool $pasv
	 * @return bool
	 */
	public static function connect($host, $username, $password, $port=21, $timeout=90, $pasv = false)
	{
		if(!extension_loaded('ftp')) throw_exception(language('SYSTEM:module.not.loaded', array('ftp')));
        if((self::$resource = ftp_connect($host, $port, $timeout)) === false) throw_exception('ftp_unable_to_connect');
        if(!self::login($username, $password)) throw_exception('ftp_unable_to_login');
        if($pasv === true) self::pasv($pasv);
        return true;
	}
	
	
	/**
	 * 
	 * 登录 FTP 服务器
	 * 
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public static function login($username, $password)
	{
		return ftp_login(self::$resource, $username, $password);
	}
	
	
	/**
	 * 
	 * 切换目录至当前目录的父目录 (上级目录)
	 * 
	 * @return bool
	 */
	public static function cdup()
	{
		return ftp_cdup(self::$resource);
	}
	
	
	/**
	 * 
	 * 将当前目录切换为指定的目录
	 * 
	 * @param string $directory
	 * @return bool
	 */
	public static function chdir($directory)
	{
		return ftp_chdir(self::$resource, $directory);
	}
	
	/**
	 * 
	 * 设置在指定的远程文件的权限模式
	 * 
	 * @param int $mode
	 * @param string $filename
	 * @return bool
	 */
	public static function chmod($mode, $filename)
	{
		return ftp_chmod(self::$resource, $mode, $filename);
	}
	
	
	/**
	 * 
	 * 关闭ftp连接标识符并释放资源
	 * 
	 * @return bool
	 */
	public static function close()
	{
		return ftp_close(self::$resource);
	}
	
	
	/**
	 * 
	 *  ftp_close() 的 别名
	 *  
	 *  @return bool
	 */
	public static function quit()
	{
		return self::close();
	}
	
	
	/**
	 * 
	 * 删除 FTP 服务器上的一个文件
	 * 
	 * @param string $path
	 * @return bool
	 */
	public static function delete($path)
	{
		return ftp_delete(self::$resource, $path);
	}
	
	
	/**
	 * 
	 * 请求运行一条 FTP 命令
	 * 
	 * @param string $command
	 * @return bool
	 */
	public static function exec($command)
	{
		return ftp_exec(self::$resource, $command);
	}
	
	/**
	 * 
	 * 发送到FTP服务器的任意命令
	 * 
	 * @param string $command
	 * @return array
	 */
	public static function raw($command)
	{
		return ftp_raw(self::$resource, $command);
	}
	
	
	/**
	 * 
	 * 从 FTP 服务器上下载一个文件并保存到本地一个已经打开的文件中
	 * 
	 * @param resource $handle 本地已经打开的文件的句柄
	 * @param string $remote_file  远程文件
	 * @param int $mode  传送模式参数 mode 必须是 (文本模式) FTP_ASCII 或 (二进制模式) FTP_BINARY 中的一个
	 * @param int $resumepos
	 * @return bool
	 */
	public static function fget($handle, $remote_file, $mode=FTP_ASCII, $resumepos=0)
	{
		return ftp_fget(self::$resource, $handle, $remote_file, $mode, $resumepos);
	}
	
	
	/**
	 * 
	 * 上传一个已经打开的文件到 FTP 服务器
	 * 
	 * @param string $remote_file
	 * @param resource $handle
	 * @param int $mod
	 * @param int $startpos
	 * @return bool
	 */
	public static function fput($remote_file, $handle, $mod=FTP_ASCII, $startpos=0)
	{
		return ftp_fput(self::$resource, $remote_file, $handle, $mode, $startpos);
	}
	
	
	/**
	 * 
	 * 返回当前 FTP 连接的各种不同的选项设置
	 * 
	 * @param int $option
	 * @return mixed
	 */
	public static function getOption($option)
	{
		return ftp_get_option(self::$resource, $option);
	}
	
	
	/**
	 * 
	 * 设置各种 FTP 运行时选项
	 * 
	 * @param int $option
	 * @param mixed $value
	 * @return bool
	 */
	public static function setOption($option, $value)
	{
		return ftp_set_option(self::$resource, $option, $value);
	}
	
	
	/**
	 * 
	 * 从 FTP 服务器上下载一个文件
	 * 
	 * @param string $local_file
	 * @param string $remote_file
	 * @param int $mode
	 * @param int $resumepos
	 * @return bool
	 */
	public static function get($local_file, $remote_file, $mode=FTP_ASCII, $resumepos=0)
	{
		return ftp_get(self::$resource, $local_file, $remote_file, $mode, $resumepos);	
	}
	
	
	
	/**
	 * 
	 * 上传文件到 FTP 服务器
	 * 
	 * @param string $remote_file
	 * @param string $local_file
	 * @param int $mode
	 * @param int $startpos
	 * @return bool
	 */
	public static function put($remote_file, $local_file, $mode=FTP_ASCII, $startpos=0)
	{
		return ftp_put(self::$resource, $remote_file, $local_file, $mode, $startpos);
	}
	
	
	/**
	 * 
	 * 返回指定文件的最后修改时间
	 * 
	 * @param string $remote_file
	 */
	public static function mdtm($remote_file)
	{
		return ftp_mdtm(self::$resource, $remote_file);
	}
	
	
	
	/**
	 * 
	 * 递归创建目录并授权
	 * 
	 * @param string $directory
	 * @param int $mode 权限模式
	 * @return string 如果成功返回新建的目录名，否则返回 FALSE
	 */
	public static function mkdir($directory, $mode=null)
	{
		if(empty($directory)) return false;
	    if(self::isDir($directory)) return true;
	    if(!self::mkdir(dirname($directory), $mode)) return false;
	    if(!ftp_mkdir(self::$resource, $directory)) return false;
	    if(!is_null($mode)) self::chmod($mode, $directory);
		return true;
	}
	
	
	/**
	 * 
	 *  递归删除 FTP服务器上的目录
	 *  
	 * @param string $directory
	 * @return bool
	 */
	public static function rmdir($directory)
	{
		$list = self::nlist($directory);
		if($list !== false && count($list) > 0)
		{
			foreach ($list as $k=>$v)
			{
				$path = "{$directory}/{$v}";
				if(self::isDir($path)) self::rmdir($path);
				else self::delete($path);
			}
		}
		return ftp_rmdir(self::$resource, $directory);
	}
	
	
	/**
	 * 
	 * 连续获取／发送文件（non-blocking）
	 * 以不分块的方式连续获取／发送一个文件。 
	 * 
	 * @return int 返回常量 FTP_FAILED 或 FTP_FINISHED 或 FTP_MOREDATA
	 */
	public static function nbContinue()
	{
		return ftp_nb_continue(self::$resource);
	}
	
	
	/**
	 * 从检索FTP服务器上的文件并将其写入一个打开的文件（非阻塞）
	 * 
	 * @param resource $handle
	 * @param string $remote_file
	 * @param int $mode
	 * @param int $resumepos
	 * @return int 返回ftp_failed或ftp_finished或ftp_moredata
	 */
	public static function nbFget($handle, $remote_file, $mode=FTP_ASCII, $resumepos=0)
	{
		return ftp_nb_fget(self::$resource, $handle, $remote_file, $mode, $resumepos);
	}
	
	
	/**
	 * 
	 * 从打开的文件到FTP服务器上的文件（非阻塞）
	 * 
	 * @param unknown_type $remote_file
	 * @param unknown_type $handle
	 * @param unknown_type $mode
	 * @param unknown_type $startpos
	 */
	public static function nbFput($remote_file, $handle, $mode=FTP_ASCII, $startpos=0)
	{
		return ftp_nb_fput(self::$resource, $remote_file, $handle, $mode, $startpos);
	}
	
	
	
	/**
	 * 
	 * 从 FTP 服务器上获取文件并写入本地文件（non-blocking）
	 * 
	 * @param string $local_file
	 * @param string $remote_file
	 * @param int $mode
	 * @param int $resumepos
	 * @return int  返回 FTP_FAILED，FTP_FINISHED 或 FTP_MOREDATA
	 */
	public static function nbGet($local_file, $remote_file, $mode=FTP_ASCII, $resumepos=0)
	{
		return ftp_nb_get(self::$resource, $local_file, $remote_file, $mode, $resumepos);
	}
	
	
	/**
	 * 
	 * 存储一个文件至 FTP 服务器（non-blocking）
	 * 
	 * @param string $remote_file
	 * @param string $local_file
	 * @param int $mode
	 * @param int $startpos
	 * @return int  返回 FTP_FAILED，FTP_FINISHED 或 FTP_MOREDATA
	 */
	public static function nbPut($remote_file, $local_file, $mode=FTP_ASCII, $startpos=0)
	{
		return ftp_nb_put(self::$resource, $remote_file, $local_file, $mode, $startpos);
	}
	
	
	/**
	 *
	 * 返回给定目录的文件列表
	 * 
	 * @param string $directory
	 * @return array
	 */
	public static function nlist($directory)
	{
		return ftp_nlist(self::$resource, $directory);
	}
	
	
	/**
	 * 
	 * 返回当前 FTP 被动模式是否打开
	 * 在被动模式打开的情况下，数据的传送由客户机启动，而不是由服务器开始。 
	 * 
	 * @param bool $pasv
	 * @return bool
	 */
	public static function pasv($pasv)
	{
		return ftp_pasv(self::$resource, $pasv);
	}
	
	
	/**
	 * 
	 * 返回当前目录名
	 * 
	 * @return string
	 */
	public static function pwd()
	{
		return ftp_pwd(self::$resource);
	}
	
	
	/**
	 * 
	 * 返回指定目录下文件的详细列表
	 * 将执行 FTP LIST 命令，并把结果做为一个数组返回
	 * 
	 * @param string $directory
	 * @return array
	 */
	public static function rawlist($directory)
	{
		return ftp_rawlist(self::$resource, $directory);
	}
	
	
	/**
	 * 
	 * 更改 FTP 服务器上的文件或目录名
	 * 
	 * @param string $oldname
	 * @param string $newname
	 * @return bool
	 */
	public static function rename($oldname, $newname)
	{
		return ftp_rename(self::$resource, $oldname, $newname);
	}
	
	
	/**
	 * 
	 * 向服务器发送 SITE 命令
	 * 
	 * @param string $command
	 * @return bool
	 */
	public static function site($command)
	{
		return ftp_site(self::$resource, $command);
	}
	
	
	/**
	 * 
	 * 返回指定文件的大小
	 * 
	 * @param string $remote_file
	 * @return int  如果指定文件不存在或发生错误，则返回 -1。有些 FTP 服务器可能不支持此特性。 
	 */
	public static function size($remote_file)
	{
		return ftp_size(self::$resource, $remote_file);
	}
	
	
	/**
	 * 
	 * 打开一个安全的ssl-ftp连接
	 * 注：此函数需要OpenSSL扩展库支持
	 * 
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 * @return resource
	 */
	public static function sslConnect($host, $port=21, $timeout=90)
	{
		return ftp_ssl_connect($host, $port, $timeout);
	}
	
	
	/**
	 * 
	 * 返回远程 FTP 服务器的操作系统类型
	 * 
	 * @return string
	 */
	public static function systype()
	{
		return ftp_systype(self::$resource);
	}
	
	
	/**
	 * 
	 * 发送一个配置命令远程FTP服务器对上传的文件分配空间。
	 * 注：许多FTP服务器不支持这个命令。
	 *  
	 * @param int $filesize 分配的字节数。
	 * @param string $result 服务器的响应文本表示将通过引用返回的结果如果提供一个变量。
	 * @return bool
	 */
	public static function alloc($filesize,  &$result='')
	{
		return ftp_alloc(self::$resource, $filesize, $result);
	}
	
	
	/**
	 * 
	 * 判断是否为目录
	 * 
	 * @param string $remote_file
	 * @return bool
	 */
	public static function isDir($remote_file)
	{
		return (self::size($remote_file) === -1 && self::rawlist($remote_file) !== false) ? true : false;
	}
	
	
	/**
	 * 
	 * 根据文件后缀获取ftp传输模式
	 * 
	 * @param string $file
	 * @return string
	 */
	private static function _getTransferMode($file)
	{
		$extArr = array('txt', 'text', 'php', 'phps', 'php4', 'js', 'css', 'htm', 'html', 'phtml', 'shtml', 'log', 'xml');
		if(($pos = strrpos($file, '.')) === false) return FTP_ASCII;
		$ext = substr($file, $pos + 1);
		return in_array($ext, $extArr) ? FTP_ASCII : FTP_BINARY;
	}
	
	
	/**
	 * 
	 * 上传文件至FTP服务器
	 * 
	 * @param string $local_file
	 * @param string $remote_file
	 * @param int $mode
	 * @param int $permissions
	 * @return void
	 */
	public static function upload($local_file, $remote_file, $mode=null, $permissions = null)
	{
		if(!file_exists($local_file)) throw_exception('ftp_no_source_file:'.$local_file);
		$mode = is_null($mode) ? self::_getTransferMode($local_file) : $mode;
		if(!self::put($remote_file, $local_file, $mode)) throw_exception("ftp_unable_to_upload:local_file[{$local_file}]/remote_file[{$remote_file}]");
		if(!is_null($permissions)) self::chmod($permissions, $remote_file);
		return true;
	}
	
	
	/**
	 * 
	 * 上传目录至FTP服务器
	 * 注：上传的中目录或文件名不能包含中文
	 * 
	 * @param string $local_dir
	 * @param string $remote_dir
	 * @param int $mode
	 * @param int $permissions
	 */
	public static function uploadDir($local_dir, $remote_dir, $permissions = null)
	{
		$list = Folder::read($local_dir);
		if(count($list) > 0)
		{
			if(!self::isDir($remote_dir)) self::mkdir($remote_dir, $permissions);
			foreach ($list as $k=>$v)
			{
				$local_path = $local_dir.'/'.$v;
				$remote_path = $remote_dir.'/'.$v;
				if(Folder::isDir($local_path)) self::uploadDir($local_path, $remote_path);
				else self::upload($local_path, $remote_path, null, $permissions);
			}
			return true;
		}
		return false;
	}
	
	
	/**
	 * 
	 * 从ftp下载文件至本地
	 * 
	 * @param string $remote_file
	 * @param string $local_file
	 * @param int $mode
	 * @return bool
	 */
	public static function download($remote_file, $local_file, $mode = null)
	{
		$mode = is_null($mode) ? self::_getTransferMode($remote_file) : $mode;
		if(!self::get($local_file, $remote_file, $mode)) throw_exception("ftp_unable_to_download:local_file[{$local_file}]-remote_file[{$remote_file}]");
		return true;
	}
	
	
	/**
	 * 
	 * 下载ftp上的文件夹至本地
	 * 
	 * @param string $remote_dir
	 * @param string $local_dir
	 */
	public static function downloadDir($remote_dir, $local_dir)
	{
		$list = self::nlist($remote_dir);
		if(count($list) > 0)
		{
			if(!Folder::isDir($local_dir)) mk_dir($local_dir);
			foreach ($list as $k=>$v)
			{
				$remote_path = $remote_dir.'/'.$v;
				$local_path = $local_dir.'/'.$v;
				if(self::isDir($remote_path)) self::downloadDir($remote_path, $local_path);
				else self::download($remote_path, $local_path);
			}
			return true;
		}
		return false;
	}
	
	
}