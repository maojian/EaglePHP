<?php
/**
 * 数据缓存中间层
 * @author maojianlw@139.com
 * @since 2012-05-08
 */

class Cache{
 
   protected $connected = false;
   
   protected $options = array();
   
   protected $handler = null; // 操作句柄
   
   const APC = 'apc';
   
   const FILE = 'file';
   
   const MEMCACHE = 'memcache';
    
    /**
     * 获取缓存对象实例
     * @param string $type
     * @param array $options
     */
    public static function getInstance($type='', $options=array()){
         $type = (empty($type)) ? getCfgVar('cfg_cache_type') : $type;
         $class = 'Cache'.ucfirst($type);
         if(!class_exists($class))
         {
             throw_exception(language('SYSTEM:class.not.exists', array($class, 'getInstance')));
         }
         $cache_class = new $class($options);
         return $cache_class;
    }
    
    /**
     * 读取缓存数据
     * @param string $name
     */
    public function __get($name){
         return $this->get($name);
    }
    
    /**
     * 写入缓存数据
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value){
         $this->set($name, $value);
    }
    
    /**
     * 根据变量名销毁缓存数据
     * @param string $name
     */
    public function __unset($name){
         $this->rm($name);
    }
 
}