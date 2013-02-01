<?php
/**
 * 页面运行时间统计
 * @author maojianlw@139.com
 * @since 1.6 - 2011-10-14
 */
 
class RunTime 
{
	
    private static $start_time = 0;
    private static $stop_time = 0;
    
    /**
     * 获得当前Unix时间戳和微秒数
     * 
     * @return float
     */
    public static function getMicotime()
    {
    	list($usec, $sec) = explode(' ',microtime());
    	return ((float)$usec+(float)$sec);
    }
    
    
    /**
     * 启动开始时间
     * 
     * @return void
     */
    public static function init()
    {
    	self::start();
    }
    
    
    /**
     * 开始计时
     * 
     * @return void
     */
    public static function start()
    {
    	self::$start_time = self::getMicotime();
    }
    
    
    /**
     * 停止计时
     * 
     * @return void
     */
    public static function stop()
    {
    	self::$stop_time = self::getMicotime();
    }
    
    
    /**
     * 计算运行时间
     * 
     * @return float
     */
    public static function spent()
    {
    	return round((self::$stop_time-self::$start_time) * 1000);
    }
    
    
}
