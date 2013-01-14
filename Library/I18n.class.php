<?php
/**
 * 多语言国际化处理类
 * @author maojianlw@139.com
 * @since 2012-08-01
 * @copyright EaglePHP开发团队
 */

class I18n{
    
    private static $language, $default, $suffix, $dir, $_messages = array();
    
    public static function getMessage($message, $params = array())
    {
        self::_setConfig();
        $message = self::_transform($message);
        return empty($params) ? $message : self::sprintfStr($message, $params);
    }
    
    private static function _transform($message)
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
        if(!isset(self::$_messages[$path])){
            self::$_messages[$path] = require($path);
        }
        return isset(self::$_messages[$path][$key]) ? self::$_messages[$path][$key] : $message;
    }
    
    
    private static function sprintfStr($message, $params)
    {
        $format = '\''.implode('\',\'', $params).'\'';
        eval("\$message = sprintf('{$message}', {$format});");
        return $message;
    }
    
    public static function getLangFile($module='')
    {
         return DATA_DIR.self::$dir.__DS__.self::$language.($module ? __DS__.strtolower($module) : '');
    }
    
    private static function _setConfig()
    {
        self::$dir = 'I18n';
        self::$language = 'zh_cn';
        self::$default = 'message';
        self::$suffix = '.lang.php';
    }
    
}