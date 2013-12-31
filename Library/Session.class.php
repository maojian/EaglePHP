<?php

/**
 * session配置管理类
 * @author maojianlw@139.com
 * @since 2012-08-03
 * @link www.eaglephp.com
 * @copyright EaglePHP Group
 */

class Session {


    public static function start()
    {
        return session_start();
    }

    /**
     * 设置或获取session的名称
     * @param string $name
     * @return bool | string
     */
    public static function name($name = null)
    {
        return isset($name) ? session_name($name) : session_name();
    }

    /**
     * 设置或者获取当前session的id
     * @param string $id
     * @return $id
     */
    public static function id($id = null)
    {
        return isset($id) ? session_id($id) : session_id();
    }

    /**
     * 设置或者获取session的保存路径
     * @param string $path
     */
    public static function path($path = null)
    {
        return isset($path) ? session_save_path($path) : session_save_path();
    }

    /**
     * 设置或获取session对象反序列化时的回调函数
     * @param string $callback
     * @return string
     */
    public static function callback($callback = null)
    {
        return isset($callback) ? ini_set('unserialize_callback_func', $callback) : ini_get('unserialize_callback_func');
    }

    /**
     * 设置或获取session是否使用cookie
     * @param string $callback
     * @return string
     */
    public static function useCookie($useCookie = null)
    {
        return isset($useCookie) ? ini_set('session.use_cookies', $useCookie ? 1 : 0) : (ini_get('session.use_cookies') ? true : false);
    }

    /**
     * 获取当前session中的值
     * @param string $name
     * @return string
     */
    public static function get($name)
    {
        if(self::exists($name))
        {
            return $_SESSION[$name];
        }
        return null;
    }

    /**
     * 获取整个session
     */
    public static function getAll()
    {
        return $_SESSION;
    }

    /**
     * 设置当前session中的值
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function set($name, $value)
    {
        if($value === null)
        {
            unset($_SESSION[$name]);
        }
        else
        {
            $_SESSION[$name] = $value;
        }
        return true;
    }

    /**
     * 删除session中的值
     * @param string $name
     * @return bool
     */
    public static function delete($name)
    {
        return self::set($name, null);
    }


    /**
     * 检查session的值是否存在
     * @param string $name
     * @return mixed
     */
    public static function exists($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * 设置或者返回session是否使用use_trans_sid
     * @param string $useTranSID
     * @return bool
     */
    public static function useTranSID($useTranSID = null)
    {
        return isset($useTranSID) ? ini_set('session.use_trans_sid', $useTranSID ? 1 : 0) : (ini_get('session.use_trans_sid') ? true : false);
    }

    /**
     * 设置或者返回session cookie domain
     *
     */
    public static function cookieDomain($cookieDomain = null)
    {
        return isset($cookieDomain) ? ini_set('session.cookie_domain', $cookieDomain) : ini_get('session.cookie_domain');
    }

    /**
     * 设置或者返回session gc max life time
     * @param string | int $gcMaxLifeTime
     */
    public static function gcMaxLifeTime($gcMaxLifeTime = null)
    {
        if(isset($gcMaxLifeTime) && is_int($gcMaxLifeTime) && $gcMaxLifeTime > 0)
        {
            return ini_set('session.gc_maxlifetime', $gcMaxLifeTime);
        }
        else
        {
            return ini_get('session.gc_maxlifetime');
        }
    }


    /**
     * 设置或者返回session session.cookie_lifetime
     * @param string | int $cookieLifeTime
     */
    public static function cookieLifeTime($cookieLifeTime = null)
    {
        if(isset($cookieLifeTime) && is_int($cookieLifeTime) && $cookieLifeTime > 0)
        {
            return ini_set('session.cookie_lifetime', $cookieLifeTime);
        }
        else
        {
            return ini_get('session.cookie_lifetime');
        }
    }

    /**
     * 设置或者获取session gc_probability 值
     * @param int $gcProbability
     * @return mixed
     */
    public static function gcProbability($gcProbability = null)
    {
        if(isset($gcProbability) && is_int($gcProbability) && $gcProbability>=1 && $gcProbability <= 100)
        {
            return ini_set('session.gc_probability', $gcProbability);
        }
        else
        {
            return ini_get('session.gc_probability');
        }
    }

    /**
     * 获取当前session文件名
     */
    public static function getFileName()
    {
        return self::path().__DS__.'sess_'.self::id();
    }

    /**
     * 设置或者获取当前session module
     * @param string $module
     * @return mixed
     */
    public static function module($module = null){
        return isset($module) ? session_module_name($module) : session_module_name();
    }

    /**
     * 关闭session写入
     */
    public static function writeClose()
    {
        session_write_close();
        return true;
    }
     
    /**
     * session提交。alias：session_write_close
     */
    public static function commit()
    {
        session_commit();
        return true;
    }
     
    /**
     * 设置用户级的会话存储功能
     * @param callback $open
     * @param callback $close
     * @param callback $read
     * @param callback $write
     * @param callback $destroy
     * @param callback $gc
     */
    public static function setSaveHandler($open, $close, $read, $write, $destroy, $gc)
    {
        return session_set_save_handler($open, $close, $read, $write, $destroy, $gc);
    }
     
    /**
     * 设置session cookie参数
     * @param int $lifetime
     */
    public static function setCookieParams($lifetime, $path = '/', $domain = null, $secure = false, $httponly = false)
    {
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        return true;
    }
     
     
    /**
     * 销毁会话
     */
    public static function destory()
    {
        session_unset();
        session_destroy();
    }
     

    /**
     * 初始化 session 配置
     */
    public static function init(){
        $session_name = self::name();
        //$session_id = HttpRequest::getRequest($session_name);
        
        switch (SESSION_SAVE_TYPE)
        {
            case 'file':
                // session 数据保存目录，仅 SESSION_SAVE_TYPE 为 file 时生效
                $session_dir = DATA_DIR.getCfgVar('cfg_session_dir');
                if(!is_dir($session_dir)) mk_dir($session_dir);
                self::path($session_dir);
                break;
            
            case 'memcache':
                SessionMemcache::init();
                break;
                
            case 'table':
                SessionTable::init();
                break;

            default:
                throw_exception(language('SYSTEM:session.type.not.exists', array(SESSION_SAVE_TYPE)));
                break;
        }

        // 利用链接传递session_id
        //if($session_id) self::id($session_id);
        $expire = SESSION_LIFE_TIME;

        // 设置垃圾回收最大生存时间，超过设定时间，gc就认为是垃圾文件。
        if($expire != 0)
        {
            self::gcMaxLifeTime($expire);
            //self::cookieLifeTime($expire);
            //self::gcProbability(1);
            //ini_set('session.gc_divisor', 1); //  默认100。值越小，概率越大。
        }

        //session_cache_limiter('private, must-revalidate');
        $sid = Cookie::get($session_name);
        if(empty($sid))
        {
            //self::setCookieParams($expire);
            self::start();
        }
        else
        {
            self::start();
            //Cookie::set($session_name, $sid, false, time() + $expire);
        }
         
    }
     
    /**
     * 验证客户端cookie是否有效，预防cookie id被劫持、非法利用。
     * 利用客户端信息和cookie相绑定
     */
    public function checkClientCookie()
	{
        $name = 'cookie_verification_key';

        $key = md5(//$_SERVER['HTTP_USER_AGENT'].
                    HttpRequest::getServer('HTTP_COOKIE').
                    HttpRequest::getServer('REMOTE_ADDR')
                );

        $value = self::get($name);
        if(empty($value))
        {
            self::set($name, $key);
        }

        if(self::get($name) != $key)
        {
            self::destory();
            return false;
        }
        return true;
    }


}
