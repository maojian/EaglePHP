<?php
class AlbumController extends CommonController{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = M('album');
    }
    
    public function indexAction(){	
    	$perpage = 10;
    	$total =  $this->cur_model->where($sql)->count();
    	$page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__));
		$list = $this->cur_model->where($sql)->order('id DESC')->limit("{$page->offset},{$perpage}")->select(array('cache'=>true));
        $photo_model = M('photo');
		foreach($list as &$val){
             $photo_info = $photo_model->field('thumbnail')->where("albumid={$val['id']}")->order('id DESC')->find();
             $img = $photo_info['thumbnail'];
             $val['img'] = ($img) ? __UPLOAD__.$img : __APP_RESOURCE__.'imgs/nopic.jpg';
        }
		$this->assign('list', $list);
		$this->assign('page', $page->show(4));
		$this->assign('title', '相册');
        $this->display();
    }
    
    
     
}