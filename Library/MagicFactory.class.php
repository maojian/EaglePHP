<?php
/**
 * 通用工厂类
 * 将记住所有的操作，并将其应用到它的对象实例
 * 
 * @author maojianlw@139.com
 * @since 1.6 - 2011-6-8
 */

class MagicFactory {

	private static $history = array (), $class, $instance;
	private $obj = null; // 当前调用对象
	
	final private function MagicFactory($obj) {
		$this->obj = $obj;
	}

	public function __call($method, $args) {
		$obj = $this->obj;
		if (method_exists($obj, $method)) {
			return call_user_func_array(array ($obj,$method), $args);
		}else{
			throw_exception(language('SYSTEM:method.not.exists', array($method, get_class($obj))));
		}
		
	}

	public function __set($property, $value) {
		$this->obj-> {
			$property }
		= $value;
	}

	public static function getHistroy() {
		return self :: $history;
	}


	/**
	 * 根据类名获取对象实例
	 */
	public static function getInstance($class_name) {
		$args = array_slice(func_get_args(), 1);
		$args_str = implode(',', $args);
		if (count($args) > 0 || !isset (self :: $history[$class_name])) {
			if (class_exists($class_name)) {
				eval ("self::\$history[\$class_name] = new \$class_name($args_str);");
			} else {
				throw_exception(language('SYSTEM:class.not.exists', array($class_name, ACTION_NAME)));
			}
		}
		
		self :: $class = self :: $history[$class_name];
		
		// 返回本对象单例
		if (self :: $instance == null) {
			$instance = new MagicFactory(self :: $class);
		}
		return $instance;
	}

}
