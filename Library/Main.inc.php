<?php
/**
 * 系统配置文件
 * @author maojianlw@139.com
 * @since 2.1 - 2012-09-21
 * @link www.eaglephp.com
 */

//header('Content-Type:text/html; charset=utf-8');
//error_reporting(E_ALL &~ E_STRICT &~ E_NOTICE);
//ini_set('display_errors', 1);
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

define('SESSION_SAVE_TYPE', 'file'); //memcache 、 table 、file
define('SESSION_LIFE_TIME', 3600); // session 生命周期 ,0为PHP默认时间

define('URL_MODEL', 1); // URL模式
define('__DEFAULT_DATA_SOURCE__', 'default'); // 默认的数据源
define('__CLI__', (php_sapi_name() == 'cli') ? true : false); // CLI 命令行模式

