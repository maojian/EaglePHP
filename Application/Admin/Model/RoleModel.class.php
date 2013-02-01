<?php

/**
 * 角色模块
 * @copyright Copyright &copy; 2011, MAO JIAN
 * @since 1.0 - 2011-8-2
 * @author maojianlw@139.com
 */

class RoleModel extends Model{
	
	
	/**
	 * 验证是否有权限访问
	 */
    public function authRoleAccess($url, $modules=array()){
    	$isAccess = false;
    	$url = strtolower($url);
    	$modules[] = array('name'=>'主页', 'url'=>'index/index');// 默认任何角色都可以访问主页
    	if(is_array($modules)){
    		foreach($modules as $key=>$module){
    			$module_url = strtolower($module['url']);
				$module_array = explode('/', $module_url);
				if(!isset($module_array[1])){
					$module_url = $module_array[0].'/index';
					//define('__NAV_TAB_ID__', $key); // 当前导航tab页的ID，用来执行功能操作后的重新加载
				}
    			if(($url == $module_url)){
    				$isAccess = true;
    				break;
    			}
    		}
    	}
    	return $isAccess;
    }
   
}
?>