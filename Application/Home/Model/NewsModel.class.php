<?php
class NewsModel extends Model{
    
     private $cfg_html_make = null;
     private $cfg_html_dir = null;
     
     public function __construct(){
         $this->cfg_html_make = getCfgVar('cfg_html_make');
         $this->cfg_html_dir  = getCfgVar('cfg_html_dir');
     }
     
     
    private function getChildType($id){
         static $list = array();
         if(count($list) == 0)
         {
             $list = model('news_type')->field('id,title,parent')->cache()->select();
         }
         $arr = model('helper')->getChild($id, $list, true);
         return is_array($arr) ? implode(',', array_keys($arr)) : 0;
    }
    
    
    
     /**
      * 获取公告
      * @param int $count
      */
     public function getAnnouncement($count=4){
         $list = $this->field('id,title,create_time')->where('type IN('.$this->getChildType(1).')')->order('rank ASC,create_time DESC')->limit($count)->cache()->select();
         if(is_array($list)){
             foreach($list as &$val){
                 $val['link'] = $this->getHtmlLink($val);
             }
         }
         return $list;
     }
     
     /**
      * 获取新闻列表
      */
     public function getList($perpage = 15){	
      	  $type_id = HttpRequest::getRequest('type');
      	  $content = HttpRequest::getRequest('content');
      	  
		  $sql = $url = '';
      	  if($type_id){
      	       $sql = $type_id ? 'type IN('.$this->getChildType($type_id).')' : '';
      	       $url = "&type=$type_id";
      	  }
      	  if($content){
      	       $sql = (($sql) ? $sql." AND " : '')." (content LIKE '%{$content}%' OR title LIKE '%{$content}%') ";
      	       $url .= "&content={$content}";
      	  }
      	  $total =  $this->where($sql)->count();
      	  $page = new Page(array ('total' =>$total, 'perpage' =>$perpage, 'url' => __ACTION__.$url)); //clicknum,comments,
      	  $news_list = $this->field('id,title,type,description,img,create_time')->where($sql)->order('rank ASC,create_time DESC')->limit("{$page->offset},{$perpage}")->cache()->select();

  		  if($news_list){
             $news_type_m = model('news_type');
             foreach ($news_list as &$val){
                $type_info = $news_type_m->field('title')->getbyId($val['type']);
                if($type_info) $val['type_name'] = $type_info['title'];
                $val['link'] = $this->getHtmlLink($val);
             }
          }
          return array('list'=>$news_list, 'page'=>$page->show(4));
     }
     
     /**
      * 获取关联的新闻
      */
     public function getRelation($type, $exculd_id, $count=6){
         if(empty($exculd_id) || empty($type)) return false;
         $sql = $exculd_id ? 'AND id!='.$exculd_id : '';
         $list = $this->field('id,title,create_time')->where("type=$type $sql")->order('rank ASC,id DESC')->limit($count)->cache()->select();
         if($list){
            foreach ($list as &$val){
                $val['short_title'] = utf8Substr($val['title'], 0, 11);
                $val['link'] = $this->getHtmlLink($val);
            }
         }
         return $list;
     }
     
     /**
      * 获得热点文章
      */
     public function getHot($count=5){
          $type_list = model('news_type')->field('id,title')->where('parent=0 OR id=1')->limit(4)->order('id ASC')->select();
          if(is_array($type_list)){
              foreach ($type_list as $type){
                  $type_id = $type['id'];
                  $type_ids = $this->getChildType($type_id);
                  $list = $this->field('id,title,create_time')->where("type IN($type_ids)")->order('rank ASC,id DESC')->limit($count)->select();
                  if($list) {
                     foreach ($list as &$val){
                         $val['link'] = $this->getHtmlLink($val);
                     }
                  }
                  $news_list[] = $list;
              }
          }
          $list = array('type_list'=>$type_list, 'news_list'=>$news_list);
          return $list;
     }
     
     /**
      * 获得精彩推荐
      */
     public function getRecommend($type_id=''){
          $type_id = (int)$type_id;
          $sql = 'img!=""';
          if(!empty($type_id)) $sql .= " AND type=$type_id";
          $recom_info = $this->field('id,title,img,description,create_time')->where($sql)->order('id DESC')->find();
          $type_list = model('news_type')->field('id AS type_id,title AS type_name')->where('id!=1')->order('id ASC')->cache()->select();
          if(is_array($type_list)){
              foreach ($type_list as $type){
                  $type_id = $type['type_id'];
                  $news_info = $this->field('id,title,create_time')->where("type=$type_id")->limit(1)->order('id DESC')->find();
                  if($news_info){
                      $news_info['link'] = $this->getHtmlLink($news_info);
                      $news_info['short_title'] = utf8Substr($news_info['title'], 0, 13);
                      $news_list[] = array_merge($type, $news_info);
                  }
              }
          }
          if($recom_info){
              $recom_info['link'] = $this->getHtmlLink($recom_info);
          }
          $list = array('recom_info'=>$recom_info, 'news_list'=>$news_list);
          return $list;
     }
     
     /**
      * 获取静态文件链接
      */
     public function getHtmlLink($news_info, $page = 0){
          if($this->cfg_html_make == 1){
              $link = rtrim(__PUB__,'/').$this->cfg_html_dir.'/'.date('Ymd',strtotime($news_info['create_time'])).'/'.$news_info['id'].($page ? "_{$page}" : '').'.html';
          }else{
              $link = __PROJECT__.'index.php?c=news&a=show&id='.$news_info['id'].($page ? "&page={$page}" : '');
              $link = url($link);
          }
          return $link;
     }
    
 
}