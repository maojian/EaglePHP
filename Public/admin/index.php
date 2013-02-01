<?php


define('APP_NAME', 'Admin'); // 应用名称

define('SESSION_USER_NAME', 'manager_info'); // 保存用户信息的session名称

define('URL_MODEL', 1); // URL模式（1为普通模式、2为pathinfo模式、3为.html模式）

include realpath(dirname(dirname(dirname(__FILE__))).'/Library/Loader.php');

MagicFactory::getInstance('Application')->run();

?>