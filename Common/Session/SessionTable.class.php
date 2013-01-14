<?php

/**
 * 记录session数据至数据库
 * @author maojianlw@139.com
 * @since 2012-03-25
 * @link http://eaglephp.googlecode.com/
 */

class SessionTable {

    private static $handler = null;
    
    
    public static function init()
    {
        self::$handler = model('session');
        Session::module('user');
        Session::setSaveHandler(
            	array('SessionTable', 'open'),
            	array('SessionTable', 'close'),
            	array('SessionTable', 'read'),
            	array('SessionTable', 'write'),
            	array('SessionTable', 'destroy'),
            	array('SessionTable', 'gc')
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
        $session_info = self::$handler->field('data')->where("sid='{$session_id}' AND expiry>=".time())->find();
        return $session_info ? $session_info['data'] : '';
    }
    
    
    /**
    * 写入session
    */
    public static function write($session_id, $data)
    {
        $expiry = time() + SESSION_LIFE_TIME;
        $sql = "REPLACE INTO session (sid, expiry, data) VALUES('$session_id', $expiry, '{$data}')";
        self::$handler->execute($sql);
        return true;
    }
    
    
    /**
    * 销毁session
    */
    public static function destroy($session_id)
    {
        self::$handler->where("sid='{$session_id}'")->delete();
        return true;
    }
    
    
    /**
    * 垃圾回收
    */
    public static function gc($maxlifetime=null)
    {
        self::$handler->where('expiry<'.time())->delete();
        // 由于经常对session表进行删除操作，容易产生碎片，所以在垃圾回收中对该表进行优化。
        self::$handler->execute('OPTIMIZE TABLE session');
        return true;
    }
  
  
}
