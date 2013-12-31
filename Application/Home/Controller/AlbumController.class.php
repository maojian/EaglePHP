<?php
class AlbumController extends CommonController{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = model('album');
    }
    
    public function indexAction(){	
    	$perpage = 10;
    	$total =  $this->cur_model->count();
    	$page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__));
		$list = $this->cur_model->order('id DESC')->limit("{$page->offset},{$perpage}")->cache()->select();
        $photo_model = model('photo');
		foreach($list as &$val){
             $photo_info = $photo_model->field('thumbnail')->where("albumid={$val['id']}")->order('id DESC')->find();
             $img = ($photo_info) ? $photo_info['thumbnail'] : '';
             $val['img'] = ($img) ? __UPLOAD__.$img : __APP_RESOURCE__.'imgs/nopic.jpg';
        }
		$this->assign('list', $list);
		$this->assign('page', $page->show(4));
		$this->assign('title', '相册名称大全,qq相册封面拼图,qq相册名称');
        $this->display();
    }
    
    
     
}