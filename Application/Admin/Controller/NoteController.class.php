<?php

/**
 * 笔记管理
 * 
 * @author maojianlw@139.com
 * @since 1.6 - 2011-03-29
 */

class NoteController extends CommonController{

    private $cur_model;
    
    public function __construct(){
		$this->cur_model = M('note');
	}
	
	/**
	 * 新闻类型列表页
	 */
	public function indexAction(){
		$page = $this->page($this->cur_model->where(true)->count());
		$list = $this->cur_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	
	private function check(){
	     if(mb_strlen($this->getParameter('content'), 'utf-8') > 240){
	         $this->ajaxReturn(300, '内容不能超过240个字！');
	     }
	}
	
	/**
	 * 添加新闻类型
	 */
	public function addAction(){
		if(count($_POST) > 0){
		    $this->check();
			$_POST['create_time'] = date('Y-m-d H:i:s');
			if($this->cur_model->add()){
				$this->ajaxReturn(200, '添加成功');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
			$this->display();
		}
	}
	
	/**
	 * 修改新闻类型
	 */
	public function updateAction(){
		if(count($_POST) > 0){
		    $this->check();
			if($this->cur_model->save()){
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$this->getParameter('id');
			$info = $this->cur_model->where("id=$id")->find();
			$this->assign('info', $info);
			$this->display();
		}
	}
	
	/**
	 * 删除新闻类型
	 */
	public function deleteAction(){
		$ids = $this->getParameter('ids');
		if(!empty($ids) && $this->cur_model->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
    
}
?>