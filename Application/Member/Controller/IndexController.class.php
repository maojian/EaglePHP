<?php
class IndexController extends CommonController
{
    private $userModel = null;
    
    public function __construct()
    {
        $this->userModel = model('user');
        if(!$this->userModel->isLogin()) redirect(__ROOT__.'?c=user&a=login');
        if(!$this->userModel->isActive()) redirect(__ROOT__.'?c=user&a=verify');        
    }
    
    
    public function indexAction()
    {
        $this->assign('tip', Date::getPeriodOfTime().'好');
        $this->assign('userInfo', $this->userModel->getUser());
        $this->assign('now_time', Date::format());
        $data = model('home.news')->getList(5);
        foreach ($data['list'] as $k=>&$v) $v['title'] = String::msubstr($v['title'], 0, 18);
        $this->assign('list', $data['list']);
        $this->display();
    }
    
}
