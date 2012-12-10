<?php
class MessageController extends CommonController{
    
     private $cur_model = null;
     
     
     public function __construct(){
          $this->cur_model = model('message');
     }
     
     public function indexAction(){ 	
      	  $perpage = 10;
      	  $total =  $this->cur_model->where(true)->count();
      	  $page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__));
  		  $list = $this->cur_model->where(true)->order('create_time DESC')->limit("{$page->offset},{$perpage}")->select();
          $helper = model('helper');
  		  foreach ($list as &$v){
              $v['img'] = $helper->getGravatarByEmail($v['email'], $v['id']);
              $v['ip'] = $v['ip'].' '.HttpRequest::getIpLocation($v['ip']);
          }
  		  $this->assign('list', $list);
  		  $this->assign('page', $page->show(4));
  		  $this->assign('title', '留言反馈');
          $this->display();
    }
    
    public function feedbackAction(){
          if($this->isPost()){
			 //$this->ajaxReturn(300, '很抱歉，五一假期留言功能将暂时关闭，谢谢关注！');
             $yzm = $this->post('yzm');
             $content = $this->post('content');
             if(Session::get('verify') != md5($yzm)){
                 $this->ajaxReturn(300, '验证码错误，请重新输入！');
             }
             if(preg_match('#('.getCfgVar('cfg_filter_word').')#i', $content)){
                 $this->ajaxReturn(300, '评论内容包含禁词，请检查！');
             }
             $_POST['ip'] = get_client_ip();
             $_POST['create_time'] = date('Y-m-d H:i:s');
             if($id = $this->cur_model->add()){
                 $this->ajaxReturn(200, '留言成功！');
             }
        }
        $this->ajaxReturn(300, '留言失败，请稍后再试！');
    }
     
	 /**
	 * 获取验证码
	 */
	 public function verifyCodeAction(){
		  Image::buildImageVerify(4,1,'jpeg',50,24);
	 }
    
}