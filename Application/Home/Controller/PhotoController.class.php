<?php
class PhotoController extends Controller{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = model('photo');
    }
    
    public function showAction(){
        $album_id = (int)$this->get('album');
        $album_info = model('album')->field('title')->getbyId($album_id);
		$this->assign('rssFeed', url(__URL__."&a=flashXML&album=$album_id"));
		$this->assign('title', $album_info['title'].' - 图片秀');
        $this->display();
    }
    
    
    public function flashXMLAction(){
        $album_id = (int)$this->get('album');
		$list = $this->cur_model->field('title,thumbnail,middle,original')->where("albumid=$album_id")->order('id DESC')->cache()->select();
		$items = null;
		foreach($list as $k=>&$v){
		    $thumbnail = __UPLOAD__.$v['thumbnail'];
		    $middle = __UPLOAD__.$v['middle'];
		    $original = __UPLOAD__.$v['original'];
		    $title = addslashes($v['title']);
				$items .= "<item>
   							<title>{$title}</title>
   							<link>{$original}</link>
   							<guid>{$k}</guid>
   							<media:thumbnail url='{$thumbnail}' />
   							<media:content url='{$middle}'  type='' />
   						</item>";
		}
		$feed_str =<<<EOT
			<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss">
			<channel>
			<generator>piclens publisher win 1.0.12</generator>
			<title></title>
			<link></link>
			<description></description>
			{$items}
			</channel>
			</rss>	
EOT;
		echo $feed_str;
    }
    
    
     
}