<?php

class CommonController extends Controller{

    public function _initialize() {
        if(HttpRequest::isAjaxRequest()) return false;
        $news_m = model('news');
    	$type_tree = model('NewsType')->getNewsTypeList();
    	$controller = strtolower(CONTROLLER_NAME);
    	if(in_array($controller, array('index', 'news')) && ACTION_NAME!='show'){
    	     $news_arr = $news_m->getList();
          	 $this->assign('news_list', $news_arr['list']);
          	 $this->assign('page', $news_arr['page']);
    	}
    	$this->assign('case_list', model('case')->field('title,img,url')->where('state=0')->order('rank DESC,id DESC')->limit(4)->cache()->select());
    	$this->assign('hot_news', $news_m->getHot());
    	$this->assign('type_tree', $type_tree);
    	$this->assign('recommend', $news_m->getRecommend($this->request('type')));
    	$cfg_keywords = getCfgVar('cfg_keywords');
    	$this->assign('keywords_arr', ($cfg_keywords) ? explode(',', $cfg_keywords) : '');
    	$this->assign('links', model('links')->field('name,url,img')->where('state=0')->order('rank DESC,id DESC')->cache()->select());
        $this->assign(array('keywords' => '', 'description' => '', 'index' => '', 'case' => '', 'video' => '', 'album' => '', 'music' => '', 'taobao' => ''));
        $this->assign('host', HttpRequest::getHostInfo().'/index.php');
    }
    
}
?>