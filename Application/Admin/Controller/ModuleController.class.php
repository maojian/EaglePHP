<?php

/**
 * 系统模块管理
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-7-24
 */

class ModuleController extends CommonController{
	
	private $module_model, $levels, $targets;
	
    public function __construct() {
    	$this->module_model = model('module');
    	$this->levels = array(0=>'链接', 1=>'按钮');
		$this->targets = array('navTab'=>'navTab', 'dialog'=>'dialog', 'ajaxTodo'=>'ajaxTodo', '_blank'=>'_blank', '_self'=>'_self');
    }
    
    
    /**
	 * 列表页
	 */
	public function indexAction(){
		$page = $this->page($this->module_model->where(true)->count());
		$modules = $this->module_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		if(is_array($modules)){
			foreach($modules as &$module){
				$module['level'] = $this->levels[$module['level']];
			}
		}
		$this->assign('modules', $modules);
		$this->assign('page', $page);
		$this->display();
	}
	
	
	/**
	 * 查找页
	 */
	public function lookupAction(){
		//$sql[] = 'level=0';
		$page = $this->page($this->module_model->where(true)->count());
		$modules = $this->module_model->field('id,name,url')->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		if(is_array($modules)){
			array_unshift($modules, array('id'=>0, 'name'=>'主菜单'));
		}
		$this->assign('modules', $modules);
		$this->assign('page', $page);
		$this->display();
	}
	

	/**
	 * 添加模块
	 */
	public function addAction(){
		if($this->isPost()){
			$_POST['create_time'] = date('Y-m-d H:i:s');
			$_POST['parent'] = (int)$this->post('orgLookup_id');
			if($this->module_model->add()){
				$this->ajaxReturn(200, '添加成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
			$this->assign('targets', $this->targets);
			$this->assign('levels', $this->levels);
			$this->display();
		}
	}
	
	/**
	 * 修改模块
	 */
	public function updateAction(){
		$id = (int)$this->request('id');
		if($this->isPost()){
			$_POST['parent'] = (int)$this->post('orgLookup_id');
			if($this->module_model->save()){
				$this->ajaxReturn(200, '修改成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$this->assign('targets', $this->targets);
			$this->assign('levels', $this->levels);
			$module_info = $this->module_model->where("id=$id")->find();
			$parent_info = $this->module_model->field('name')->where("id={$module_info['parent']}")->find();
			$module_info['parent_name'] = $parent_info['name'];
			$this->assign('module_info', $module_info);
			$this->display();
		}
	}
	
	/**
	 * 删除模块
	 */
	public function deleteAction(){
		$ids = $this->request('ids');
		if(!empty($ids) && $this->module_model->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
	
    
}
?>