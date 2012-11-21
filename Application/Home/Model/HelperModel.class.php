<?php
class HelperModel extends Model{
     
     public function getParent($id, $list){
          static $arr = array();
          if(is_array($list)){
              foreach($list as $val){
                  if($val['id'] == $id){
                      $arr[$id] = $val['title'];
                      if($val['parent'] != 0){
                          $this->getParent($val['parent'], $list);
                      }
                  }
              }
          }
          return $arr;
     }
     
     public function getChild($id, $list, $isnull=false){
          static $arr = array();
          if($isnull) $arr = null;
          if(is_array($list)){
              foreach($list as $val){
                  if($val['parent'] == $id){
                      $arr[$val['id']] = $val['title'];
                      $this->getChild($val['id'], $list);
                  }elseif($val['id'] == $id){
                      $arr[$id] = $val['title'];
                  }
              }
          }
          return $arr;
     }
     
     
     /**
      * 根据email获取gravatar头像
      * @param string $email
      * @param int $size
      */
     public function getGravatarByEmail($email, $id, $size = 48){
         return "http://{$_SERVER['HTTP_HOST']}/".__APP_RESOURCE__.'imgs/avatar/'.((intval($id)%22)+1).'.jpg';
         //return 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?d='. urlencode($default).'&s='.$size;
     }
     
}