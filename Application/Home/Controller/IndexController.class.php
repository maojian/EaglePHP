<?php
class IndexController extends CommonController{
    
    public function indexAction(){
        $this->assign('adv_list', model('adv')->field('id,title,url,img')->order('rank ASC,id DESC')->where('state=0')->cache()->select());
        $news_m = model('news');
		$this->assign('title', '首页');
		$this->assign('type_name', '博客文章');
		$this->assign('announ_list', $news_m->getAnnouncement());
		$this->assign('note', model('note')->order('id DESC')->limit(1)->cache()->find());
		$this->assign('video', model('video')->order('rank DESC,id DESC')->cache()->find());
    	$this->display();
    }
     
}