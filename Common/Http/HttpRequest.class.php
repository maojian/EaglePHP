<?php
/**
 * 客户端请求对象
 * @author maojianlw@139.com
 * @link www.eaglephp.com
 * @version 2.3
 */

class HttpRequest
{

    /**
     * server host信息
     * 
     * @var string
     */
    protected static $_hostInfo;
    
    
    /**
     * client 浏览器语言
     * 
     * @var string
     */
    protected static $_langauge;
    
    
    
    /**
     * 获取GET方式请求的数据
     * @param string $name 变量名
     * @param mixed $defaultVal 默认值
     * @param bool $filter 是否清除XSS
     * @return mixed
     */
    public static function getGet($name = null, $defaultVal=null, $filter = true)
    {
        if($name === null) $value = $_GET;
        elseif(isset($_GET[$name])) $value = $_GET[$name];
        else $value = $defaultVal;
        return $filter ? Filter::runMagicQuote($value) : $value;
    }
    
    
    /**
     * 设置get方法中的变量
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function setGet($name=null, $value=null)
    {
        if($name === null)
        {
            $_GET = $value;
        }
        else
        {
            if($value === null) unset($_GET[$name]);
            else $_GET[$name] = $value;
        }
        return true;
    }
    
    
    
    /**
     * 获取POST方式请求的数据
     * @param string $name 变量名
     * @param mixed $defaultVal 默认值
     * @param bool $filter 是否清除XSS
     * @return mixed
     */
    public static function getPost($name = null, $defaultVal=null, $filter = true)
    {
        if($name === null) $value = $_POST;
        elseif(isset($_POST[$name])) $value = $_POST[$name];
        else $value = $defaultVal;
        return $filter ? Filter::runMagicQuote($value) : $value;
    }
    
    
    /**
     * 设置post变量
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function setPost($name=null, $value=null)
    {
        if($name === null)
        {
            $_POST = $value;
        }
        else
        {
            if($value === null) unset($_POST[$name]);
            else $_POST[$name] = $value;
        }
        return true;
    }
    
    
    /**
     * 获取客户端请求的数据
     * @param string $name 变量名
     * @param mixed $defaultVal
     * @param bool $filter 是否清除XSS
     * @return mixed
     */
    public static function getRequest($name = null, $defaultVal=null, $filter = true)
    {
        if($name === null) $value = $_REQUEST;
        elseif(isset($_REQUEST[$name])) $value = $_REQUEST[$name];
        else $value = $defaultVal;
        return $filter ? Filter::runMagicQuote($value) : $value;
    }
    
    
    /**
     * 设置request中的变量
     * 
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function setRequest($name=null, $value=null)
    {
        if($name === null)
        {
            $_REQUEST = $value;
        }
        else
        {
            if($value === null) unset($_REQUEST[$name]);
            else $_REQUEST[$name] = $value;
        }
        return true;
    }
    
    
    
	/**
     * 获取客户端cookie数据
     * @param string $name 变量名
     * @param mixed $defaultVal 默认值
     * @return mixed
     */
    public static function getCookie($name = null, $defaultVal=null, $filter = true)
    {
        if($name === null) return Cookie::getAll($filter);
        return Cookie::exists($name) ? Cookie::get($name, $filter) : $defaultVal;
    }
    
    
    /**
     * 设置cookie中的变量
     * @param string $name
     * @param string $value
     * @return
     */
    public static function setCookie($name, $value = null)
    {
        if($value === null) Cookie::delete($name);
        else Cookie::set($name, $value);
        return true;
    }
    
    
    
	/**
     * 获取客户端上传的文件数据
     * @param string $name 变量名
     * @param mixed $defaultVal 默认值
     * @return mixed
     */
    public static function getFile($name = null)
    {
        if($name === null) return $_FILES;
        return isset($_FILES[$name]) ? $_FILES[$name] : null;
    }
    
    
	/**
     * 获取客户端产生的会话数据
     * @param string $name 变量名
     * @param mixed $defaultVal 默认值
     * @return mixed
     */
    public static function getSession($name = null, $defaultVal=null)
    {
        if($name === null) return Session::getAll();
        return Session::exists($name) ? Session::get($name) : $defaultVal;
    }
    
    
	/**
     * 设置session中的变量
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function setSession($name, $value=null)
    {
        return Session::set($name, $value);
    }
    
    
	/**
     * 获取Server数据
     * @param string $name 变量名
     * @param mixed $defaultVal 默认值
     * @return mixed
     */
    public static function getServer($name = null, $defaultVal=null)
    {
        if($name === null) return $_SERVER;
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $defaultVal;
    }
    
    
    /**
     * 设置server中的变量
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function setServer($name=null, $value = null)
    {
        if($name === null)
        {
            $_SERVER = $value;
        }
        else
        {
            if($value === null) unset($_SERVER[$name]);
            else $_SERVER[$name] = $value;
        }
        return true;
    }
    
    
    
	/**
     * 获取Env数据
     * @param string $name 变量名
     * @param mixed $defaultVal 默认值
     * @return mixed
     */
    public static function getEnv($name = null, $defaultVal=null)
    {
        if($name === null) return $_ENV;
        return isset($_ENV[$name]) ? $_ENV[$name] : $defaultVal;
    }
    
    
    /**
     * 设置env中的变量
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function setEnv($name=null, $value=null)
    {
        if($name === null)
        {
            $_ENV = $value;
        }
        else
        {
            if($value === null) unset($_ENV[$name]);
            else $_ENV[$name] = $value;
        }
        return true;
    }
    
    
    /**
     * 获取请求链接协议
     * @return string
     */
    public static function getScheme()
    {
        return (self::getServer('HTTPS') == 'on') ? 'https' : 'http';
    }
    
    
    /**
     * 获取通信协议和版本
     * @return string
     */
    public static function getProtocol()
    {
        return self::getServer('SERVER_PROTOCOL', 'HTTP/1.0');
    }
    
    
    /**
     * 获取客户端IP地址
     * @return string
     */
    public static function getClientIP()
    {
        return get_client_ip();
    }
    
    
    /**
     * 获取客户端IP所在地
     * @param string $ip
     * @return string
     */
    public static function getIpLocation($ip = null)
    {
        if($ip === null) $ip = self::getClientIP();
        return IpLocation::getlocation($ip);
    }
    
    
    /**
     * 获取客户端请求方法
     * @return string
     */
    public static function getRequestMethod()
    {
        return strtoupper(self::getServer('REQUEST_METHOD'));
    }
    
    
    /**
     * 获取客户端是否以Ajax方式请求
     * @return bool
     */
    public static function isAjaxRequest()
    {
        return !strcasecmp(self::getServer('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest');
    }
    
    
    /**
     * 是否使用HTTPS安全链接
     * @return bool
     */
    public static function isSecure()
    {
        return !strcasecmp(self::getServer('HTTPS'), 'on');
    }
    
    
    /**
     * 是否是get请求方式
     * @return bool
     */
    public static function isGet()
    {
        return !strcasecmp(self::getRequestMethod(), 'GET');
    }
    
    
	/**
     * 是否是post请求方式
     * @return bool
     */
    public static function isPost()
    {
        return !strcasecmp(self::getRequestMethod(), 'POST');
    }
    
    
	/**
     * 是否是put请求方式
     * @return bool
     */
    public static function isPut()
    {
        return !strcasecmp(self::getRequestMethod(), 'PUT');
    }
    
    
    
    /**
     * 获取host
     * @return string
     */
    public static function getHttpHost()
    {
        return self::getServer('HTTP_HOST', '');
    }
    
    
    /**
     * 获取服务名
     * @return string
     */
    public static function getServerName()
    {
        return self::getServer('SERVER_NAME');
    }
    
    
    /**
     * 获取服务端口
     * @return string
     */
    public static function getServerPort()
    {
        $defaultPort = self::isSecure() ? 443 : 80;
        return self::getServer('SERVER_PORT', $defaultPort);
    }
    
    
    /**
     * 获取用户主机名
     * @return string
     */
    public static function getRemoteHost()
    {
        return self::getServer('REMOTE_HOST');
    }
    
    
	/**
     * 获取用户主机端口
     * @return string
     */
    public static function getRemotePort()
    {
        return self::getServer('REMOTE_PORT');
    }
    
    
    /**
     * 获取客户端来源URL
     * @return string
     */
    public static function getHttpReferer()
    {
        return self::getServer('HTTP_REFERER');
    }
    
    
    
    /**
     * 获取用户代理
     * @return string
     */
    public static function getHttpUserAgent()
    {
        return self::getServer('HTTP_USER_AGENT');
    }
    
    
    /**
     * 获取客户端MIME类型
     * @return string
     */
    public static function getAcceptTypes()
    {
        return self::getServer('HTTP_ACCEPT');
    }
    
    
    /**
     * 获取用户的数据编码方式
     * @return string
     */
    public static function getAcceptEncoding()
    {
        return self::getServer('HTTP_ACCEPT_ENCODING');
    }
    
    
    /**
     * 获取客户端接受的语言格式
     * @return string
     */
    public static function getAcceptLanguage()
    {
        if(!self::$_langauge)
        {
            $_language = explode(',', self::getServer('HTTP_ACCEPT_LANGUAGE'));
            self::$_langauge = (isset($_language[0]) && $_language[0]) ? $_language[0] : 'zh_cn';
        }
        return self::$_langauge;
    }
    
    
    /**
     * 获取主机信息
     * @return string
     */
    public static function getHostInfo()
    {
        if(!self::$_hostInfo){
            $http = self::getScheme();
            if(($host = self::getHttpHost()) != null)
            {
                self::$_hostInfo = "{$http}://{$host}";
            }
            else if(($host = self::getServerName()) != null)
            {
                self::$_hostInfo = "{$http}://{$host}";
                if(($port = self::getServerPort()) != null) self::$_hostInfo .= ':'.$port;
            }else 
            {
                throw_exception(__CLASS__.' '.__FUNCTION__.': get host info failed.');
            }
        }
        return self::$_hostInfo;
    }
    
    
	/**
     * 获取Apache请求的头信息
     * 
     * @return array
     */
    public static function getApacheRequestHeader()
    {
        if(function_exists('apache_request_headers'))
        {
            return apache_request_headers();
        }
        return false;
    }
    
}