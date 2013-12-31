<?php

/**
 * 加载系统所需资源
 * 
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 * @since 2012-11-28
 */

header('Content-Type:text/html; charset=utf-8');
//error_reporting(E_ALL &~ E_STRICT &~ E_NOTICE);
//ini_set('display_errors', 1);

include 'Function.php';
include 'AutoLoader.class.php';
include dirname(dirname(__FILE__)).'/Config/Constants.php';

AutoLoader::init();

Date::timeZone('PRC');

Application::init();

Controller::init();

TraceException::init();

RunTime::init();

Log::init();

Session::init();

