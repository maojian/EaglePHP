<?php
class NewsModel extends Model{
    
	/**
	 * 生成静态文件
	 */
	public function makeHtml($news_id)
	{
	    set_time_limit(0);
	    if(getCfgVar('cfg_html_make') == 0) return false;
	    $news_info = $this->where("id=$news_id")->field('create_time,content')->find();
	    $dir = (PUB_DIR.getCfgVar('cfg_html_dir').__DS__.date('Ymd',strtotime($news_info['create_time'])).__DS__);
	    mk_dir($dir);
	    
	    import('Util.File');
	    $count = count(explode('#page#', $news_info['content']));
	    for($i=1; $i<=$count; $i++)
	    {
	        $flag = ($i==1) ? 0 : $i;
	        $content = File::read($this->getHref($news_id, $flag));
	        File::write($dir.$news_id.(!$flag ? '' : "_{$flag}").'.html', $content);
	    }
	    return true;
	}
    
	/**
	 * 获得新闻地址
	 * @param int $news_id
	 */
	public function getHref($news_id, $page=0)
	{
	    return 'http://'.$_SERVER['HTTP_HOST'].__PROJECT__.'index.php/news/show/static/1/id/'.$news_id.($page ? '/page/'.$page : '');
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
	 */
	public function makeAllHtml(){
	    $i = 0;
	    if(getCfgVar('cfg_html_make') == 0) return false;
	    $list = $this->field('id')->select();
	    if(is_array($list)){
	        foreach ($list as $val){
	            if(!$this->makeHtml($val['id'])){
	                break;
	            }else{
	                $i++;
	            }
	        }
	    }
	    return $i;
	}
	
	/**
	 * 删除静态文件
	 */
	public function delHtml($val){
	    import('Util.File');
	    $count = count(explode('#page#', $val['content']));
	    for($i=0; $i<$count; $i++)
	    {
	        $file = PUB_DIR.getCfgVar('cfg_html_dir').'/'.date('Ymd',strtotime($val['create_time'])).'/'.$val['id'].($i>0 ? "_{$i}" : '').'.html';
	        File::isFile($file) && File::del($file);
	    }
	    return true;
	}
	
}