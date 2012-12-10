<?php
class NewsTypeModel extends Model{
    
       public function __construct(){
        
       }
       
       public function getNewsTypeList(){
            $list = model('news_type')->field('id,title,parent')->where('state=0')->order('rank DESC')->select(array('cache'=>true));
            return $this->getTree(0, $list);
       }
       
       public function getChild($id, $list){
			$child = array();
            if(is_array($list)){
                 foreach ($list as $val){
                     if($val['parent'] == $id){
                         $child[] = $val;
                     }
                 }
            }
			return $child;
       }
       
       public function getTree($id, $list){
            static $tree = null;
            $temp = null;
            $child = $this->getChild($id, $list);
            if(is_array($child)){
                foreach ($child as $val){
                      $title = $val['title'];
                      $child_tree = $this->getTree($val['id'], $list);
                      if($child_tree){
                          if($val['parent']!=0) $title .= '&gt;&gt;';
                          $child_tree = "<div><ul>{$child_tree}</ul></div>";
                      }
                      $temp .= "<li><a href=\"".__ROOT__."news/index/type/{$val['id']}\">{$title}</a>{$child_tree}</li>\r\n";
                      $tree .= $temp;
                }
            }
            
            return $temp;
       }
         
}