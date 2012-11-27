<?php

/**
 * 加载系统所需资源
 * 
 * @author maojianlw@139.com
 * @since 1.6 - 2011-6-8
 */

include 'Main.inc.php';
include 'Function.php';
include 'AutoLoader.class.php';

AutoLoader :: init();

Date :: timeZone('PRC');

Application :: init();

Controller :: init();

TraceException :: init();

RunTime :: init();

Filter :: init();

Log :: init();

Session :: init();
