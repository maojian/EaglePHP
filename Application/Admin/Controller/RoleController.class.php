<?php

/**
 * 角色管理
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-7-16
 */

class RoleController extends CommonController{

    private $role_model;
    
    public function __construct(){
		$this->role_model = M('role');
	}
	
	/**
	 * 角色列表页
	 */
	public function indexAction(){
		$sql = $this->getWhereSql();
		$page = $this->page($this->role_model->where($sql)->count());
		$roles = $this->role_model->where($sql)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		
		$this->assign('roles', $roles);
		$this->assign('page', $page);
		$this->display();
	}
	
	
	/**
	 * 获取条件SQL
	 */
	private function getWhereSql(){
		$name = $_REQUEST['name'];
		$create_time = $_REQUEST['create_time'];
		
		if($name) 
			$sql[] = " name LIKE '%{$name}%' ";
		if($create_time)
			$sql[] = " DATE_FORMAT(create_time, '%Y-%m-%d')='$create_time' ";
		
		return $sql;
	}

	/**
	 * 添加角色
	 */
	public function addAction(){
		if(count($_POST) > 0){
			$module_ids = $_POST['module_ids'];
			$_POST['create_time'] = date('Y-m-d H:i:s');
			if(!empty($_POST['module_ids'])){
				sort($module_ids);
				$module_ids = implode(',', $module_ids);
				$_POST['module_ids'] = $module_ids;	
			}else{
				$_POST['module_ids'] = '';
			}
				
			if($this->role_model->add()){
			    // 自动刷新权限
			    M('module')->getMenuTree(true);
				$this->ajaxReturn(200, '添加成功');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
			$this->assign('trees', $this->getTree());
			$this->display();
		}
	}
	
	/**
	 * 修改角色
	 */
	public function updateAction(){
		if(count($_POST) > 0){
			$module_ids = $_POST['module_ids'];
			if(!empty($_POST['module_ids'])){
				sort($module_ids);
				$module_ids = implode(',', $module_ids);
				$_POST['module_ids'] = $module_ids;	
			}else{
				$_POST['module_ids'] = '';
			}
			if($this->role_model->save()){
			    // 自动刷新权限
			    M('module')->getMenuTree(true);
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败', '');
			}
		}else{
			$role_id = (int)$_REQUEST['id'];
			$role_info = $this->role_model->where("id=$role_id")->find();
			$this->assign('trees', $this->getTree($role_info['module_ids']));
			$this->assign('role_info', $role_info);
			$this->display();
		}
	}
	
	
	
	/**
	 * 删除角色
	 */
	public function deleteAction(){
		$ids = $_REQUEST['ids'];
		
		if(!$ids){
			$this->ajaxReturn(300, '编号错误');
		}
		
		$idArr = explode(',', $ids);
		
		if(in_array(1, $idArr)){
			$this->ajaxReturn(300, '无法删除超级管理员角色');
		}
		
		if(!empty($ids) && $this->role_model->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
	
	    
    /**
     * 获取模块树节点
     */
    protected function getTree($module_ids=''){
    	if(!empty($module_ids))
    		$role_modules = explode(',', $module_ids);
		
    	function getChildNode($modules, $role_modules){
    		if(is_array($modules)){
    		    $role_id = (int)$_REQUEST['id'];
	    		foreach($modules as $module){
	    			$module_id = $module['id'];
	    			if($role_id == 1){
	    			    $isCheck = 'checked=true';
	    			}else{
	    			    $isCheck = ($role_modules && in_array($module_id, $role_modules) ? 'checked=true' : 'checked=false');
	    			}
	    			
	    			$tree .= "<li><a tname=\"module_ids[]\" tvalue=\"{$module_id}\" {$isCheck}>{$module['name']}</a>";
	    			if(isset($module['childs'])){
	    				$tree .= '<ul>';
	    				$tree .= getChildNode($module['childs'], $role_modules);
	    				$tree .= '</ul>';	
	    			}
	    			$tree .= '</li>';
	    		}
	    	}
	    	return $tree;
    	}
    	
    	return getChildNode(M('module')->getModule(), $role_modules);
    }

    
}
?>