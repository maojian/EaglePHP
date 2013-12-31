<?php
/**
 * 用户接口
 * @copyright Copyright &copy; 2011, MAOJIAN
 * @since 1.0 - 2011-7-16
 * @author maojianlw@139.com
 */

class ManagerController extends ApiCommonController
{
	
	private $user_model = null;
	
	public function __construct()
	{
		$this->user_model = model('manager');
	}
	
	/**
	 * 登录接口
	 */
    public function loginAction()
    {
    	$username = $this->post('username');
    	$password = $this->post('password');
    	$verify = $this->post('verify');
    	if(empty($username)){
    		$this->formatReturn(201);
    	}else if(empty($password)){
    		$this->formatReturn(202);
    	}else if(empty($verify)){
    		$this->formatReturn(206);	
    	}else if(Session::get('verify') != md5($verify)){
    		$this->formatReturn(207);
    	}else{
    		$password = md5($password);
    		if($manager_info = $this->user_model->field('uid,username,role_id,state')->where("username='$username' AND password='$password'")->find()){
    			if($manager_info['state'] == 'D'){
    				$this->formatReturn(203);
    			}else{
    			    Session::delete('verify');
    				$role_info = model('role')->field('name')->where("id={$manager_info['role_id']}")->find();
    				$manager_info['role_name'] = $role_info['name'];
    				Session::set(SESSION_USER_NAME, $manager_info);
    				$login_ip = get_client_ip();
    				$login_time = date('Y-m-d H:i:s');
    				$this->user_model->where("uid={$manager_info['uid']}")->save(array('login_ip'=>$login_ip, 'login_time'=>$login_time));
    				if(getCfgVar('cfg_login_email')){ // 是否发送登录邮件
    				     sendMail(getCfgVar('cfg_adminemail'), getCfgVar('cfg_admin_sysname').' 登录提醒', "{$username} 于 {$login_time} 在IP为： {$login_ip} 登录成功！来自：".IpLocation::getlocation($login_ip));
    				}
    				$this->user_model->where('uid='.$manager_info['uid'])->save(array('login_num'=>array('exp'=>'login_num+1')));
    				$this->formatReturn(200, $manager_info);
    			}
    		}else{
    			$this->formatReturn(204);
    		}
    	}
    }

    
    /**
	 * 用户退出
	 */
	public function logoutAction() 
	{
		Session::destory();
		$this->formatReturn(200);
	}
    
    
}
?>