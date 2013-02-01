<?php

/**
 * 类映射
 * @copyright Copyright &copy; 2011, MAOJIAN
 * @author maojianlw@139.com
 * @since 1.0 - 2011-10-15
 */

class Reflect
{
	
	public $reflect = null;
	public $file_arr = null;
	public $file_fun_arr = null;
	
	
	/**
	 * 初始化类
	 * 
	 * @return void
	 */
    public function Reflect($class='', $file_arr=array()) 
    {
    	if(!empty($class))
    	{
    		$this->file_arr = $file_arr;
    		$this->reflect = new ReflectionClass($class);
    	}
    }
    
    
	/**
	 * 获取类的名称
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->reflect->getName();
	}
	
	
	/**
	 * 获得类的所有方法
	 * 
	 * @return array
	 */
	public function getMethods()
	{
		$methods = $this->reflect->getMethods();
		if(is_array($methods))
		{
			foreach($methods as $method)
			{
				$declaring_class = $method->getDeclaringClass();
				if($declaring_class->name == $this->getName())
				{
					$comment_arr[] = $this->getDocComment($method, false);
				}
			}
		}
		return $comment_arr;
	}
	
	
	/**
	 * 获得对象注释
	 * 
	 * @param object $obj
	 * @param bool $isClass
	 * @param bool $isFun
	 * @return array
	 */
	public function getDocComment($obj, $isClass = true, $isFun = false)
	{
		$comment = $obj->getDocComment();
		$comment_arr['name'] = $obj->name;
		$comment_params = array();
		
		if(!empty($comment))
		{
			$comment = str_replace(chr(10), '', $comment);
			$comments = array_unique(explode('*', $comment));
			$size = count($comments);
			
			$remark = null;
			
			if($isClass)
			{ // 对象类
				foreach($comments as $val)
				{
					$val = trim($val);
					if(!empty($val) && $val!='/')
					{
						if(strpos($val, '@') === 0)
						{
							$val = substr($val, 1);
						}
						$val = htmlspecialchars($val);
						$remark[] = "{$val}";
					}
				}
				$comment_arr['comment'] = $remark;
			}
			else
			{  // 类方法或者函数
				foreach($comments as $k=>$val)
				{
					$val = trim($val);
					if(!empty($val) && $val!='/')
					{
						if(strpos($val, '@param') === 0)
						{
							$param_arr = explode(' ', $val);
							$param_name = str_replace('$', '', isset($param_arr[2]) ? $param_arr[2] : '');
							$desc = isset($param_arr[3]) ? $param_arr[3] : '';
							$size = count($param_arr);
							
							if($size > 4)
							{
								for($i=4; $i<=($size-1); $i++)
								{
									$desc .= ' '.$param_arr[$i];
								}
								$desc = htmlspecialchars($desc);
							}
							
							$comment_params[$param_name] = array('type'=>isset($param_arr[1]) ? $param_arr[1] : '', 'desc'=>$desc);
						}
						else if(strpos($val, '@return') === 0)
						{
							$return_arr = explode(' ', $val);
							$comment_arr['return'] = array('type'=>strtolower(isset($return_arr[1]) ? $return_arr[1] : ''), 'desc'=>isset($return_arr[2]) ? $return_arr[2] : '');
						}
						else
						{
							$remark .= "{$val}, ";
						}
					}
				}
				$comment_arr['comment'] = rtrim($remark, ', ');
			}
		}
		
		if(!$isClass)
		{
			// 参数匹配
			$method_params = $obj->getParameters();
			if(is_array($method_params) && count($method_params) > 0)
			{
				foreach($method_params as $param)
				{
					$param__name = $param->name;
					if($comment_params && array_key_exists($param__name, $comment_params))
					{
						$type = $comment_params[$param__name]['type'];
						$desc = $comment_params[$param__name]['desc'];
					}
					else
					{
						$type = 'mixed';
						$desc = '';
					}
					
					// 是否是可选项,获取默认值
					$value = ($option = $param->isOptional()) ? $param->getDefaultValue() : '';
					if(!empty($value))
					{
						$value = htmlspecialchars($value);
					}
					$refer = $param->isPassedByReference();
					$params[] = array('name'=>$param__name, 'type'=>strtolower($type), 'desc'=>$desc, 'value'=>$value, 'option'=>$option, 'refer'=>$refer);
				}
				$comment_arr['params'] = $params;
			}
			
			// 函数没有此项修饰符
			if(!$isFun)
			{
				$comment_arr['modifiers'] = Reflection::getModifierNames($obj->getModifiers());	
			}
			
			$start_line = $obj->getStartLine()-1;
			$end_line = $obj->getEndLine();
			
			$comment_arr['source'] = $this->getHighLightStr($start_line, $end_line, (($isFun) ? $this->file_fun_arr : $this->file_arr));
		}
		else
		{
			
			/**
			 * 属性列表
			 */
			$properties = $obj->getProperties();
			$proper_arr = array();
			if(is_array($properties))
			{
				foreach($properties as $p)
				{
					$declaring_class = $p->getDeclaringClass();
					if($declaring_class->name != $this->getName())
					{
						continue;
					}
					$p_comment = $p->getDocComment();
					if(!empty($p_comment))
					{
						$p_comment = str_replace('*', '', $p_comment);
						$p_comment = str_replace('/', '', $p_comment);
						$p_comment = htmlspecialchars($p_comment);
					}
					$modifier_arr = Reflection::getModifierNames($p->getModifiers());
					$p->setAccessible(true);

					$value = ($p->isDefault()) ? $p->getValue($obj) : '';
					if(!is_string($value))
					{
						$value = '';
					}
					else if(!empty($value))
					{
						$value = htmlspecialchars($value);
					}
					$proper_arr[] = array('name'=>$p->name, 'comment'=>$p_comment, 'modifier'=>implode(' ', $modifier_arr), 'value'=>$value);
				}
				$comment_arr['properties'] = $proper_arr;
			}
			
			/**
			 * 获取父类
			 */
			$parent_class = $obj->getParentClass();
			$comment_arr['parent'] = isset($parent_class->name) ? $parent_class->name : '';
			
			/**
			 * 预定义常量列表
			 */
			$constants = $obj->getConstants();
			$comment_arr['constants'] = $constants;
		}
		return $comment_arr;
	}
	
	
	/**
	 * 获得高亮显示的字符串
	 * 
	 * @param int $start_line
	 * @param int $end_line
	 * @param array $file_arr
	 * @return string
	 */
	protected function getHighLightStr($start_line=0, $end_line=0, $file_arr)
	{
	    $source = null;
		for($i=$start_line; $i<=$end_line; $i++)
		{
			$source .= isset($file_arr[$i]) ? $file_arr[$i] : '';
		}
		
		// 获取类方法源码
		$source = highlight_string("<?php \r\n{$source}\r\n?>", true);
		$search = '&lt;?php&nbsp;';
		$source = str_replace($search, '', $source);
		$search = '?&gt;';
		$source = str_replace($search, '', $source);
		
		return $source;
	}
	
	
	
	/**
	 * 获得所有用户自定义函数
	 * 
	 * @param string $fun_file
	 * @return array
	 */
	public function getFunctions($fun_file)
	{
		$functions = get_defined_functions();
		$fun_arr = $functions['user'];
		if(is_array($fun_arr))
		{
			foreach($fun_arr as $fun)
			{
				$funObj = new ReflectionFunction($fun);
				$file_name = $funObj->getFileName();
				if(strtolower($file_name) == strtolower($fun_file))
				{
					$this->file_fun_arr = file($file_name);
					$fun_info_arr[] = $this->getDocComment($funObj, false, true);
				}
			}
		}
		return $fun_info_arr;
	}
	
}
