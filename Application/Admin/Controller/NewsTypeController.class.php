<?php

/**
 * 新闻类型管理
 * 
 * @author maojianlw@139.com
 * @since 1.7 - 2012-04-24
 */

class NewsTypeController extends CommonController{

    private $cur_model;

    public function __construct(){
		$this->cur_model = model('news_type');
	}
	
	/**
	 * 新闻类型列表页
	 */
	public function indexAction(){
		$page = $this->page($this->cur_model->where(true)->count());
		$list = $this->cur_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		if(is_array($list)){
		    $state_arr = $this->getData('state');
		    $news_model = model('news');
    		foreach ($list as &$val){
    		    $type_info = $this->cur_model->field('title')->where("id={$val['parent']}")->find();
    		    $val['parent_name'] = $type_info ? "{$type_info['title']}&nbsp;&nbsp;（{$val['parent']}）" : '主类&nbsp;&nbsp;（0）'; 
    		    $val['state'] = $state_arr[$val['state']];
    		    $val['news_count'] = $news_model->where("type={$val['id']}")->count();
    		}
		}
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	

	/**
	 * 添加新闻类型
	 */
	public function addAction(){
		if(count($_POST) > 0){
			$_POST['create_time'] = date('Y-m-d H:i:s');
			if($this->cur_model->add()){
				$this->ajaxReturn(200, '添加成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
		    $this->assign('state_arr', $this->getData('state'));
		    $this->assign('type_arr', model('helper')->getNewsTypeList());
			$this->display();
		}
	}
	
	/**
	 * 修改新闻类型
	 */
	public function updateAction(){
		if(count($_POST) > 0){
			if($this->cur_model->save()){
				$this->ajaxReturn(200, '修改成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$this->get('id');
			$info = $this->cur_model->where("id=$id")->find();
			$this->assign('info', $info);
			$this->assign('state_arr', $this->getData('state'));
		    $this->assign('type_arr', model('helper')->getNewsTypeList());
			$this->display();
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