<?php
class RssController extends Controller{
     
      public function xmlAction(){
            $data = model('news')->getList(100);
            $list = $data['list'];
            $this->assign('list', $list);
            $this->display();
      }
 
}