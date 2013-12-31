<?php
/**
 * 淘宝采集
 * @author maojianlw@139.com
 */

class TaobaoController extends CommonController{
    
    
    private $taobaoClient = null;
    
    private $sorts = array();
    
    public function __construct(){
        import('Sdk.Taobao.ClientApi');
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
        $cid = (int)$this->request('cid');
        $pageNum = (int)$this->request('page');
        $keyword = $this->request('keyword');
        $sort = $this->request('sort');
        $start_price = (int)$this->request('start_price');
        $end_price = (int)$this->request('end_price');
        if($start_price === 0) $start_price = '';
        if($end_price === 0) $end_price = '';
        if($keyword == '搜索你感兴趣的商品') $keyword = '';	
        /*if(HttpRequest::isGet() && $keyword)
        {
            $keyword = mb_convert_encoding($keyword, 'utf-8', 'gbk');
            $this->request('keyword', $keyword);
        }*/
        if(preg_match('#林志玲|范冰冰|充气|自慰|真人#', $keyword) || $cid==2813) redirect(__URL__ , 3, '禁止搜索');
        $perpage = 33;
        $data = $this->taobaoClient->getTaoBaoKeItems(array('cid'=>$cid, 'sort'=>$sort, 'start_price'=>$start_price, 'end_price'=>$end_price, 'keyword'=>$keyword, 'page'=>$pageNum, 'page_size'=>$perpage));
        $url = __ACTION__;
     
        // 组装查询条件
        if($cid) $url .= "&cid={$cid}";
        if($keyword) $url .= '&keyword='.urlencode($keyword);
        if($sort) $url .= "&sort={$sort}";
        if($start_price) $url .= "&start_price={$start_price}";
        if($end_price) $url .= "&end_price={$end_price}";
        
        $page = new Page(array ('total' =>$data['count'], 'perpage' =>$perpage, 'url' => $url));
    	$this->assign('cats', $this->taobaoClient->getItemCates());
        $this->assign('list', $data['list']);
        $this->assign('page', $page->show(4));
        $this->assign('title', '淘宝网,淘宝商城,淘宝网首页');
        $this->assign('sorts', $this->sorts);
        $this->display();
    }
    
}