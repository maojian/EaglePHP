<?php
/**
 * 淘宝采集
 * @author maojianlw@139.com
 */

class TaobaoController extends CommonController{
    
    
    private $taobaoClient = null;
    
    public function __construct(){
        import('Sdk.taobao.ClientApi');
        $this->taobaoClient = ClientApi::getInstance();
    }
    
    public function indexAction(){
        $cid = (int)$_REQUEST['cid'];
        $cid = $cid ? $cid : $_REQUEST['cid'] = 30;
        $pageNum = (int)$_REQUEST['page'];
        $keyword = $_REQUEST['keyword'];
        if($keyword == '搜索你感兴趣的商品') $keyword = '';	
        $perpage = 33;
        $data = $this->taobaoClient->getTaoBaoKeItems(array('cid'=>$cid, 'keyword'=>$keyword, 'page'=>$pageNum, 'page_size'=>$perpage));
        $page = new Page(array ('total' =>$data['count'], 'perpage' =>$perpage, 'url' => __ACTION__."?cid=$cid&keyword=$keyword"));
    	$this->assign('cats', $this->taobaoClient->getItemCates());
        $this->assign('list', $data['list']);
        $this->assign('page', $page->show(4));
        $this->assign('title', '淘宝');
        $this->display();
    }
    
}