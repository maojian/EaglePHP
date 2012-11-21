<?php

/**
 * 公共控制器
 * 主要用于权限验证
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-6-8
 */

class CommonController extends Controller {
	
    protected static $adminUser = array();
    
    
	/**
	 * 初始化
	 */
	public function _initialize(){
	    self::$adminUser = Session::get(SESSION_USER_NAME);
	    //Session::checkClientCookie();
		$this->checkLogin();
		$this->checkAuth();
	}
	
	
	/**
	 * 检查会话是否超时
	 */
	public function checkLogin(){
		if(!isset(self::$adminUser)){
			// 如果为Ajax请求，则返回json数据
			if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
				$this->ajaxReturn(301, '会话已超时，请重新登录');
			}else{
				$this->redirect('public/login');	
			}
		}
	}
	
	
	/**
	 * 访问权限验证
	 */
	private function checkAuth(){
	    // 如果是超级管理员，跳过权限验证
	    if(self::$adminUser['role_id'] == 1){
	       return true;
	    }
		$url = CONTROLLER_NAME.'/'.ACTION_NAME;
		$role_modules = self::$adminUser['role_modules'];
		
		if(empty($role_modules)){
			M('module')->getMenuTree();
			$role_modules = self::$adminUser['role_modules'];
		}
		
		$isAccess = M('role')->authRoleAccess($url, $role_modules);
		if(!$isAccess){
			$message = '对不起，你没有权限执行此操作！';
			if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
				$this->ajaxReturn(300, $message);
			}else{
				$this->redirect(__ROOT__, 3, $message);
			}
		}
	}
	
	
	/**
	 * 上传图片
	 */
	protected function upload($image_dir, $type='image'){
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

}
?>