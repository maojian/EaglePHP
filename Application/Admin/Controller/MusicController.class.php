<?php

/**
 * 音乐管理
 * 
 * @author maojianlw@139.com
 * @since 1.8 - 2012-05-21
 */

class MusicController extends CommonController{

    private $cur_model, $state_arr;
    
    public function __construct(){
        $this->state_arr = array(0=>'开启', 1=>'关闭');
		$this->cur_model = model('music');
	}
	
	public function indexAction(){
		$page = $this->page($this->cur_model->where(true)->count());
		$list = $this->cur_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		foreach($list as &$val){
		    $val['state'] = $this->state_arr[$val['state']];
		}
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}

	public function addAction(){
		if(count($_POST) > 0){
			$_POST['create_time'] = date('Y-m-d H:i:s');
			if($this->cur_model->add()){
				$this->ajaxReturn(200, '添加成功');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
		    $this->assign('state_arr', $this->state_arr);
			$this->display('Music/action');
		}
	}

	public function updateAction(){
		if(count($_POST) > 0){
			if($this->cur_model->save()){
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$this->get('id');
			$info = $this->cur_model->where("id=$id")->find();
			$this->assign('info', $info);
			$this->assign('state_arr', $this->state_arr);
			$this->display('Music/action');
		}
	}

	public function deleteAction(){
		$ids = $this->request('ids');
		if(!empty($ids) && $this->cur_model->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
    
}
?>