<?php

/**
 * Redis缓存类
 * @author maojianlw@139.com
 * @since 2012-08-02
 */

class CacheRedis extends Cache{
    
    public function __construct($options)
    {
        if(!extension_loaded('redis'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('redis')));
        }

        if(!isset($options['host'])) $options['host'] = getCfgVar('cfg_redis_host') ? getCfgVar('cfg_redis_host') : '127.0.0.1';
        if(!isset($options['port'])) $options['port'] = getCfgVar('cfg_redis_port') ? getCfgVar('cfg_redis_port') : 6379;
        if(!isset($options['persistent'])) $options['persistent'] = false;
        if(!isset($options['timeout'])) $options['timeout'] = false;
        if(!isset($options['expire'])) $options['expire'] = (int)getCfgVar('cfg_cache_time');
        
        $this->options = $options;
        $function = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new Redis;
        $this->connected = $options['timeout'] === false ? $this->handler->$function($options['host'], $options['port']) : $this->handler->$function($options['host'], $options['port'], $options['timeout']);
    }
    
    
    /**
     * 是否连接到redis服务器
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }
    
    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        return $this->handler->get($name);
    }
    
    /**
     * 写入缓存
     * @param string $name  缓存变量名
     * @param mixed $value  缓存变量值
     * @param int $expire
     * @return bool
     */
    public function set($name, $value, $expire = null)
    {
        if(is_null($expire))
        {
            $expire = $this->options['expire'];
        }
        if(is_int($expire) && $expire !== 0)
        {
            $result = $this->setex($name, $expire, $value);
        }else
        {
            $result = $this->handler->set($name, $value); // 写入key 和 value（string值）
        }
        return $result;
    }
    
    /**
     * 带生存时间的写入值
     * @param string $name
     * @param int $expire
     * @param mixed $value
     * @return bool
     */
    public function setex($name, $expire, $value)
    {
        return $this->handler->setex($name, $expire, $value); 
    }
    
    /**
     * 同时给多个key赋值（redis版本1.1以上才可以用）
     * @param array $keys
     * @return bool
     */
    public function mset($keys)
    {
        return $this->handler->mset($keys);
    }
    
    
    
    /**
     * 删除缓存
     * @param mixed $name 缓存变量名，可为数组
     */
    public function rm($name)
    {
        return $this->handler->delete($name); 
    }
    
    /**
     * 清空当前数据库
     */
    public function clear()
    {
        return $this->handler->flushDB();
    }
    
    /**
     * 清空所有数据库
     */
    public function clearAll()
    {
        return $this->handler->flushAll();
    }
    
    /**
     * 返回满足给定pattern的所有key
     * @param string $keys
     * @return mixed
     */
    public function keys($keys = '*')
    {
        return $this->handler->keys($keys);
    }
    
    /**
     * 返回redis的版本信息等详情
     */
    public function info()
    {
        return $this->handler->info();
    }
    
    /**
     * 返回key的类型值
     * string: Redis::REDIS_STRING
     * set: Redis::REDIS_SET
     * list: Redis::REDIS_LIST
     * zset: Redis::REDIS_ZSET
     * hash: Redis::REDIS_HASH
     * other: Redis::REDIS_NOT_FOUND
     * @param string $name
     * @return string
     */
    public function type($name)
    {
        return $this->handler->type($name);
    }
    
    /**
     * 查看连接状态
     * @return mixed
     */
    public function ping()
    {
        return $this->handler->ping();
    }
    
    /**
     * 判断key是否存在。存在 true 不在 false
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return $this->handler->exists($name);
    }
    
    /**
     * 向hash中添加元素
     * @param string $name
     * @param string $key
     * @param mixed $value
     */
    public function hSet($name, $key, $value)
    {
        return $this->handler->hSet($name, $key, $value);
    }
    
    /**
     * 返回hash中key对应的value
     * @param string $name
     * @param string $key
     */
    public function hGet($name, $key)
    {
        return $this->handler->hGet($name, $key);
    }
    
    /**
     * 返回指定名称的hash中元素个数
     * @param string $name
     * @return int
     */
    public function hLen($name)
    {
        return $this->handler->hLen($name);
    }
    
    /**
     * 删除指定名称的hash中键的域
     * @param string $name
     * @param string $key
     * @return bool
     */
    public function hDel($name, $key)
    {
        return $this->handler->hDel($name, $key);
    }
    
    /**
     * 返回指定名称的hash中所有键
     * @param string $name
     * @return bool
     */
    public function hKeys($name)
    {
        return $this->handler->hKeys($name);
    }
    
    /**
     * 返回指定名称的hash中所有键对应的value
     * @param string $name
     */
    public function hVals($name)
    {
        return $this->handler->hVals($name);
    }
    
    /**
     * 返回指定名称的hash中所有的键（field）及其对应的value
     * @param string $name
     */
    public function hGetAll($name)
    {
        return $this->handler->hGetAll($name);
    }
    
    /**
     * 指定名称的hash中是否存在指定键名字的域
     * @param string $name
     * @param string $key
     */
    public function hExists($name, $key)
    {
        return $this->handler->hExists($name, $key);
    }
    
    /**
     * 将指定名称的hash中键的value增加指定的数
     * @param string $name
     * @param string $key
     * @param int $num
     */
    public function hIncrBy($name, $key, $num)
    {
        return $this->handler->hIncrBy($name, $key, $num);
    }
    
    /**
     * 向指定名称的键hash中批量添加元素
     * @param string $name
     * @param array $values
     */
    public function hMSet($name, array $values)
    {
        return $this->handler->hMSet($name, $values);
    }
    
    /**
     * 返回指定名称的hash中的键对应的value
     * @param string $name
     * @param array $keys
     */
    public function hMGet($name, array $keys)
    {
        return $this->handler->hMGet($name, $keys);
    }
    
    /**
     * 选择从服务器
     * @param string $host
     * @param int $port
     */
    public function slaveof($host, $port)
    {
        return $this->handler->slaveof($host, $port);
    }
    
    /**
     * 返回原来key中的值，并将value写入key
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function getSet($name, $value)
    {
        return $this->handler->getSet($name, $value);
    }
    
	/**
     * 名称为key的string的值在后面加上value
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function append($name, $value)
    {
        return $this->handler->append($name, $value);
    }
    
    /**
     * 得到key的string的长度
     * @param string $name
     */
    public function strlen($name)
    {
        return $this->handler->strlen($name);
    }
    
    /**
     * 查看现在数据库有多少key
     * @return int
     */
    public function dbSize()
    {
        return $this->handler->dbSize();
    }
    
    /**
     * 向名称为key的set中添加元素value,如果value存在，不写入，return false
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function sAdd($name, $value)
    {
        return $this->handler->sAdd($name, $value);
    }
    
    /**
     * 删除名称为key的set中的元素value
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function sRem($name, $value)
    {
        return $this->handler->sRem($name, $value);
    }
    
    /**
     * 返回名称为key的set的所有元素
     * @param string $name
     * @return array
     */
    public function sGetMembers($name)
    {
        return $this->handler->sGetMembers($name);
    }
    
	/**
     * 名称为key的集合中查找是否有value元素，有ture 没有 false
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function sIsMember($name, $value)
    {
        return $this->handler->sIsMember($name, $value);
    }
    
    /**
     * 返回名称为key的set的元素个数
     * @param unknown_type $name
     */
    public function sSize($name)
    {
        return $this->handler->sSize($name);
    }
    
    /**
     * 排序，分页等
                       参数
       'by' => 'some_pattern_*',
       'limit' => array(0, 1),
       'get' => 'some_other_pattern_*' or an array of patterns,
       'sort' => 'asc' or 'desc',
       'alpha' => TRUE,
       'store' => 'external-key'
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function sort($name, $params = array())
    {
        return $this->handler->sort($name, $params);
    }
    
    /**
     * 在指定名称的list左边（头）添加一个值为value的 元素
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function lPush($name, $value)
    {
        return $this->handler->lPush($name, $value);
    }
    
	/**
     * 在指定名称的list右边（尾）添加一个值为value的 元素
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function rPush($name, $value)
    {
        return $this->handler->rPush($name, $value);
    }
    
    
	/**
     * 在名称为key的list左边(头)添加一个值为value的元素,如果value已经存在，则不添加
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function lPushx($name, $value)
    {
        return $this->handler->lPushx($name, $value);
    }
    
	/**
     * 在名称为key的list右边（尾）添加一个值为value的元素,如果value已经存在，则不添加
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function rPushx($name, $value)
    {
        return $this->handler->rPushx($name, $value);
    }
    
	/**
     * 输出名称为key的list左(头)起的第一个元素，删除该元素
     * @param string $name
     * @return mixed
     */
    public function lPop($name)
    {
        return $this->handler->lPop($name);
    }
    
	/**
     * 输出名称为key的list右（尾）起的第一个元素，删除该元素
     * @param string $name
     * @return mixed
     */
    public function rPop($name)
    {
        return $this->handler->rPop($name);
    }
    
	/**
     * 返回名称为key的list有多少个元素
     * @param string $name
     * @return int
     */
    public function lSize($name)
    {
        return $this->handler->lSize($name);
    }
    
    
}