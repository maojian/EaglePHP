<?php
/**
 * 经典案例
 * @author maojianlw@139.com
 * @since 2012-04
 */
class CaseController extends CommonController
{
     
    private $cur_model;   
 
    public function __construct()
    {
        $this->cur_model = model('case');
    }
    
    public function indexAction()
    {
        $perpage = 20;
    	$total =  $this->cur_model->count();
        $page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__));
		$list = $this->cur_model->field('title,img,url')->where('state=0')->order('rank DESC,id DESC')->limit("{$page->offset},{$perpage}")->cache()->select();
        $this->assign('title', 'EaglePHP案例分享');
        $this->assign('page', $page->show(4));
        $this->assign('list', $list);
        $this->display();
    }
    
    
     
}