<?php

class NewsController extends CommonController{

	private $curModel = null;
    
    public function __construct(){
    	$this->curModel = M('news');
    }
	
	/**
	 * 新闻列表页
	 */
    public function indexAction(){
        $type_id = (int)$_GET['type'];
        if($type_id){
            $type_info = M('news_type')->field('title')->where("id=$type_id")->find();
            $this->assign('type_name', $type_info['title']);
            $this->assign('title', $type_info['title']);
        }else{
            $this->assign('title', '文章搜索');
        }
        
    	$this->display();
    }
    
    
    public function getDataAction(){
        $id = (int)$_GET['id'];
        $this->curModel->where("id=$id")->save(array('clicknum'=>array('exp'=>'clicknum+1')));
        
        $list = M('comment')->field('id,name,content,create_time,email')->where("news_id=$id")->order('id DESC')->select();
        if(is_array($list)){
            $helper = M('helper');
            foreach ($list as $val){
                 $img = $helper->getGravatarByEmail($val['email'], $val['id']);
                 $html .= '<li '.((intval($val['id'])%2 == 0) ? 'class="style2"' : '').'>
                 <img alt="" src="'.$img.'">
                 <div class="info">
                 <h4><a>'.$val['name'].'</a> <span class="time">'.$val['create_time'].'</span></h4>'.$val['content'].'</div>
                 <div class="clear"></div>
                 </li>';
            }
        }
        $info = $this->curModel->field('clicknum,comments')->getbyId($id);
        $html = $html ? $html : '<li id="no_comment"><div class="info">^_^，沙发哦，暂无评论...</div></li>';
        $this->ajaxReturn(200, 'ok', '', '', '', array('comment_list'=>$html, 'clicknum'=>$info['clicknum'], 'comments'=>$info['comments']));
    }
    
    /**
     * 文章内容分页
     * @param int $total
     * @param array $info
     * @param int $page
     */
    private function contentPage($total, $info, $page)
    {
        $pageStr = '<span class="pager"><span class="total">共'.$total.'页</span><span class="pages">';
        if($page > 0)
        {
            $pageStr .= '<a class="" href="'.$this->curModel->getHtmlLink($info, 0).'">第一页</a>';
            $pageStr .= '<a class="" href="'.$this->curModel->getHtmlLink($info, $page-1).'">上一页</a>';
        }
        
        for($i=1; $i<=$total; $i++)
        {
            
            if(($page==0 ? $page+1 : $page) == $i)
            {
                $pageStr .= '<span>&nbsp;'.$i.'&nbsp;</span>';   
            }
            else
            {
                $pageStr .= '<a class="page" href="'.$this->curModel->getHtmlLink($info, ($i==1 ? 0 : $i)).'">'.$i.'</a>';
            }
        }

        if($page != $total)
        {
            $pageStr .= '<a class="" href="'.$this->curModel->getHtmlLink($info, $page+1).'">下一页</a>';
            $pageStr .= '<a class="" href="'.$this->curModel->getHtmlLink($info, $total).'">最后一页</a>';
        }
        return $pageStr;
    }
    
    
    /**
     * 新闻内容页
     */
    public function showAction(){
    	$id = (int)$_GET['id'];
    	$info = $this->curModel->field('id,type,title,content,create_time,keywords,auth,source,description')->where("id=$id")->find();
    	if($info){
    	    $title = $info['title'];
    	    $info['shortTitle'] = (strlen($title)>30 ? utf8Substr($title, 0, 10).'...' : $title);
    		$info['content'] = html_entity_decode($info['content']);
    		
    		$contentArr = explode('#page#', $info['content']);    	
        	$total =  count($contentArr);
        	
        	// 文章内容分页
        	if($total > 0)
        	{
        	    $page = intval($_GET['page']);
        	    $page = ($page > $total) ? 1 : $page;
        	    $page = ($page == 0) ? 1 : $page;
        	    $info['content'] = $contentArr[$page-1];
        	    $this->assign('totalPage', $total);
        	    $this->assign('page', $this->contentPage($total, $info, $page));
        	}
    		
    		// 获取父节点类型
    		$type_id = (int)$info['type'];
    		$type_arr = M('news_type')->field('id,title,parent')->select();
            $parent_arr = M('helper')->getParent($type_id, $type_arr);
            
    		$this->assign('type_arr', array_reverse($parent_arr, true));
    		$this->assign('type_info', array('id'=>$type_id, 'title'=>$parent_arr[$type_id]));
    		$this->assign('relation_list', $this->curModel->getRelation($type_id, $id));
    	}
    	
    	$keywords = $info['keywords'];
    	if($keywords){
    	    $k_arr = explode(',', $keywords);
    	    $this->assign('key_arr', $k_arr);
    	}
    	
    	$this->assign('title', "{$info['title']} | {$parent_arr[$type_id]}");
    	$this->assign('keywords', $keywords);
    	$this->assign('description', $info['description']);
    	$this->assign('info', $info);
    	$this->display();
    }
    
    public function commentAction(){
        if(count($_POST) > 0){
			 //$this->ajaxReturn(300, '很抱歉，五一假期评论功能将暂时关闭，谢谢关注！');
             $yzm = $_POST['yzm'];
             $content = $_POST['content'];
             if(Session::get('verify') != md5($_POST['yzm'])){
                 $this->ajaxReturn(300, '验证码错误，请重新输入！');
             }
             if(preg_match('#('.getCfgVar('cfg_filter_word').')#i', $content)){
                 $this->ajaxReturn(300, '评论内容包含禁词，请检查！');
             }
             $_POST['ip'] = get_client_ip();
             $_POST['create_time'] = date('Y-m-d H:i:s');
             if($id = M('comment')->add()){
                 $helper = M('helper');
                 $this->curModel->where("id={$_POST['news_id']}")->save(array('comments'=>array('exp'=>'comments+1')));
                 $img = $helper->getGravatarByEmail($_POST['email'], $id);
                 $html = '<li '.((intval($id)%2 == 0) ? 'class="style2"' : '').'>
                 <img alt="" src="'.$img.'">
                 <div class="info">
                 <h4><a>'.$_POST['name'].'</a> <span class="time">'.$_POST['create_time'].'</span></h4>'.$content.'</div>
                 <div class="clear"></div>
                 </li>';
                 $this->ajaxReturn(200, $html);
             }
        }
        $this->ajaxReturn(300, '评论失败，请稍后再试！');
    }
    
}
?>