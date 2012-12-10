<?php
/**
 * 淘宝客户端API
 * @author maojianlw@139.com
 * @since 2012-7-24
 */
import('Sdk.taobao.TopClient', false);
import('Sdk.taobao.Logger', false);
import('Sdk.taobao.RequestCheckUtil', false);

class ClientApi
{
    
    private static $pid = null;
    private static $client = null;
    private static $instance = null;
    
    /**
     * 获取api client实例
     */
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new ClientApi();
            self::$pid = getCfgVar('cfg_taobao_pid');
            self::$client = new TopClient();
            self::$client->appkey = getCfgVar('cfg_taobao_appkey');
            self::$client->secretKey = getCfgVar('cfg_taobao_secret');
            self::$client->format = 'json';
        }
        return self::$instance;
    }
    
    /**
     * 查询淘宝客推广商品
     * @param array $param
     */
    public static function getTaoBaoKeItems($param){
        import('Sdk.taobao.request.TaobaokeItemsGetRequest', false);
        $req = new TaobaokeItemsGetRequest;
        $req->setFields('num_iid,title,nick,item_location,seller_credit_score,pic_url,price,click_url,shop_click_url,commission,commission_rate,commission_volume,commission_num,volume');
        $req->setPid(self::$pid);
        $req->setKeyword($param['keyword']);
        $req->setPageNo($param['page']);
        $req->setPageSize($param['page_size']);
        $req->setCid($param['cid']);
        $req->setSort((isset($param['sort']) && $param['sort'])?$param['sort']:'commissionNum_desc');
        $req->setStartPrice(isset($param['start_price']) ? $param['start_price'] : '');
        $req->setEndPrice(isset($param['end_price']) ? $param['end_price'] : '');
        $req->setRealDescribe('true');
        $object = self::$client->execute($req);
        $data = array();
        $data['list'] = isset($object->taobaoke_items) ? $object->taobaoke_items->taobaoke_item : null;
        $data['count'] = isset($object->total_results) ? $object->total_results : 0;
        return $data;
    } 
    
    /**
     * 获取后台供卖家发布商品的标准商品类目。 
     */
    public static function getItemCateList($parent_id=0){
        import('Sdk.taobao.request.ItemcatsGetRequest', false);
        $req = new ItemcatsGetRequest();
		$req->setFields('cid,parent_cid,name,is_parent');
		$req->setParentCid($parent_id);
		$object = self::$client->execute($req);
		return $object->item_cats->item_cat;
    }
    
    
    /**
     * 获取商品类目的id和名称，以键值对的方式组成
     */
    public static function getItemCates(){
        $key = 'taobao_cat_cache';
        $data = cache($key);
        if(!$data){
            $cats = self::getItemCateList();
            if(is_array($cats))
            foreach ($cats as $val){
                $data[$val->cid] = $val->name;
            }
            cache($key, $data, 86400);
        }
        return $data;
    }
    
}