<?php
class PublicController extends Controller {

	/**
	 * 用户登录
	 */
	public function loginAction() {	
		if(Session::get(SESSION_USER_NAME)){
			$this->redirect('./');
		}else{
			$this->display();	
		}
	}
	
	
	/**
	 * 用户退出
	 */
	public function logoutAction() {
		Session::destory();
		$this->redirect('?c=public&a=login');
	}
	
	
	/**
	 * 弹出登录框
	 */
	public function loginDialogAction(){
		$this->display();
	}
	
	
	/**
	 * 获取验证码
	 */
	public function getVerifyCodeAction(){
		Image::buildImageVerify(4,1,'png',60,18,2);
	}
	
}
?>