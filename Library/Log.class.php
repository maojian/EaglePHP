<?php

/**
 * 日志记录类
 * @since 2.3 - 2012-11-28
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 */

class Log
{
    
    /**
     * 错误
     * @var string
     */
    const ERROR = 'ERROR';

    /**
     * 警告
     * @var string
     */
    const WARN = 'WARN';

    /**
     * 通知
     * @var string
     */
    const NOTICE = 'NOTICE';

    /**
     * 调试信息
     * @var string
     */
    const INFO = 'INFO';

    /**
     * SQL错误
     * @var string
     */
    const SQL = 'SQL';

    /**
     * 异常
     * @var string
     */
    const EXCEPTION = 'EXCEPTION';

    /**
     * 日志文件大小
     * @var int
     */
    const LOG_FILE_SIZE = 10097152;

    /**
     * 记录debug日志目录
     * @var string
     */
    const DEBUG_DIR = 'debug';

    /**
     * 记录访问日志目录
     * @var string
     */
    const ACCESS_DIR = 'access';

    /**
     * 日期格式
     * @var string
     */
    protected static $format = 'Y-m-d H:i:s';

    /**
     * 日志信息
     * @var array
     */
    static $log = array();
    
    /**
     * 错误追溯信息
     * @var array
     */
    static $trace = array();

    /**
     * 信息级别
     * @var array
     */
    static $levels = array('ERROR', 'WARN', 'INFO', 'SQL', 'EXCEPTION'); // 要记录的日志级别,  'NOTICE'


    /**
     * 初始化错误绑定函数和脚本终止前回调函数
     *
     * @return void
     */
    public static function init()
    {
        set_error_handler(array(__CLASS__, 'errorHandler'));// 错误处理绑定函数
        register_shutdown_function(array(__CLASS__, 'shutdonwHandler'));// 注册页面脚本终止前回调函数
    }


    /**
     * 页面脚本终止前回调函数
     *
     * @return void
     */
    public static function shutdonwHandler()
    {
        if (!is_null($last_error = error_get_last())) self::errorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line'], '');
        self::writeAccessLog(); // 记录访问日志
        //self::writeDebugLog(); // 记录系统日志
        Session::writeClose(); // 关闭session写入
    }
    

    /**
     * 错误处理绑定函数
     *
     * @param int $error_no
     * @param string $msg
     * @param string $file
     * @param int $line
     * @param array $vars
     * @return void
     */
    public static function errorHandler($error_no, $msg, $file, $line, $vars)
    {
    	if(error_reporting() === 0) return;// 加了@符号的错误将不再输出，也不会记录。
        self::$trace['error_no'] = $error_no;
        self::$trace['message'] = $msg;
        self::$trace['file'] = $file;
        self::$trace['line'] = $line;
        
        // 调试信息
        /*
        if (isset ($vars['debug_backtrace']))
        {
            self::$trace['debug_backtrace'] = $vars['debug_backtrace'];
            $debug_backtrace = self::$trace['debug_backtrace'][0];
            $request_array['DEBUG_BACKTRACE'] = $debug_backtrace;
            $file = $debug_backtrace['file'];
            $line = $debug_backtrace['line'];
        }
        else
        {
            self::$trace['debug_backtrace'] = $vars;
        }
        */

        switch($error_no)
        {
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
                	
            case self::EXCEPTION:
                $error_no = 1000;
                $level = self::EXCEPTION;
                break;
                	
            default :
                $level = self::INFO;
                break;
        }

        $separator = self::getSeparator();
        $msg = strip_tags(str_replace(array(chr(10), chr(13)), '', $msg));
        $msg = str_replace(chr(13).chr(10), '', $msg);
        $message = "[$error_no]{$separator}{$msg}{$separator}{$file}{$separator}{$line}";
        self::record($message, $level);
        self::writeDebugLog(); // 记录系统日志
    }


    /**
     * 获取日志数据分隔符
     *
     * @return string
     */
    public static function getSeparator()
    {
        return chr(31);
    }


    /**
     * 记录日志，过滤未经设置的级别
     *
     * @param string $message
     * @param string $level
     * @return void
     */
    public static function record($message, $level=self::ERROR)
    {
        // 按日志级别来记录
        if(in_array($level, self::$levels))
        {
            $now = date(self::$format);
            $file_name = LOG_DIR.date('y-m-d').'.log';
            $separator = self::getSeparator();
            $data = "[{$now}]{$separator}{$level}{$separator}{$message}";
            self::$log[] = $data;
        }
    }
    
    protected static function isDebug()
    {
    	return getCfgVar('cfg_debug_mode') == 1;
    }


    /**
     * 将程序中运行的各种类型信息保存到文件中
     *
     * @return void
     */
    public static function writeDebugLog()
    {
        if(!empty(self::$log))
        {
            self::$log[] = '';
            $message = implode("\r\n", self::$log);

            if(getCfgVar('cfg_system_log') == 1)
            {
                self::addLogData(self::DEBUG_DIR, $message);
            }

            // 系统报错邮件提醒
            if(getCfgVar('cfg_debug_email') == 1)
            {
                sendMail(getCfgVar('cfg_adminemail'), language('SYSTEM:app.error', array(APP_NAME)), nl2br($message));
            }
            
            // 如果开启调式，就输出信息
            if(self::isDebug())
            {
                halt(self::$log);
            }
            else
            {
                // 线上环境错误提示信息
                ob_end_clean();
                self::$trace = array();
                $msg = language('SYSTEM:server.error', array(getCfgVar('cfg_adminemail')));
                self::$trace['message'] = $msg;
                halt($msg);
            }
        }
    }


    /**
     * 检查程序运行过程中是否出错
     *
     * @return bool
     */
    public static function isError()
    {
        return (count(Log::$log) > 0) ? true : false;
    }


    /**
     * 输出客户端访问信息
     *
     * @return void
     */
    public static function output()
    {
        echo template(str_replace(self::getSeparator(), '&nbsp;', self::accessInfo()), 'access info');
        return;
    }

    /**
     * 收集客户端访问信息
     *
     * @return string
     */
    private static function accessInfo()
    {
        $separator = self::getSeparator();
        $now = date(self::$format);
        $ip = get_client_ip();
        RunTime::stop();
        $spent = RunTime::spent();
        $message = "[$now]".$separator.$ip.$separator.CONTROLLER_NAME.$separator.ACTION_NAME.$separator.HttpRequest::getServerName().':'.HttpRequest::getServerPort().$separator.$spent."\r\n";
        return $message;
    }


    /**
     * 记录客户端访问日志
     *
     * @return void
     */
    private static function writeAccessLog()
    {
        if(getCfgVar('cfg_access_log') == 1){
            $message = self::accessInfo();
            self::addLogData(self::ACCESS_DIR, $message);
        }
    }


    /**
     * 增加日志数据
     * 
     * @param string $dir_name
     * @param string $message
     * @return void
     */
    private static function addLogData($dir_name, $message)
    {
        $dir = LOG_DIR.$dir_name.'/';
        $date = date('Ymd');
        $dir .= $date.'/';
        mk_dir($dir);

        $file_name = $dir.$date.'_'.$dir_name.'.log';
        // 如果日志文件超过指定大小，将进行备份
        if(is_file($file_name) && filesize($file_name)>=self::LOG_FILE_SIZE)
        {
            rename($file_name, dirname($file_name).'/'.basename($file_name).'.bak');
        }
        file_put_contents($file_name, $message, FILE_APPEND);
        //error_log($message, 3, $file_name, '');
    }


    /**
     * 记录SQL错误信息
     * 
     * @param string $message
     * @return void
     */
    public static function sql($message)
    {
        $debug_backtrace = debug_backtrace();
        trigger_error($message, E_USER_ERROR);
    }


    /**
     * 记录调式信息
     * 
     * @param string $message
     * @return void
     */
    public static function info($message)
	{
		$debug_backtrace = debug_backtrace();
		trigger_error($message, E_USER_NOTICE);
	}
	
	
	/**
	 * 显示错误追溯信息
	 */
	public static function showDebugBackTrace()
	{
	    $text1 = $text2 = null;
	    $traceArr = (isset(self::$trace['debug_backtrace']) && !empty(self::$trace['debug_backtrace'])) ? self::$trace['debug_backtrace'] : debug_backtrace();
	    if(!empty(self::$trace['file']))
	    {
	        $text1 .= '<div class="info"><h1>('.self::$trace['error_no'].')'.self::$trace['message'].'</h1><div class="info2">FILE: '.self::$trace['file'].' &#12288;LINE:'.self::$trace['line'].'</div></div>';
	    }
	    else
	    {
	        $text1 .= '<div class="info"><h1>'.self::$trace['message'].'</h1></div>';
	    }
	    
	    if(self::isDebug() && is_array($traceArr))
	    {
	        $text2 = '<div class="info"><p><strong>PHP Debug</strong></p><table cellpadding="5" cellspacing="1" width="100%" class="table"><tr class="bg2"><td>No.</td><td>File</td><td>Line</td><td>Code</td></tr>';
            $dapArr = array('halt()', 'Log::errorHandler()', 'Log::writeDebugLog()', 'Log::showDebugBackTrace()');
	        foreach ($traceArr as $k=>$v)
    	    {
    	        $file = isset($v['file']) ? $v['file'] : '';
    	        $line = isset($v['line']) ? $v['line'] : '';
    	        $function = isset($v['function']) ? $v['function'] : '';
    	        $class = isset($v['class']) ? $v['class'] : '';
    	        $type = isset($v['type']) ? $v['type'] : '';
    	        $callText ="{$class}{$type}{$function}()";
    	        if(in_array($callText, $dapArr)) continue;
    	        $text2 .= "<tr class='bg1'><td>".($k+1)."</td><td>{$file}</td><td>{$line}</td><td>{$callText}</td></tr>";
    	    }
    	    $text2 .= '</table></div><div class="help"><a href="http://www.eaglephp.com">EaglePHP</a><sup>2.7</sup></div>';
	    }
	    
	    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                 <html>
                 <head>
                	<title>System Error - EaglePHP Framework</title>
                	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
                	<style type="text/css">
                	<!--
                	body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
                	#container { width: 1024px; margin:0 auto}
                	#message   { width: 1024px; color: black; }
                	 .red  {color: red;}
                	 a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
                	 a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
                	 h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
                	.bg1{ background-color: #FFFFCC;}
                	.bg2{ background-color: #EEEEEE;}
                	.table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
                	.info {background: none repeat scroll 0 0 #F3F3F3;border: 0px solid #aaaaaa;border-radius: 10px 10px 10px 10px;color: #000000;font-size: 11pt;line-height: 160%;margin-bottom: 1em;padding: 1em;}
                	.help {background: #F3F3F3;border-radius: 10px 10px 10px 10px;font: 12px verdana, arial, sans-serif;text-align: center;line-height: 160%;padding: 1em;}
                	.info2 {background: none repeat scroll 0 0 #FFFFCC;border: 1px solid #aaaaaa;color: #000000;font: arial, sans-serif;font-size: 9pt;line-height: 160%;margin-top: 1em;padding: 4px;}
                	-->
                	</style>
                </head>
                <body>
                <div id="container">'.$text1.$text2.'</div>
                </body>
                </html>';
	    exit($html);
	}
	
}