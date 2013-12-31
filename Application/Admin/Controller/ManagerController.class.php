<?php

/**
 * 账户管理
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-6-8
 */
 
class ManagerController extends CommonController {
	
	private $manager_model, $roles, $status;
	
	public function __construct(){
		$this->manager_model = model('manager');
		$this->roles = $this->getRoles();
		$this->status = array('U'=>'可用', 'D'=>'锁定');
	}
	
	
	/**
	 * 获取角色
	 */
	protected function getRoles(){
		if($roles = model('role')->field('id,name')->select()){
			foreach($roles as $v){
				$data[$v['id']] = $v['name'];
			}
		}
		return $data;
	}
	
	
	/**
	 * 获得频道盒子
	 */
	protected function getChannelBox($channelIds='')
	{
	    $channelArr = array();
	    $box = null;
	    if($channelIds) $channelArr = explode(',', $channelIds);
	    $list = model('helper')->getNewsTypeList();
	    foreach ($list as $k=>$v)
	    {
	        $box .= "<option value='{$k}' ".(is_array($channelArr) && in_array($k, $channelArr) ? 'selected="true"' : '').">{$v}</option>";
	    }
	    return $box;
	}
	
	
	
	/**
	 * 列表页
	 */
	public function indexAction(){
		$page = $this->page($this->manager_model->where(true)->count(), 'uid');
		$users = $this->manager_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		if(is_array($users)){
			foreach($users as &$user){
				$user['role_id'] = $this->roles[$user['role_id']];
				$user['state'] = $this->status[$user['state']];
			}
		}
		$this->assign('roles', $this->roles);	
		$this->assign('users', $users);
		$this->assign('page', $page);
		$this->display();
	}
	
	
	
	/**
	 * 验证表单提交的值是否符合条件
	 */
	private function checkValidate($flag='add'){
	    $username = $this->post('username');
	    $uid = $this->post('uid');
		
	    $sql = "username='{$username}'";
		$sql .= ($flag == 'update') ? " AND uid!={$uid}" : '';
		
		$password = $this->post('password');
	    $channel_ids = $this->post('channel_ids');
		$pwd_len = strlen($password);
		if($this->manager_model->where($sql)->count() > 0){
			$this->ajaxReturn(300, '用户名已经存在');
		}else if(!empty($password) && ($pwd_len<6 || $pwd_len >8)){
			$this->ajaxReturn(300, '密码长度必须限制在6~8位之间');
		}
		if($flag == 'update' && empty($password)){
		    unset($_POST['password']);
		}else{
		    $_POST['password'] = md5($password);
		}
		
		if($channel_ids){
		    $_POST['channel_ids'] = implode(',', $channel_ids);
		}
	}
	
	
	/**
	 * 添加用户
	 */
	public function addAction(){
		if(count($_POST) > 0){
			$this->checkValidate();
			$_POST['register_time'] = date('Y-m-d H:i:s');
			if($this->manager_model->add()){
				$this->ajaxReturn(200, '添加成功');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
		    $this->assign('channelBox', $this->getChannelBox());
			$this->assign('roles', $this->roles);
			$this->assign('status', $this->status);
			$this->display();
		}
	}
	
	/**
	 * 修改用户
	 */
	public function updateAction(){
		if(count($_POST) > 0){
			$this->checkValidate('update');
			if($this->manager_model->save()){
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$uid = (int)$this->get('uid');
			$user_info = $this->manager_model->where("uid=$uid")->find();
			$this->assign('user_info', $user_info);
			$this->assign('roles', $this->roles);
			$this->assign('status', $this->status);
			$this->assign('channelBox', $this->getChannelBox($user_info['channel_ids']));
			$this->display();
		}
	}
	
	/**
	 * 删除用户
	 */
	public function deleteAction(){
		$ids = $this->request('ids');
		
		if(!$ids){
			$this->ajaxReturn(300, '编号错误');
		}
		
		$idArr = explode(',', $ids);
		
		if(in_array(1, $idArr)){
			$this->ajaxReturn(300, '无法删除超级管理员');
		}
		
		if(!empty($ids) && $this->manager_model->where("uid IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
	
	
	/**
	 * 导出至Excel文件
	 */
	public function exportAction(){
		$data[0] = array('用户ID', '用户名', '密码', '角色', '最近登录IP', '最近登录时间', '注册时间', '状态');
		$users = $this->manager_model->where(true)->limit(10000)->order('uid DESC')->select();
		if(is_array($users)){
			foreach($users as &$user){
				$user['role_id'] = $this->roles[$user['role_id']];
				$user['state'] = $this->status[$user['state']];
			}
			$data = array_merge($data, $users);
		}
		$xls = new Excel('UTF-8', false, '账号列表');
		$xls->addArray($data);
		$xls->generateXML('manager_'.date('YmdHis'));
	}
	
	
	/**
	 * 修改密码
	 */
	public function setPwdAction(){
		$username = self::$adminUser['username']; 
		if($this->isPost()){
			$old_password = md5($this->post('old_password'));
			$new_password = $this->post('new_password');
			$new_password2 = $this->post('new_password2');
			
			$pwd_len = strlen($new_password);
			if($new_password != $new_password2){
				$this->ajaxReturn(300, '新密码跟确认密码不一致');
			}else if($pwd_len<6 || $pwd_len >8){
				$this->ajaxReturn(300, '密码长度必须限制在6~8位之间');
			}
			$manager_info = $this->manager_model->field('uid')->where("username='{$username}' AND password='{$old_password}'")->find();
			if(!$manager_info){
				$this->ajaxReturn(300, '输入的旧密码错误');
			}else{
				$this->manager_model->where("uid={$manager_info['uid']}")->save(array('password'=>md5($new_password)));
				$this->ajaxReturn(200, '密码修改成功');
			}
		}
		$this->assign('username', $username);
		$this->display();
	}
	
    
}
?>