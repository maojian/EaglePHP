<?php

/**
 * Memcache操作类
 * @author maojianlw@139.com
 * @since 2012-08-02
 */

class CacheMemcache extends Cache{
    
    public function __construct($options)
    {
        if(!extension_loaded('memcache'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('memcache')));
        }
        
        if(!isset($options['host'])) $options['host'] = getCfgVar('cfg_memcache_host') ? getCfgVar('cfg_memcache_host') : '127.0.0.1';
        if(!isset($options['port'])) $options['port'] = getCfgVar('cfg_memcache_port') ? getCfgVar('cfg_memcache_port') : 11211;
        if(!isset($options['persistent'])) $options['persistent'] = false;
        if(!isset($options['timeout'])) $options['timeout'] = false;
        if(!isset($options['expire'])) $options['expire'] = getCfgVar('cfg_cache_time') ? getCfgVar('cfg_cache_time') : 0;
        
        $this->options = $options;
        $function = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new Memcache;
        $this->connected = $options['timeout'] === false ? $this->handler->$function($options['host'], $options['port']) : $this->handler->$function($options['host'], $options['port'], $options['timeout']);
    }
    
    /**
     * 获取memcache连接状态
     */
    public function isConnected()
    {
        return $this->connected;
    }
    
    /**
     * Memcache::set方法有四个参数，
     * 第一个参数是key，
     * 第二个参数是value，
     * 第三个参数可选，表示是否压缩保存，
     * 第四个参数可选，用来设置一个过期自动销毁的时间。
     */
    public function set($key,$val,$expire = null){
        if(is_null($expire))
        {
            $expire = $this->options['expire'];   
        }
    	return $this->handler->set($key, $val, 0, $expire);
    }
    
    
    /**
     * 替换已经存在的元素的值
     */
    public function replace($key, $val, $expire=null){
        if(is_null($expire))
        {
            $expire = $this->options['expire'];   
        }
    	return $this->handler->replace($key, $val, 0, $expire);
    }
    
    
    /**
     * Memcache::add方法的作用和Memcache::set方法类似，
     * 区别是如果 Memcache::add方法的返回值为false，
     * 表示这个key已经存在，而Memcache::set方法则会直接覆写。
     */
    public function add($key,$val,$time=0){
    	return $this->handler->add($key, $val, 0, $time);
    }
    
    /**
     * Memcache::get方法的作用是获取一个key值，
     * Memcache::get方法有一个参数，表示key。
     */
    public function get($key){
    	return $this->handler->get($key);
    }
    
    
    /**
     * Memcache::delete方法的作用是删除一个key值，
     * Memcache::delete方法有两个参数，
     * 第一个参数表示key，
     * 第二个参数可选，表示删除延迟的时间。
     */
    public function rm($key, $time=0){
    	return $this->handler->delete($key, $time);
    }
    
    
    /**
     *  用于获取一个服务器的在线/离线状态
     */
    public function getServerStatus(){
    	return $this->handler->getServerStatus($this->options['host'], $this->options['port']);
    }
    
    /**
     * 缓存服务器池中所有服务器统计信息
     */
    public function getExtendedStats(){
    	return $this->handler->getExtendedStats();
    }
}
