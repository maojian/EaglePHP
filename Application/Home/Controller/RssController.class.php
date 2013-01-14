<?php
class RssController extends Controller{
     
      public function xmlAction(){
            $data = model('news')->getList(100);
            $list = $data['list'];
            $this->assign('host', HttpRequest::getHostInfo());
            $this->assign('list', $list);
            $this->display();
      }
 
}