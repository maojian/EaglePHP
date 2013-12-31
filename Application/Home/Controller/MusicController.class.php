<?php
/**
 * 在线听音乐
 * @author maojianlw@139.com
 * @since 2012-05-21
 * @version 1.8
 */
class MusicController extends CommonController{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = model('music');
    }
    
    public function indexAction(){
		$list = $this->cur_model->where("state=0")->order('rank DESC,id DESC')->cache()->select();
		$music = null;
		foreach($list as $val){
		    $music .= "{title: '{$val['title']}',artist: '{$val['author']}', mp3: '{$val['url']}'},";
		}
		$this->assign('url', url(__PUB__.'api/index.php?c=xiami', false, 1));
		$this->assign('music', trim($music, ','));
		$this->assign('list', $list);
		$this->assign('title', '酷狗音乐盒2013官方免费下载,qq音乐,音乐台');
		$this->assign('hot_news', model('news')->getHot());
        $this->display();
    }
    
    
     
}