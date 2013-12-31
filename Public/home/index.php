<?php

// 应用名称
define('APP_NAME', 'Home'); 

// URL模式（1为普通模式、2为pathinfo模式、3为.html模式）注：每次切换URL模式后需删除Data下面的Complie目录。
define('URL_MODEL', 3); 

// 此处用绝对路径用于支持CLI命令行模式
include realpath(dirname(dirname(dirname(__FILE__))).'/Library/Loader.php');

MagicFactory::getInstance('Application')->run();

?>