<?php

class RoleController extends ApiCommonController{

    private $current_model = null;
    
    public function __construct(){
    	$this->current_model = M('role');
    }
    
    
    /**
     * 获取角色列表
     */
    public function listsAction(){
    	$data = $this->current_model->field('id,name')->order('id DESC')->select();
    	if(is_array($data)){
    		foreach($data as $k=>$val){
    			$temps["{$k} attr"] = array('id'=>$val['id'], 'name'=>$val['name']);
    			$temps[$k] = '';
    		}
    		$list['role'] = $temps;
    	}
    	$this->formatReturn(200, $list);
    }
    
    
    /**
     * 修改角色配置
     */
    public function updateRoleConfigAction(){
    	$role_id = (int)$_POST['role'];
    	$cfg = $_REQUEST['cfg'];
    	//Log::info(var_export($_POST, true));
    	$this->current_model->where("id=$role_id")->save(array('config'=>$cfg));
    }
}
?>