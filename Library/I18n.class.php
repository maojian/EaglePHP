<?php
/**
 * 多语言国际化处理类
 * @author maojianlw@139.com
 * @since 2012-08-01
 * @copyright EaglePHP开发团队
 */

class I18n
{
    
    /**
     * 当前语言
     * 
     * @var string
     */
    protected static $language;
    
    
    /**
     * 默认语言包
     * 
     * @var string
     */
    protected static $default;
    
    
    /**
     * 语言文件后缀
     * 
     * @var string
     */
    protected static $suffix;
    
    
    /**
     * 语言包目录
     * 
     * @var string
     */
    protected static $dir;
    
    
    /**
     * 语言包文本信息数组
     * 
     * @var array
     */
    protected static $_messages = array();
    
    
    public static function getMessage($message, $params = array())
    {
        self::_setConfig();
        $message = self::_transform($message);
        return empty($params) ? $message : vsprintf($message, $params);
    }
    
    
    protected static function _transform($message)
    {
        $module = $file = $key = '';
        $_message = $message;
        if(strpos($message, ':')) list($module, $_message) = explode(':', $_message);
        if(strpos($message, '.')) list($file, $key) = explode('.', $_message);
        $path = self::getLangFile($module);
        if(is_file($path.__DS__.self::$default.self::$suffix))
        {
            $path .= __DS__.self::$default.self::$suffix;
            $key = $_message;
        }
        elseif(is_file($path.__DS__.$file.self::$suffix))
        {
            $path .= __DS__.$file.self::$suffix;
        }
        else
        {
            return $message;
        }
        if(!isset(self::$_messages[$path]))
        {
            self::$_messages[$path] = require($path);
        }
        return isset(self::$_messages[$path][$key]) ? self::$_messages[$path][$key] : $message;
    }
    
    
    public static function getLangFile($module='')
    {
         return DATA_DIR.self::$dir.__DS__.self::$language.($module ? __DS__.strtolower($module) : '');
    }
    
    
    protected static function _setConfig()
    {
        self::$dir = 'I18n';
        self::$language = strtolower(HttpRequest::getAcceptLanguage());
        self::$default = 'message';
        self::$suffix = '.lang.php';
    }
    
}