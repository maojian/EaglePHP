<?php

// 应用名称
define('APP_NAME', 'Home'); 

// 此处用绝对路径用于支持CLI命令行模式
include realpath(dirname(dirname(dirname(__FILE__))).'/Library/Loader.php');

MagicFactory::getInstance('Application')->run();

?>