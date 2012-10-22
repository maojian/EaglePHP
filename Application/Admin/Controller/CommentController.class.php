<?php

/**
 * 评论管理
 * @author maojianlw@139.com
 * @since 2012-04-16 
 */
 
class CommentController extends CommonController {
	
	private $curModel;
	
	public function __construct(){
		$this->curModel = M('comment');
	}
	
	
	/**
	 * 列表页
	 */
	public function indexAction(){
		$page = $this->page($this->curModel->where(true)->count());
		$list = $this->curModel->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();	
		if(is_array($list)){
		    $news_model = M('news');
		    foreach ($list as &$val){
		        $news_info = $news_model->field('title')->where("id={$val['news_id']}")->find();
		        $val['news_title'] = $news_info['title'];
		    }
		}
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	
	
	/**
	 * 评论回复
	 */
	public function updateAction(){
		if(count($_POST) > 0){
		    $revert = $_REQUEST['revert'];
		    if(!empty($revert)){
		        $_REQUEST['content'] .= "<br/><br/><font color=red>管理员回复：{$revert}</font><br/>";
		    }
		    $_POST['content'] = addslashes($_REQUEST['content']);
			if($this->curModel->save()){
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$_REQUEST['id'];
			$info = $this->curModel->where("id=$id")->find();
			if($info){
			    $news_info = M('news')->field('title')->where("id={$info['news_id']}")->find();
			    $info['news_title'] = $news_info['title'];
			}
			$this->assign('info', $info);
			$this->display();
		}
	}
	
	
	/**
	 * 删除
	 */
	public function deleteAction(){
		$ids = $_REQUEST['ids'];
		if($ids){
		    $arr = $this->curModel->field('news_id')->where("id IN($ids)")->select();
		    foreach ($arr as $v){
		        M('news')->where("id={$v['news_id']} AND comments>0")->save(array('comments'=>array('exp'=>'comments-1')));
		    }
		}
		if(!empty($ids) && $this->curModel->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
    
}
?>