<?php
/**
 * 淘宝采集
 * @author maojianlw@139.com
 */

class TaobaoController extends CommonController{
    
    
    private $taobaoClient = null;
    
    private $sorts = array();
    
    public function __construct(){
        import('Sdk.taobao.ClientApi');
        $this->taobaoClient = ClientApi::getInstance();
        $this->sorts = array(
            'price_desc' => '价格从高到低', 
            'price_asc' => '价格从低到高', 
            'credit_desc' => '信用等级从高到低',
            'commissionNum_desc' => '成交量成高到低',
            'commissionNum_asc' => '成交量从低到高'
        );
    }
    
    public function indexAction(){
        $cid = (int)$this->getParameter('cid');
        //$_REQUEST['cid'] = $cid;
        $pageNum = (int)$this->getParameter('page');
        $keyword = $this->getParameter('keyword');
        $sort = $this->getParameter('sort');
        $start_price = (int)$this->getParameter('start_price');
        $end_price = (int)$this->getParameter('end_price');
        if($start_price === 0) $start_price = '';
        if($end_price === 0) $end_price = '';
        if($keyword == '搜索你感兴趣的商品') $keyword = '';	
        $perpage = 33;
        $data = $this->taobaoClient->getTaoBaoKeItems(array('cid'=>$cid, 'sort'=>$sort, 'start_price'=>$start_price, 'end_price'=>$end_price, 'keyword'=>$keyword, 'page'=>$pageNum, 'page_size'=>$perpage));
        //dump($data);exit;
        $page = new Page(array ('total' =>$data['count'], 'perpage' =>$perpage, 'url' => __ACTION__."?cid=$cid&keyword=$keyword"));
    	$this->assign('cats', $this->taobaoClient->getItemCates());
        $this->assign('list', $data['list']);
        $this->assign('page', $page->show(4));
        $this->assign('title', '淘宝');
        $this->assign('sorts', $this->sorts);
        $this->display();
    }
    
}