<?php
class NoteController extends CommonController{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = model('note');
    }
    
    public function indexAction(){  	
    	$perpage = 15;
    	$total =  $this->cur_model->where(true)->count();
    	$page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__));
		$list = $this->cur_model->where(true)->order('create_time DESC')->cache()->limit("{$page->offset},{$perpage}")->select();
        
		$this->assign('list', $list);
		$this->assign('page', $page->show(4));
		$this->assign('title', '往期微博');
        $this->display();
    }
    
    
     
}