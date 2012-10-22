<?php
class IndexController extends CommonController{
    
    public function indexAction(){
        $this->assign('adv_list', M('adv')->field('id,title,url,img')->order('rank ASC,id DESC')->where('state=0')->select());
        $news_m = M('news');
		$this->assign('title', '首页');
		$this->assign('announ_list', $news_m->getAnnouncement());
		$this->assign('note', M('note')->order('id DESC')->limit(1)->find());
		$this->assign('video', M('video')->order('rank DESC,id DESC')->find());
    	$this->display();
    }
     
}