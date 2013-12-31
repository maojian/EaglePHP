<?php

/**
 * 路由器应用管理
 * @author maojianlw@139.com
 * @since 2012-08-03
 */

class Application 
{
    
    /**
     * 初始化路由方式
     * 
     * @return void
     */
    public static function init()
    {
        if(__CLI__)
        {
            Router::cliParse();
            return;
        }
        switch (URL_MODEL)
        {
            case 1:
                 Router::ordinaryParse();
                 break;
            case 2:
                 Behavior::checkRoute();
                 Router::pathinfoParse();
                 break;
            case 3:
                 Behavior::checkRoute();
                 Router::htmlParse();
                 break;
        }
	    Behavior::checkRefresh();
    }
     
    
	/**
	 * 执行控制器的方法
	 * 
	 * @return void
	 */
	public function run() 
	{
		$controller = CONTROLLER_NAME . 'Controller';
		$action = ACTION_NAME . 'Action';
		
	    // 检测控制器、方法是否存在，否则跳转至404页面
		if(!class_exists($controller) || !method_exists($controller, $action))
		{
		   show_404();
		}

		$cls_parent = class_parents($controller);
		if($cls_parent !== false)
		{
    		$parent_name = array_shift($cls_parent);
    		$parent_obj = new $parent_name ();
    		$method = '_initialize';
    		if (method_exists($parent_obj, $method)) $parent_obj-> $method (); // 执行父类的初始化方法
		}
      
		$controller_obj = MagicFactory :: getInstance($controller);
		$controller_obj->$action();
	}
	
}
