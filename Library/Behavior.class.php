<?php
/**
 * 用户及系统行为检测类
 * @author maojianlw@139.com
 * @since 2012-08-06
 */
class Behavior 
{

    
    /**
     * 正则匹配路由
     * 
     * @return bool
     */
    public static function checkRoute()
    {
        if(!__CLI__ && isset($GLOBALS['URL_RULES']))
        {
            $rules = $GLOBALS['URL_RULES'];
            $pathinfo = HttpRequest::getServer('PATH_INFO');
            if($pathinfo == null) return false;
            foreach ($rules as $k=>$v)
            {
                $path = preg_replace($k, $v, $pathinfo, 1, $count);
                if($count)
                {
                    $count && $_SERVER['PATH_INFO'] = $path;
                    break;
                }
            }
            return true;
        }
        return false;
    }
    
    
    /**
     * 防刷机制
     * 
     * @return void
     */
    public static function checkRefresh()
    {
        $cfgRefreshTime = intval(getCfgVar('cfg_refresh_time'));
        //$cfgRefreshTime = 10;
        if($cfgRefreshTime > 0 && HttpRequest::getRequestMethod() == 'GET')
        {            
            $pageUniqid = '_last_access_time_'.md5(HttpRequest::getServer('REQUEST_URI'));
            if(Date::getTimeStamp() < Cookie::get($pageUniqid)) // 检查页面刷新间隔
            {
                HttpResponse::sendHeader(304); // 页面刷新读取浏览器缓存
            }
            else
            {
                $time = HttpRequest::getServer('REQUEST_TIME') + $cfgRefreshTime; // 缓存当前页面地址和访问时间
                Cookie::set($pageUniqid, $time, false, $time);
            }
        }
    }
    /*
	    header('Last-Modified: '.Date::format('D,d M Y H:i:s').' GMT');
        header('Cache-Control: max-age=600');
        header('Expires: '.Date::format('D,d M Y H:i:s', Cookie::get($pageUniqid)).' GMT');
        header('Date: '.Date::format('D,d M Y H:i:s').' GMT');
     */
    
}