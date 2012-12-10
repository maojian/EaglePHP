<?php
/**
 * cookie操作类
 * @author maojianlw@139.com
 * @since 2012-08-03
 */
class Cookie{

    /**
     * 设置cookie
     * 
     * @param string $name cookie名称
     * @param string $value cookie值
     * @param bool $encode 是否编码
     * @param string|int $expire 过期时间，默认为空即会话cookie，随着会话结束失效
     * @param string $path cookie保存路径
     * @param string $domain cookie所属域
     * @param bool $secure	是否采用安全连接
     * @param bool $httponly 是否可通过客户端脚本访问，默认为false即客户端脚本可以访问
     * @return bool 设置成功返回true，否则false
     */
    public static function set($name, $value=null, $encode=false, $expire=null, $path=null, $domain=null, $secure=false, $httponly=false)
    {
        $encode && $value && $value = base64_encode(serialize($value));
        $path = $path ? $path : '/';
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);      
    } 
    
    /**
     * 获取cookie
     * @param string $name cookie名称
     * @param bool $decode 是否对cookie值解码
     * @param bool $filter 是否清除XSS脚本攻击代码
     */
    public static function get($name, $decode = false, $filter = true) 
    {
        if (self::exists($name)) 
        {
            $value = $_COOKIE[$name];
            $value && $decode && $value = unserialize(base64_decode($value));
            $value = $filter ? Filter::runMagicQuote($value) : $value;
            return $value;
        }
        return false;
    }
    
    /**
     * 检查cookie是否存在
     * @param string $name cookie名称
     * @return bool
     */
    public static function exists($name)
    {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * 删除指定的cookie
     * @param string $name
     * @return bool
     */
    public static function delete($name)
    {
        if(self::exists($name)){
            self::set($name, '', false, -3600);
            unset($_COOKIE[$name]);
        }
        return true;
    }
    
    /**
     * 清除所有的cookie
     */
    public static function clear()
    {
        $cookieArr = self::getAll();
        foreach ($cookieArr as $name=>$value)
        {
            self::delete($name);
        }
        return true;
    }
    
	/**
	 * 获取所有cookie
	 * 
	 * @param bool $filter 是否清除XSS脚本攻击代码
	 * @return array
	 */
	public static function getAll($filter = true)
	{
	    return $filter ? Filter::runMagicQuote($_COOKIE) : $_COOKIE;
	}
    
    
    
}