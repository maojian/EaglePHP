<?php

define('APP_NAME', 'Api'); // 应用名称

define('SESSION_USER_NAME', 'manager_info'); // 保存用户信息的session名称

// 此处用绝对路径用于支持CLI命令行模式
include realpath(dirname(dirname(dirname(__FILE__))).'/Library/Loader.php');

MagicFactory::getInstance('Application')->run();

?>