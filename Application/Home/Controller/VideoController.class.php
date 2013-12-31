<?php
class VideoController extends CommonController{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = model('video');
    }
    
    public function indexAction(){  	
    	$perpage = 14;
    	$total =  $this->cur_model->count();
    	$page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__));
		$list = $this->cur_model->field('id,title,img')->where('state=0')->order('rank DESC,id DESC')->limit("{$page->offset},{$perpage}")->cache()->select();
		$this->assign('list', $list);
		$this->assign('page', $page->show(4));
		$this->assign('title', 'php视频教程,php视频教程下载');
        $this->display();
    }
    
    
    public function showAction(){
        $id = (int)$this->get('id');
        $info = $this->cur_model->field('title,url')->getbyId($id);
        $this->assign('info', $info);
        $this->assign('title', "{$info['title']} | php视频教程,php视频教程下载");
        $this->display();
    }
    
     
}