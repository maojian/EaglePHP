<?php

/**
 * 留言反馈管理
 * @author maojianlw@139.com
 * @since 2012-04-22 
 */
 
class MessageController extends CommonController {
	
	private $curModel;
	
	public function __construct(){
		$this->curModel = M('message');
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
	 * 留言回复
	 */
	public function updateAction(){
		if(count($_POST) > 0){
		    $revert = $this->getParameter('revert');
		    if(!empty($revert)){
		        $_REQUEST['content'] .= "<br/><br/><font color=red>管理员回复：{$revert}</font><br/>";
		    }
		    $_POST['content'] = addslashes($_REQUEST['content']);
			if($this->curModel->save() !== false){
			    if($this->getParameter('isSendEmail') > 0)
			    {
			        $email = $this->getParameter('email');
			        $emailArr = explode('@', $email);
			        $url = HttpRequest::getHostInfo();
			        $link = $url.'/index.php/message/';
			        sendMail($email, getCfgVar('cfg_webname').'官网留言反馈回复', "{$emailArr[0]},你好<br/><br/>你的反馈已得到管理员的回复：<br/><br/>".$_REQUEST['content']."<br/>你可以点击查看评论：<br/><a href='{$link}' target='_blank'>{$link}</a><br/><br/>想了解更多信息，请访问 <a href='{$url}' target='_blank'>{$url}</a>");
			    }
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$_REQUEST['id'];
			$info = $this->curModel->where("id=$id")->find();
			$info['location'] = IpLocation::getlocation($info['ip']);
			$this->assign('info', $info);
			$this->display();
		}
	}
	
	/**
	 * 删除
	 */
	public function deleteAction(){
		$ids = $_REQUEST['ids'];
		if(!empty($ids) && $this->curModel->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
    
}
?>