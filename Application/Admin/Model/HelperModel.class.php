<?php
class HelperModel extends Model{

    public function __construct()
    {
    
    }
    
    
    /**
     * 向指定的站点发出通告服务
     * @param string $url
     * @return bool
     */
    public function weblogUpdates($url, $abort = false)
    {
        $sites = getCfgVar('cfg_ping_sites');
        if(!$sites) return false;
        if($abort) abortConnect();
        $sitesArr = explode("\n", $sites);
        $webname = getCfgVar('cfg_webname');
        $host = "http://{$_SERVER['HTTP_HOST']}/";
        foreach ($sitesArr as $site)
        {
            $data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                     <methodCall>
                     <methodName>weblogUpdates.extendedPing</methodName>
                     <params>
                     <param><value><string>{$webname}</string></value></param>
                     <param><value><string>{$host}</string></value></param>
                     <param><value><string>{$url}</string></value></param>
                     <param><value><string>{$host}index.php/rss/xml</string></value></param>
                     </params>
                     </methodCall>";
            try
            {
                $return = curlRequest($site,$data,'post','',array("Content-type: text/xml;charset=\"gb2312\""));
            }catch (Exception $e)
            {
               //echo $e->getMessage();
            }
        }
                
    }
    
    /**
     * 递归循环获取子节点
     * @param int $id
     * @param array $list
     * @param bool $isnull
     */
    public function getChildRecursion($id, $list, $isnull=false){
          static $arr = array();
          if($isnull) $arr = null;
          if(is_array($list)){
              foreach($list as $val){
                  if($val['parent'] == $id){
                      $arr[$val['id']] = $val['title'];
                      $this->getChildRecursion($val['id'], $list, $isnull);
                  }elseif($val['id'] == $id){
                      $arr[$id] = $val['title'];
                  }
              }
          }
          return $arr;
     }
    

     /**
      * 获取子节点列表
      * @param array $typeIdArr
      */
    public function getChildList($typeIds='', $list=array())
    {
        $data = null;
        if(!empty($typeIds)){
            $typeIdArr = explode(',', $typeIds);
            foreach ($typeIdArr as $k=>$v)
            {
                $data[] = $this->getChildRecursion($v, $list);
            }
            $data = $data[count($data)-1];
        }
        return $data;
    }
    
    
    public function getNewsTypeList($typeIds='', &$return=array())
    {
        $list = model('news_type')->field('id,title,parent')->where('state=0')->select();
        $childArr = $this->getChildList($typeIds, $list);
        $return = is_array($childArr) ? array_keys($childArr) : null;
        return $this->getTree(0, $list, '', $childArr);
    }
    
    public function getChild($id, $list)
    {
        if(is_array($list)){
             $child = null;
             foreach ($list as $val){
                 if($val['parent'] == $id){
                     $child[] = $val;
                 }
             }
        }
        return $child;
    }
    
    
    public function getTree($id, $list, $symbol='', $typeIdArr='')
    {
        static $type_arr = array();
        $child = $this->getChild($id, $list);
        if(is_array($child)){
            foreach ($child as $val){
                  if(empty($typeIdArr) || (is_array($typeIdArr) && array_key_exists($val['id'], $typeIdArr)))
                  $type_arr[$val['id']] = $symbol.$val['title'].$symbol;
                  $this->getTree($val['id'], $list, $symbol.'&nbsp;&nbsp;&nbsp;&nbsp;', $typeIdArr);//
            }
        }
        return $type_arr;
    }
         
}