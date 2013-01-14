<?php

/**
 * 记录session数据至Memcache
 * @author maojianlw@139.com
 * @since 2012-03-25
 * @link http://www.eaglephp.com/
 */

class SessionMemcache {

    private static $handler = null;
    
    public static function init()
    {
        self::$handler = Cache::getInstance('memcache', array('expire'=>SESSION_LIFE_TIME));
        Session::module('user');
        Session::setSaveHandler(
                  array('SessionMemcache', 'open'), 
                  array('SessionMemcache', 'close'), 
                  array('SessionMemcache', 'read'), 
                  array('SessionMemcache', 'write'), 
                  array('SessionMemcache', 'destroy'), 
                  array('SessionMemcache', 'gc')
               );
    }
    
    
    /**
    * 打开session
    */
    public static function open($save_path, $session_name)
    {
        return true;
    }
    
    /**
    * 关闭session
    */
    public static function close()
    {
        return true;
    }
    
    
    /**
    * 读取session
    */
    public static function read($session_id)
    {
        $data = self::$handler->get($session_id);
        if(!empty($data)){
            return $data;
        }else{
            self::$handler->set($session_id, 0);
            return true;
        }
    }
    
    
    /**
    * 写入session
    */
    public static function write($session_id='', $data='')
    {
        self::$handler->replace($session_id, $data);
        return true;
    }
    
    
    /**
    * 销毁session
    */
    public static function destroy($session_id)
    {
        self::write($session_id);
        return true;
    }
    
    
    /**
    * 垃圾回收
    * 无需额外回收，memcache有自己的过期回收机制
    */
    public static function gc($maxlifetime=null)
    {
        return true;
    }
  
}
