<?php

/**
 * 日志记录类
 * @copyright Copyright &copy; 2011, MAOJIAN
 * @since 1.6 - 2011-7-14
 * @author maojianlw@139.com
 */

class Log {
	
	const ERROR = 'ERROR';  // 错误 
	const WARN = 'WARN'; // 警告
	const NOTICE = 'NOTICE'; // 通知
	const INFO = 'INFO'; // 调试信息
	const SQL = 'SQL'; // SQL错误
	const EXCEPTION = 'EXCEPTION'; // 异常
	
	const LOG_FILE_SIZE = 10097152; // 日志文件大小
	const DEBUG_DIR = 'debug';  // 记录debug日志目录
	const ACCESS_DIR = 'access'; // 记录访问日志目录
	
	static $format = 'Y-m-d H:i:s';
	static $log = array(); // 日志信息
	static $levels = array('ERROR', 'WARN', 'INFO', 'SQL', 'EXCEPTION'); // 要记录的日志级别,  'NOTICE'
	
	
	/**
	 * 初始化错误绑定函数和脚本终止前回调函数
	 */
	public static function init() {
		set_error_handler(array('Log', 'errorHandler'));// 错误处理绑定函数
		register_shutdown_function(array(Log, 'shutdonwHandler'));// 注册页面脚本终止前回调函数
	}
	
	
	/**
	 * 页面脚本终止前回调函数
	 */
	public static function shutdonwHandler() {
		if (!is_null($last_error = error_get_last())) {
			self::errorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line'], '');
		}
		self::writeAccessLog(); // 记录访问日志
		self::writeDebugLog(); // 记录系统日志
		Session::writeClose(); // 关闭session写入
	}

	/**
	* 错误处理绑定函数
	*/
	public function errorHandler($error_no, $msg, $file, $line, $vars) {
		
		// 调试信息
		if (isset ($vars['debug_backtrace'])) {
			$debug_backtrace = $vars['debug_backtrace'][0];
			$request_array['DEBUG_BACKTRACE'] = $debug_backtrace;
			$file = $debug_backtrace['file'];
			$line = $debug_backtrace['line'];
		}
		
		switch($error_no){
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			     $msg = mb_convert_encoding($msg, 'utf-8', 'gbk');
				 $level = self::ERROR;
				 break;
				 
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
				 $msg = mb_convert_encoding($msg, 'utf-8', 'gbk');
				 $level = self::WARN;
				 break;
				 
			case E_NOTICE:
			case E_STRICT:
				 $level = self::NOTICE;
				 break;
				 
			case E_USER_ERROR:
				 $level = self::SQL;
				 break;
				 
			case EXCEPTION:
				 $error_no = 1000;
				 $level = self::EXCEPTION;
				 break;
				 
			default :
				 $level = self::INFO;
				 break;
		}
		
		$separator = self::getSeparator();
		$msg = strip_tags($msg);
		$msg = str_replace(chr(13).chr(10), '', $msg);
		$message = "[$error_no]{$separator}{$msg}{$separator}{$file}{$separator}{$line}";
		self::record($message, $level);
	}
	
	
	/**
	 * 获取日志数据分隔符
	 */
	public static function getSeparator(){
		return chr(31);
	}
	
	
	/**
	 * 记录日志，过滤未经设置的级别
	 */
	public static function record($message, $level=self::ERROR){
		// 按日志级别来记录
		if(in_array($level, self::$levels)){
			$now = date(self::$format);
			$file_name = LOG_DIR.date('y-m-d').'.log';
			$separator = self::getSeparator();
			$data = "[{$now}]{$separator}{$level}{$separator}{$message}";
			self::$log[] = $data;
		}
	}
	
	
	/**
	 * 将程序中运行的各种类型信息保存到文件中
	 */
	public static function writeDebugLog(){
   		if(!empty(self::$log)){
   			self::$log[] = '';
   			$message = implode("\r\n", self::$log);
   			
   			if(getCfgVar('cfg_system_log') == 1){
   			   self::addLogData(self::DEBUG_DIR, $message);
   			}
   			
   			// 系统报错邮件提醒
   			if(getCfgVar('cfg_debug_email') == 1){
   			   sendMail(getCfgVar('cfg_adminemail'), L('SYSTEM:app.error', array(APP_NAME)), nl2br($message));
   			}
   			
   			// 如果开启调式，就输出信息
   			if(getCfgVar('cfg_debug_mode') == 1){
   				halt(implode('<br/>', self::$log));
   			}else{
   				halt(L('SYSTEM:server.error', array(getCfgVar('cfg_adminemail'))));
   			}
   		}
	}
	
	
	/**
	 * 检查程序运行过程中是否出错
	 */
	public static function isError(){
		return (count(Log::$log) > 0) ? true : false;
	}
	
	
	/**
	 * 输出客户端访问信息
	 */
	public static function output(){
		echo template(self::accessInfo(), 'access info');
		return;
	}
	
	/**
	 * 收集客户端访问信息
	 */
	private static function accessInfo(){
		$separator = self::getSeparator();
	    $now = date(self::$format);
	    $ip = get_client_ip();
	    RunTime::stop();
	    $spent = RunTime::spent();
		$message = "[$now]".$separator.$ip.$separator.CONTROLLER_NAME.$separator.ACTION_NAME.$separator.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'].$separator.$spent."\r\n";
		return $message;
	}
	
	
	/**
	 * 记录客户端访问日志
	 */
	private static function writeAccessLog(){
	    if(getCfgVar('cfg_access_log') == 1){
	     $message = self::accessInfo();
		 self::addLogData(self::ACCESS_DIR, $message);
	    }
	}
	
	
	/**
	 * 增加日志数据
	 */
	private static function addLogData($dir_name, $message){
		$dir = LOG_DIR.$dir_name.'/';
		$date = date('Ymd');
		$dir .= $date.'/';
		mk_dir($dir);
		
		$file_name = $dir.$date.'_'.$dir_name.'.log';
		// 如果日志文件超过指定大小，将进行备份
		if(is_file($file_name) && filesize($file_name)>=self::LOG_FILE_SIZE){
			rename($file_name, dirname($file_name).'/'.basename($file_name).'.bak');
		}
		error_log($message, 3, $file_name, '');
	}
	
	
	/**
	 * 记录SQL错误信息
	 */
	public static function sql($message){
		$debug_backtrace = debug_backtrace();
		trigger_error($message, E_USER_ERROR);
	}
	
	/**
	 * 记录调式信息
	 */
	public static function info($message){
		$debug_backtrace = debug_backtrace();
		trigger_error($message, E_USER_NOTICE);
	}

}
