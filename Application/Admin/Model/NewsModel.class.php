<?php
class NewsModel extends Model
{
        
	/**
	 * 生成静态文件
	 */
	public function makeHtml($news_info)
	{
	    set_time_limit(0);
	    if(getCfgVar('cfg_html_make') == 0) return false;
	    $dir = (PUB_DIR.getCfgVar('cfg_html_dir').__DS__.Date::format('Ymd', $news_info['create_time']).__DS__);
	    mk_dir($dir);
	    $count = count(explode('#page#', $news_info['content']));
	    for($i=1; $i<=$count; $i++)
	    {
	        $flag = ($i==1) ? 0 : $i;
	        $content = File::read($this->getHref($news_info['id'], $flag));
	        File::write($dir.$news_info['id'].(!$flag ? '' : "_{$flag}").'.html', $content);
	    }
	    return true;
	}
    
	/**
	 * 获得新闻地址
	 * @param int $news_id
	 */
	public function getHref($news_id, $page=0)
	{
	    $url = HttpRequest::getHostInfo().__PROJECT__.'index.php?c=news&a=show&static=1&id='.$news_id.($page ? '&page='.$page : '');
	    return url($url, false, 3);
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Model::_beforeAdd()
	 */
	protected function _beforeAdd(&$data, $options)
	{
	    
	    return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model::_afterAdd()
	 */
	protected function _afterAdd(&$data, $options)
	{
	    
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model::_beforeUpdate()
	 */
	protected function _beforeUpdate(&$data, $options)
	{
	    return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model::_afterUpdate()
	 */
	protected function _afterUpdate(&$data, $options)
	{

	}
	
	
	/**
	 * 生成全部的静态文件
	 * 
	 * @param int $type
	 * @param int $start_id
	 * @param int $end_id
	 */
	public function makeList($type, $start_id, $end_id)
	{
	    $i = 0;
	    if(getCfgVar('cfg_html_make') == 0) return $i;
	    $sql = '1=1';
	    if($type) $sql .= " AND type=$type";
	    if($start_id && $end_id) $sql .= " AND (id BETWEEN $start_id AND $end_id)";
	    $list = $this->field('id,create_time,content')->where($sql)->select();
	    if(is_array($list))
	    {
	        foreach ($list as $val)
	        {
	            if(!$this->makeHtml($val)) break;
	            else $i++;
	        }
	    }
	    return $i;
	}
	
	/**
	 * 删除静态文件
	 */
	public function delHtml($val)
	{
	    $count = count(explode('#page#', $val['content']));
	    for($i=0; $i<$count; $i++)
	    {
	        $file = PUB_DIR.getCfgVar('cfg_html_dir').'/'.Date::format('Ymd', $val['create_time']).'/'.$val['id'].($i>0 ? "_{$i}" : '').'.html';
	        File::isFile($file) && File::del($file);
	    }
	    return true;
	}
	
}