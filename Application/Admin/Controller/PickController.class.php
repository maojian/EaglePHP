<?php

/**
 * 网页采集管理
 * 
 * @author maojianlw@139.com
 * @since 1.8 - 2012-04-27
 */

class PickController extends CommonController{

    private $cur_model;
    private $lang_arr;
    
    public function __construct(){
		$this->cur_model = model('pick');
		$this->lang_arr = array('gb2312'=>'GB2312/GBK', 'utf-8'=>'UTF-8');
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
	
	
	/**
	 * 添加新闻类型
	 */
	public function addAction(){
		if($this->isPost()){
			$data['title'] = $this->post('title');
			$data['url'] = $this->post('url');
			$data['lang'] = $this->post('lang');
		    $data['rule'] = $this->post('rule', self::_NO_CHANGE_VAL_, false);
			$data['create_time'] = Date::format();
			if(!$this->post('lang')) $data['lang'] = 'utf-8';
			if($this->cur_model->add($data)){
				$this->ajaxReturn(200, '添加成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
		    $this->assign('lang_arr', $this->lang_arr);
			$this->display('Pick/action');
		}
	}
	
	/**
	 * 修改新闻类型
	 */
	public function updateAction(){
		if($this->isPost()){
			$id = (int)$this->post('id');
			$data['title'] = $this->post('title');
			$data['url'] = $this->post('url');
			$data['lang'] = $this->post('lang');
		    $data['rule'] = $this->post('rule', self::_NO_CHANGE_VAL_, false);
			if($this->cur_model->where("id=$id")->save($data)){
				$this->ajaxReturn(200, '修改成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$this->get('id');
			$info = $this->cur_model->where("id=$id")->find();
			$this->assign('info', $info);
			$this->assign('lang_arr', $this->lang_arr);
			$this->display('Pick/action');
		}
	}
	
	/**
	 * 删除新闻类型
	 */
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