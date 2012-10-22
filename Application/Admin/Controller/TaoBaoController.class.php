<?php
/**
 * 淘宝采集
 * @author maojianlw@139.com
 */

class TaoBaoController extends CommonController{
    
    private $taobaoClient = null;
    
    public function __construct(){
        import('sdk.taobao.ClientApi');
        $this->taobaoClient = ClientApi::getInstance();
    }
    
    
    public function indexAction(){
        $pageNum = (int)$_REQUEST['pageNum'];
        $data = $this->taobaoClient->getTaoBaoKeItems(array('cid'=>(int)$_POST['cid'], 'keyword'=>$_POST['keyword'], 'page'=>$pageNum, 'page_size'=>20));
        $page = $this->page($data['count']);
        $this->assign('cats', $this->taobaoClient->getItemCates());
        $this->assign('list', $data['list']);
        $this->assign('page', $page);
        $this->display();
    }
    
    
}