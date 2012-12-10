<?php
/**
 * 系统代理类,采用委托模式，主要将控制器类、模型类、视图类集中于一体,也可用于代理其他类
 *
 * @author maojianlw@139.com
 * @since 1.6 - 2011-6-10
 */

class Delegate 
{
   
   private static $instances;
   
   public static function addObject($object)
   {
   		self::$instances[] = $object;
   }
   
   public function __call($method, $args)
   {
		$instances = self::$instances;
   		if(is_array($instances))
   		{
   			foreach($instances as $key => $object)
   			{
   				if(method_exists($object, $method))
   				{
   					return call_user_func_array(array($object, $method), $args);
   				}
   			}
   		}
   		throw_exception(language('SYSTEM:method.not.exists', array($method, get_class($object))));
   }
    
}
