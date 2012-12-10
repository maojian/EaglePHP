<?php

/**
 * Alternative PHP Cache(可选PHP缓存)
 * @author maojianlw@139.com
 * @since 2012-08-02
 */

class CacheApc extends Cache{
    
    public function __construct($options)
    {
        if(!extension_loaded('apc'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('apc')));
        }
        if($options) $this->options = $options;
        $this->options['expire'] = $this->options['expire'] ? $this->options['expire'] : getCfgVar('cfg_cache_time');
    }
    
    
    /**
     * 缓存一个变量到数据存储
     * 仅仅是缓存变量不存在的情况下缓存变量到数据存储中
     * @param string $key 变量名称
     * @param mixed $value 值
     * @param int $ttl 生存时间
     * @return bool
     */
    public function add($key, $value, $ttl=0)
    {
        return apc_add($key, $value, $ttl);
    }
    
    /**
     * 缓存一个变量到APC中，如果存在则覆盖
     * @param string $key 变量名称
     * @param mixed $value 值
     * @param int $ttl 生存时间
     * @return bool
     */
    public function set($key, $value, $expire=null)
    {
        if(is_null($expire)){
            $expire = $this->options['expire'];
        }
        return apc_store($key, $value, $expire);
    }
    
    /**
     * 从缓存中取出存储的变量
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return apc_fetch($key);
    }
    
    /**
     * 检查一个或多个key名是否存在，多个key用array数组传递
     * @param mixed $keys
     * @return mixed
     */
    public function exists($keys)
    {
        return apc_exists($keys);
    }
    
    /**
     * 删除APC缓存中的变量
     * @param string $key
     * @return bool
     */
    public function rm($key)
    {
        return apc_delete($key);
    }
    
    /**
     * 清除用户或者系统缓存（默认清除系统缓存），如要清除用户缓存则cache_type为user
     * @return bool
     */
    public function clear($cache_type = '')
    {
        return apc_clear_cache($cache_type);
    }
    
    /**
     * 返回APC的共享内存分配信息
     * @return array
     */
    public function getSmaInfo($limited = false)
    {
        return apc_sma_info($limited);
    }
    
    /**
     * 从APC的数据存储和检索缓存的信息元数据。
     * @param string $cache_type
     * @return array
     */
    public function getCacheInfo($cache_type = '')
    {
        return apc_cache_info($cache_type);
    }
    
    
}