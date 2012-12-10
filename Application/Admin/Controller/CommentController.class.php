<?php

/**
 * 评论管理
 * @author maojianlw@139.com
 * @since 2012-04-16 
 */
 
class CommentController extends CommonController {
	
	private $curModel;
	
	public function __construct(){
		$this->curModel = model('comment');
	}
	
	
	/**
	 * 列表页
	 */
	public function indexAction(){
		$page = $this->page($this->curModel->where(true)->count());
		$list = $this->curModel->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();	
		if(is_array($list)){
		    $news_model = model('news');
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
		if($this->isPost()){
		    $id = (int)$this->post('id');
		    $revert = $this->post('revert', self::_NO_CHANGE_VAL_, false);
		    $content = $this->post('content');
		    if(!empty($revert)){
		        $content .= "<br/><br/><font color=red>管理员回复：{$revert}</font><br/>";
		    }
		    $data = array('name'=>$this->post('name'), 'content'=>$content);
		    if($this->curModel->where("id=$id")->save($data) !== false){
			    if($this->post('isSendEmail') > 0)
			    {
			        $id = $this->post('id');
			        $info = $this->curModel->where("id=$id")->find();
			        $news_info = model('news')->field('title')->where("id={$info['news_id']}")->find();
			        $email = $info['email'];
			        $news_title = $news_info['title'];
			        $emailArr = explode('@', $email);
			        $url = HttpRequest::getHostInfo();
			        $link = $url.__PUB__.'index.php/news/show/id/'.$info['news_id'];
			        $user = getCfgVar('cfg_smtp_user');
			        $title = "{$user}刚刚回复了评论： {$news_title}(请不要回复此邮件)";
			        $content = "{$emailArr[0]},你好<br/><br/>".
			                    "你在 &nbsp;<strong>{$news_title}</strong>&nbsp;发表评论已得到管理员回复：<br/><br/>".
			                    $content.
			                    "<br/>你可以点击查看评论：<br/>".
			                    "<a href='{$link}' target='_blank'>{$link}</a><br/>".
			                    "<br/>想了解更多信息，请访问 <a href='{$url}' target='_blank'>{$url}</a>";
			        sendMail($email, $title, $content);
			    }
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$this->get('id');
			$info = $this->curModel->where("id=$id")->find();
			if($info){
			    $news_info = model('news')->field('title')->where("id={$info['news_id']}")->find();
			    $info['news_title'] = $news_info['title'];
			    $info['location'] = IpLocation::getlocation($info['ip']);
			}
			$this->assign('info', $info);
			$this->display();
		}
	}
	
	
	/**
	 * 删除
	 */
	public function deleteAction(){
		$ids = $this->request('ids');
		if($ids){
		    $arr = $this->curModel->field('news_id')->where("id IN($ids)")->select();
		    foreach ($arr as $v){
		        model('news')->where("id={$v['news_id']} AND comments>0")->save(array('comments'=>array('exp'=>'comments-1')));
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