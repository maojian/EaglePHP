<?php
/**
 * 系统异常基类
 * @author maojianlw@139.com
 * @since 1.0 - 2011-10-8
 */

class TraceException extends Exception{
	
	private $type; // 异常类型
	private $extra; // 过滤多余的调试信息
	
	/**
	 * 构造函数
	 * @param string $message
	 * @access public
	 */
    public function __construct($message, $code=0, $extra=false) {
    	parent::__construct($message, $code);
    	$this->type = get_class($this);
    	$this->extra = $extra;
    }
    
    
    /**
     * 异常输出，所有异常处理类均通过__toString输出错误
     * 每次异常都会写入日志
     * 该方法可以被子类重载
     * @access public
     * @return array
     */
    public function __toString(){
    	$trace = $this->getTrace();
    	if($this->extra) array_shift($trace);
  		$message = $this->type.' '.$this->message;
  		$file = isset($trace[0]['file']) ? $trace[0]['file'] : '';
  		$line = isset($trace[0]['line']) ? $trace[0]['line'] : '';
  		Log :: errorHandler(Log::EXCEPTION, $message, $file, $line, $trace);
    }
    
    
    /**
     * 初始化异常绑定
     */
    public static function init(){
    	set_exception_handler(array('TraceException', 'handle'));
    }
    
    
    public static function handle($e){
    	$e->__toString();
    }
    
}
