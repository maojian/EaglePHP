<?php

/**
 * 视图层基类
 * 
 * @author maojianlw@139.com
 * @since 2.3 - 2012-11-21
 * @link http://www.eaglephp.com
 */


class View extends SmartyBC {
	
    /**
     * 模版文件后缀
     * @var TPL_SUFFIX
     */
    const TPL_SUFFIX = '.html';
    
    /**
     * 缓存文件生存期
     * @var LIFE_TIME
     */
    const LIFE_TIME = 216000;
    
    /**
     * Smarty模版引擎对象
     * @var Object
     */
	protected static $smartyBC;
	
    
	/**
	 * 初始化模版引擎
	 * @return void
	 */
	public function __construct() 
	{
		$this->getSmartyInstance();
		self :: $smartyBC->register_modifier('utf8Substr', 'utf8Substr');
	    self :: $smartyBC->register_modifier('getCfgVar', 'getCfgVar');
		self :: $smartyBC->cache_dir = APP_CACHE_DIR;
		self :: $smartyBC->compile_dir = APP_COMPILE_DIR;
		self :: $smartyBC->template_dir = APP_VIEW_DIR;
		self :: $smartyBC->left_delimiter = '{{';
		self :: $smartyBC->right_delimiter = '}}';
	}
	
	
	/**
	 * 获取Smarty实例对象
	 */
    public function getSmartyInstance() 
    {
		if (self :: $smartyBC == null) 
		{
		    mk_dir(APP_CACHE_DIR);
		    mk_dir(APP_COMPILE_DIR);
			self :: $smartyBC = new SmartyBC();
		}
		return self :: $smartyBC;
	}

	/**
	 * 获取模板页面路径
	 * @return string
	 */
	public function getTplPath($path = '') 
	{
		return (empty ($path) ? CONTROLLER_NAME . '/' . ACTION_NAME : $path) . self::TPL_SUFFIX;
	}
	
	
	/**
	 * 设置模板常量
	 * @return void
	 */
	public function assign($name, $value = null) 
	{
		self :: $smartyBC->assign($name, $value);
	}
	
	
	/**
	 * 获取模板内容
	 * @return string
	 */
	public function fetch($tpl=ACTION_NAME)
	{
		$path = CONTROLLER_NAME."/{$tpl}".self::TPL_SUFFIX;
		return self :: $smartyBC->fetch($path);
	}
	
	
	/**
	 * 判断模板是否缓存
	 * @param int $lifetime
	 * @param string $cache_id
	 * @param string $path
	 * @return bool
	 */
	public function isCache($lifetime = NULL, $cache_id = null, $path=null) 
	{
		$lifetime = !empty($lifetime) ? $lifetime : self::LIFE_TIME;
		self :: $smartyBC->caching = true;
		self :: $smartyBC->cache_lifetime = $lifetime;
		self :: $smartyBC->compile_check = true; //设置为false，这是实现最佳性能的最小改动
		return self :: $smartyBC->is_cached($this->getTplPath($path), $cache_id);
	}
	

	/**
	 * 视图层显示
	 * @param string $path 模板路径
	 * @return void
	 */
	public function display($path = '') 
	{
	    try 
	    {
	        self :: $smartyBC->display($this->getTplPath($path));
	    }
	    catch (Exception $e)
	    {
	        throw_exception($e->getMessage());
	    }
	}
	
	
	/**
	 * 缓存模板显示
	 * @return void
	 */
	public function cacheDisplay($lifetime=null, $cache_id=null,  $path=null)
	{
		$this->isCache($lifetime, $cache_id, $path);
		$this->display($path);
	}

}
