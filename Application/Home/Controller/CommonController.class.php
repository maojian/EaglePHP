<?php

class CommonController extends Controller{

    public function _initialize() {
        if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
             return false;
        }
        $news_m = M('news');
    	$type_tree = M('NewsType')->getNewsTypeList();
    	$controller = strtolower(CONTROLLER_NAME);
    	if(in_array($controller, array('index', 'news')) && ACTION_NAME!='show'){
    	     $news_arr = $news_m->getList();
          	 $this->assign('news_list', $news_arr['list']);
          	 $this->assign('page', $news_arr['page']);
    	}
    	$this->assign('case_list', M('case')->field('title,img,url')->where('state=0')->order('rank DESC,id DESC')->limit(4)->select(array('cache'=>true)));
    	$this->assign('hot_news', $news_m->getHot());
    	$this->assign('type_tree', $type_tree);
    	$this->assign('recommend', $news_m->getRecommend($_GET['type']));
    	$cfg_keywords = getCfgVar('cfg_keywords');
    	$this->assign('keywords_arr', ($cfg_keywords) ? explode(',', $cfg_keywords) : '');
    	$this->assign('links', M('links')->field('name,url,img')->where('state=0')->order('rank DESC,id DESC')->select(array('cache'=>true)));
    }
    
}
?>