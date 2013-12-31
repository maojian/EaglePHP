<?php
class IndexController extends CommonController
{
    
    public function indexAction()
    {
        $this->assign('adv_list', model('adv')->field('id,title,url,img')->order('rank ASC,id DESC')->where('state=0')->cache()->select());
        $news_m = model('news');
		$this->assign('title', '首页');
		$this->assign('type_name', '最新文章');
		$this->assign('announ_list', $news_m->getAnnouncement());
		$this->assign('note', model('note')->order('id DESC')->limit(1)->cache()->find());
		$this->assign('member_name', Cookie::get('member_name'));
		$this->assign('userInfo', model('member.user')->getUser());
		$this->assign('is_login', model('member.user')->isLogin());
	    $this->assign('tip', Date::getPeriodOfTime().'好');
    	$this->display();
    }
     
}