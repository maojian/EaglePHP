<?php
/**
 * 系统常量配置文件
 * 
 * @author maojianlw@139.com
 * @since 2.3 - 2012-09-21
 * @link www.eaglephp.com
 */

define('__DS__',DIRECTORY_SEPARATOR); //定义目录分割符
define('ROOT_DIR', dirname(dirname(__FILE__)) . __DS__);
define('CONF_DIR', ROOT_DIR. 'Config'.__DS__);
define('DATA_DIR', ROOT_DIR.'Data'.__DS__); // 缓存数据文件存放目录
define('APP_DIR', ROOT_DIR. 'Application'. __DS__.APP_NAME.__DS__);
define('COM_DIR', ROOT_DIR. 'Common'.__DS__);
define('LIB_DIR', ROOT_DIR. 'Library'.__DS__);
define('PUB_DIR', ROOT_DIR. 'Public'.__DS__);
define('LOG_DIR', DATA_DIR.'Log'.__DS__.APP_NAME. __DS__);

define('APP_CONTROLLER_DIR', APP_DIR.'Controller'.__DS__);
define('APP_MODEL_DIR', APP_DIR.'Model'.__DS__);
define('APP_VIEW_DIR', APP_DIR.'View'.__DS__);
define('APP_COMPILE_DIR', DATA_DIR.'Compile'.__DS__.APP_NAME.__DS__);
define('APP_CACHE_DIR', DATA_DIR.'Cache'.__DS__.APP_NAME.__DS__);
define('APP_CONFIG_DIR', APP_DIR.'Config'.__DS__);

define('CACHE_FILE', 'file');
define('CACHE_APC', 'apc');
define('CACHE_MEMCACHE', 'memcache');

define('SESSION_SAVE_TYPE', CACHE_FILE); //memcache 、 table 、file
define('SESSION_LIFE_TIME', 3600); // session 生命周期 ,0为PHP默认时间

!defined('URL_MODEL') && define('URL_MODEL', 1); // URL模式（1为普通模式、2为pathinfo模式、3为.html模式），注：每次切换URL模式后需删除Data下面的Complie目录。

define('__DEFAULT_DATA_SOURCE__', 'default'); // 默认的数据源
define('__CLI__', (php_sapi_name() == 'cli') ? true : false); // CLI 命令行模式
define('OUTPUT_ENCODE', false);    // 采用ob_gzhandler方式压缩页面输出
define('HTML_STRIP_SPACE' , true); // 模版编译时是否去除html空格、换行符、注释

