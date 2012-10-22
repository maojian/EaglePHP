<?php
/**
 * 用户及系统行为检测类
 * @author maojianlw@139.com
 * @since 2012-08-06
 */
class Behavior {
    
    /**
     * 防刷机制
     */
    public static function checkRefresh()
    {
        $cfgRefreshTime = intval(getCfgVar('cfg_refresh_time'));
        if($_SERVER['REQUEST_METHOD'] == 'GET' && $cfgRefreshTime > 0)
        {            
            $pageUniqid = '_last_access_time_'.md5($_SERVER['REQUEST_URI']);
            // 检查页面刷新间隔
            if(time() < Cookie::get($pageUniqid))
            {
                // 页面刷新读取浏览器缓存
                header('HTTP/1.1 304 Not Modified');
                exit;
            }
            else
            {
                // 缓存当前页面地址和访问时间
                $time = $_SERVER['REQUEST_TIME']+$cfgRefreshTime;
                Cookie::set($pageUniqid, $time, false, $time);
            }
        }
    }
    
    
    
}