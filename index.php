<?php

/**
* 检测服务器环境，及路由控制。
* @since 1.9 - 2012-8-5
* @author maojianlw@139.com
*/

if(!file_exists('Data/Install/INSTALL.LOCK'))
{
    header('Location: ./Public/install/');
}
else
{
    include './Public/home/index.php';
}
