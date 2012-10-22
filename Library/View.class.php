<?php
/**
 * 视图层基类
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-6-10
 */


class View extends Smarty {
	
    const LIFE_TIME = 216000;// 缓存文件保存的时间
	private static $smarty;

	public function getSmartyInstance() {
		if (self :: $smarty == null) {
		    mk_dir(APP_CACHE_DIR);
		    mk_dir(APP_COMPILE_DIR);
			self :: $smarty = new Smarty();
		}
		return self :: $smarty;
	}

	public function __construct() {
		$this->getSmartyInstance();
		self::$smarty->register_modifier('utf8Substr', 'utf8Substr');
	    self::$smarty->register_modifier('getCfgVar', 'getCfgVar');
		self :: $smarty->cache_dir = APP_CACHE_DIR;
		self :: $smarty->compile_dir = APP_COMPILE_DIR;
		self :: $smarty->template_dir = APP_VIEW_DIR;
		self :: $smarty->left_delimiter = '{{';
		self :: $smarty->right_delimiter = '}}';
	}

	/**
	 * 获取模板页面路径
	 */
	public function getTplPath($path = '') {
		return (empty ($path) ? CONTROLLER_NAME . '/' . ACTION_NAME : $path) . '.html';
	}
	
	
	/**
	 * 设置模板常量
	 */
	public function assign($name, $value) {
		self :: $smarty->assign($name, $value);
	}
	
	
	/**
	 * 获取模板内容
	 */
	public function fetch($tpl=ACTION_NAME){
		$path = CONTROLLER_NAME."/{$tpl}.html";
		return self :: $smarty->fetch($path);
	}
	
	
	/**
	 * 判断模板是否缓存
	 */
	public function isCache($lifetime = NULL, $cache_id = null, $path=null) {
		$lifetime = !empty($lifetime) ? $lifetime : self::LIFE_TIME;
		self :: $smarty->caching = true;
		self :: $smarty->cache_lifetime = $lifetime;
		self :: $smarty->compile_check = true; //设置为false，这是实现最佳性能的最小改动
		return self :: $smarty->is_cached($this->getTplPath($path), $cache_id);
	}

	/**
	 * 视图层显示
	 * @param String $path 模板路径
	 */
	public function display($path = '') {
		self :: $smarty->display($this->getTplPath($path));
	}
	
	
	/**
	 * 缓存模板显示
	 */
	public function cacheDisplay($lifetime=null, $cache_id=null,  $path=null){
		$a = $this->isCache($lifetime, $cache_id, $path);
		$this->display($path);
	}
	
	
	/**
	 * 分页计算
	 * @param String $count 数据集条数
	 * @param String $orderField 排序字段
	 * @param String $order 排序方式
	 * @return Array 
	 */
	public function page($count, $order_field='id', $order_direction='desc') {
		// 当前页
		$page_num = (int) $_POST['pageNum'];
		$page_num = (empty ($page_num)) ? 0 : $page_num;
		
		// 每页条数
		$num_per_page = (int) $_POST['numPerPage'];
		$num_per_page = (empty ($num_per_page)) ? 20 : $num_per_page;
		
		if(!empty($_POST['orderField']))
			$order_field =  $_POST['orderField'];
		
		if(!empty($_POST['orderDirection']))
			$order_direction = $_POST['orderDirection'];
		
		$page = array (
			'totalCount' => $count,
			'pageNumShown' => 10,
			'numPerPage' => $num_per_page,
			'pageNum' => (empty($page_num)) ? 1 : $page_num,
			'limit' => (($page_num > 0) ? ($page_num-1)*$num_per_page : $page_num),
			'orderDirection' => $order_direction,
			'orderField' => $order_field,
			'orderFieldStr' => "$order_field $order_direction"
		);
		return $page;
	}
	
}
