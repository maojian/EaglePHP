<?php

/**
 * 相册管理
 * @author maojianlw@139.com
 * @since 2011-12-29 
 */
 
class AlbumController extends CommonController {
	
	private $curModel;
	
	public function __construct(){
		$this->curModel = model('album');
	}
	
	
	/**
	 * 列表页
	 */
	public function indexAction(){
		$page = $this->page($this->curModel->where(true)->count());
		$list = $this->curModel->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();	
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	

	/**
	 * 添加
	 */
	public function addAction(){
		if($this->isPost()){
			$_POST['createtime'] = Date::format();
			$_POST['uid'] = $this->uid;
			if($this->curModel->add()){
				$this->ajaxReturn(200, '添加成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
			$this->cacheDisplay();
		}
	}
	
	/**
	 * 修改
	 */
	public function updateAction(){
		if($this->isPost()){
			$_POST['createtime'] = Date::format();
			if($this->curModel->save()){
				$this->ajaxReturn(200, '修改成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '修改失败', '');
			}
		}else{
			$id = (int)$this->get('id');
			$info = $this->curModel->where("id=$id")->find();
			$this->assign('info',$info);
			$this->display();
		}
	}
	
	/**
	 * 删除
	 */
	public function deleteAction(){
		$ids = $this->request('ids');
		if(!empty($ids) && $this->curModel->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
    
}
?>