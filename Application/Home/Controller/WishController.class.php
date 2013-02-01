<?php

/**
 * 许愿墙
 * 
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 * @since 2012-1-7
 */

class WishController extends CommonController
{
    
    private $curModel = null;
    
    
    public function __construct()
    {
        $this->curModel = model('wish');
    }
    
    
    public function indexAction()
    {
        $sql = null;
        if($this->isPost())
        {
            $type = (int)$this->post('searchType');
            $keyword = $this->post('keyword');
            $sql = $type == 0 ? 'id='.intval($keyword) : "DATE_FORMAT(submittime, '%Y-%m-%d')='{$keyword}'";
        }
        $perpage = 16;
    	$total =  $this->curModel->where($sql)->count();
    	$page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__));
        $list = $this->curModel->order('id DESC')->where($sql)->limit("{$page->offset},{$perpage}")->select();
        $url = __APP_RESOURCE__.'imgs/wish/';
        foreach ($list as &$v)
        {
            $lArr = IpLocation::getlocation($v['ip'], true);
            $v['location'] = $lArr['country'];
            $v['content'] = preg_replace('#\[bq([0-9]*)\]#i', '<img src="'.$url.'bq$1.gif" alt="[bq$1]" />', $v['content']);
        }
        $this->assign('curPage', $page->nowindex);
        if($total > 0) $this->assign('pageArr', range(1, $page->totalpage));
        $this->assign('totalPage', $page->totalpage);
        $this->assign('list', $list);
        $this->display();
    }
    
    
    public function writeAction()
    {
        if(HttpRequest::isPost())
        {
            $this->post('submittime', Date::format());
            $this->post('ip', HttpRequest::getClientIP());
            $this->curModel->add();
            redirect(__URL__);
        }
        else
        {
            $locationArr = IpLocation::getlocation('', true);
            $location = $locationArr['country'];
            $this->assign('location', $location);
            $this->display();
        }
    }
    

}
