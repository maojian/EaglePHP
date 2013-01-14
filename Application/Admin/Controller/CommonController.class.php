<?php

/**
 * 公共控制器
 * 主要用于权限验证
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-6-8
 */

class CommonController extends Controller {
	
    
    /**
     * 登录用户信息
     * @var array
     */
    protected static $adminUser = array();
    
    
	/**
	 * 初始化
	 */
	public function _initialize()
	{
	    self::$adminUser = $this->session(SESSION_USER_NAME);
	    //Session::checkClientCookie();
		$this->checkLogin();
		$this->checkAuth();
	}
	
	
	/**
	 * 检查会话是否超时
	 */
	public function checkLogin()
	{
		if(empty(self::$adminUser)){
			// 如果为Ajax请求，则返回json数据
			if(HttpRequest::isAjaxRequest()){
				$this->ajaxReturn(301, '会话已超时，请重新登录');
			}else{
				$this->redirect('?c=public&a=login');	
			}
		}
	}
	
	
	/**
	 * 访问权限验证
	 */
	private function checkAuth()
	{
	    // 如果是超级管理员，跳过权限验证
	    if($this->user('role_id') == 1) return true;
	    
		$url = CONTROLLER_NAME.'/'.ACTION_NAME;
		$role_modules = $this->user('role_modules');
		
		if(empty($role_modules)){
			model('module')->getMenuTree();
			$role_modules = $this->user('role_modules');
		}
		
		$isAccess = model('role')->authRoleAccess($url, $role_modules);
		if(!$isAccess){
			$message = '对不起，你没有权限执行此操作！';
			if(HttpRequest::isAjaxRequest()){
				$this->ajaxReturn(300, $message);
			}else{
				redirect(__ROOT__, 3, $message);
			}
		}
	}
	
	
	/**
	 * 上传图片
	 */
	protected function upload($image_dir, $type='image')
	{
		$upload_boj = new Upload();
		switch($type){
			case 'image':
				$upload_boj->allowTypes = array('image/gif','image/jpg','image/jpeg', 'image/pjpeg','image/bmp','image/x-png');
				break;
			case 'csv':
				$upload_boj->allowTypes = array('application/vnd.ms-excel', 'application/octet-stream');
				break;
			default :
				$upload_boj->allowTypes = '';
				break;
		}
		$message = $upload_boj->upload($image_dir);
		if($message === false){
			$this->ajaxReturn('300', $upload_boj->getErrorMsg());
		}
		$file_info = $upload_boj->getUploadFileInfo();
		return $file_info[0]['savename'];
	}
	
	
	/**
	 * 分页计算
	 * @param String $count 数据集条数
	 * @param String $orderField 排序字段
	 * @param String $order 排序方式
	 * @return Array 
	 */
	public function page($count, $order_field='id', $order_direction='desc') 
	{
		// 当前页
		$page_num = (int)HttpRequest::getPost('pageNum');
		
		$postOrderFiled = HttpRequest::getPost('orderField');
		$order_field = $postOrderFiled ? $postOrderFiled : $order_field;
        $postOrderDirection = HttpRequest::getPost('orderDirection');
        $order_direction = $postOrderDirection ? $postOrderDirection : $order_direction;
        
		$page_num = (empty ($page_num)) ? 0 : $page_num;
		$num_per_page = (int)HttpRequest::getPost('numPerPage'); // 每页条数
		$num_per_page = (empty ($num_per_page)) ? 20 : $num_per_page;

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
	
	
	/**
	 * 判断客户端是否post方式提交
	 * 
	 * @return void
	 */
	public function isPost()
	{
	    return (HttpRequest::getRequestMethod() == 'POST' && count($_POST));
	}
	
	
	/**
	 * 从会话中获取用户信息
	 * @param string $name
	 * @return mixed
	 */
	public function user($name = null)
	{
	    $info = $this->session(SESSION_USER_NAME);
	    return ($name && isset($info[$name])) ? $info[$name] : '';
	}
	
	

}
?>