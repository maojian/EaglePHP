<?php

/**
 * 视图层基类
 * 
 * @author maojianlw@139.com
 * @since 2.3 - 2012-11-21
 * @link http://www.eaglephp.com
 */


class View extends SmartyBC 
{
	
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
	    // 对输出内容进行zlib压缩
	    ob_start((!ini_get('zlib.output_compression') && OUTPUT_ENCODE) ? 'ob_gzhandler' : null);
		$this->getSmartyInstance();
		self :: $smartyBC->register_modifier('utf8Substr', 'utf8Substr');
	    self :: $smartyBC->register_modifier('getCfgVar', 'getCfgVar');
	    self :: $smartyBC->register_modifier('url', 'url');
		self :: $smartyBC->cache_dir = APP_CACHE_DIR;
		self :: $smartyBC->compile_dir = APP_COMPILE_DIR;
		self :: $smartyBC->template_dir = APP_VIEW_DIR;
		self :: $smartyBC->left_delimiter = '{{';
		self :: $smartyBC->right_delimiter = '}}';
	}
	
	
	/**
	 * 获取Smarty实例对象
	 * 
	 * @return object
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
	 * 
	 * @return string
	 */
	public function getTplPath($path = '') 
	{
		return (empty ($path) ? CONTROLLER_NAME . '/' . ACTION_NAME : $path) . self::TPL_SUFFIX;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Smarty_Internal_Data::assign()
	 */
	public function assign($tpl_var, $value = null, $nocache = false)
	{
		self :: $smartyBC->assign($tpl_var, $value, $nocache);
	}
	
	/**
	 *
	 * 获取已assign中的值
	 */
	public function getAssign($name)
	{
		if(isset(self :: $smartyBC->tpl_vars[$name])) return self :: $smartyBC->tpl_vars[$name]->value;
		return null;
	}
	
	
	/**
	 * 获取模板内容
	 * 
	 * @return string
	 */
	public function fetch($template = ACTION_NAME, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
	{
		$path = CONTROLLER_NAME."/{$template}".self::TPL_SUFFIX;
		return self :: $smartyBC->fetch($path, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
	}
	
	
	/**
	 * 判断模板是否缓存
	 * 
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
	 * 
	 * @param string $path 模板路径
	 * @return void
	 */
	public function display($template = null, $cache_id = null, $compile_id = null, $parent = null) 
	{
	    try 
	    {
	        self :: $smartyBC->display($this->getTplPath($template), $cache_id, $compile_id, $parent);
	    }
	    catch (Exception $e)
	    {
	        throw_exception($e->getMessage());
	    }
	}
	
	
	/**
	 * 缓存模板显示
	 * 
	 * @return void
	 */
	public function cacheDisplay($lifetime=null, $cache_id=null,  $path=null)
	{
		$this->isCache($lifetime, $cache_id, $path);
		$this->display($path);
	}

}
