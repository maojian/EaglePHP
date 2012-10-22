<?php
/**
 * 经典案例
 * @author maojianlw@139.com
 * @since 2012-04
 */
class CaseController extends CommonController{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = M('case');
    }
    
    public function indexAction(){
        $this->assign('list', $this->cur_model->field('title,img,url')->where('state=0')->order('rank DESC,id DESC')->select(array('cache'=>true)));
        $this->assign('title', '案例');
        $this->display();
    }
    
    
     
}