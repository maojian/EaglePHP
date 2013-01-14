<?php

// 应用名称
define('APP_NAME', 'Home'); 

// URL模式（1为普通模式、2为pathinfo模式、3为.html模式）
define('URL_MODEL', 2); 

// 此处用绝对路径用于支持CLI命令行模式
include realpath(dirname(dirname(dirname(__FILE__))).'/Library/Loader.php');

MagicFactory::getInstance('Application')->run();

?>