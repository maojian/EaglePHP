<?php
/**
 * 投票管理
 * @author maojianlw@139.com
 * @since 2.1 2012-9-7
 */

class VoteController extends CommonController{
 
    private $cur_model,$state_arr,$more_arr;
    
    public function __construct(){
        $this->state_arr = array(0=>'开启', 1=>'关闭');
        $this->more_arr = array(0=>'单选', 1=>'多选');
		$this->cur_model = model('vote');
	}
    
	public function indexAction()
	{
		$page = $this->page($this->cur_model->where(true)->count());
		$list = $this->cur_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
	    foreach ($list as &$val)
	    {
	        $val['state'] = $this->state_arr[$val['is_enable']];
	    }
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	
    public function lookupAction()
	{
		$page = $this->page($this->cur_model->where(true)->count());
		$list = $this->cur_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
	    foreach ($list as &$val)
	    {
	        $val['state'] = $this->state_arr[$val['is_enable']];
	    }
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	
	
	/**
	 * 客户端提交数据绑定
	 */
	private function dataHandle()
	{
	    $data = array();
	    foreach ($_POST as $k=>$v)
	    {
	        if(stripos($k, 'option_') !== false && !empty($v))
	        {
	            $data[] = array('name'=>$v, 'count'=>$this->post('count_'.substr($k, 7)));
	        }
	    }
	    $_POST['start_time'] = strtotime($this->post('start_time'));
	    $_POST['end_time'] = strtotime($this->post('end_time'));
	    $_POST['content'] = serialize($data);
	}
	

	public function addAction()
	{
		if($this->isPost())
		{
		    $this->dataHandle();
			if($id = $this->cur_model->add($_POST))
			{
			    $this->cur_model->getJs($id);
				$this->ajaxReturn(200, '添加成功');
			}
			else
			{
				$this->ajaxReturn(300, '添加失败');
			}
		}
		else
		{
		    $this->assign('state_arr', $this->state_arr);
		    $this->assign('more_arr', $this->more_arr);
			$this->display('Vote/action');
		}
	}
	

	public function updateAction()
	{
		if($this->isPost())
		{
		    $this->dataHandle();
			if($this->cur_model->save($_POST))
			{	
			    $this->cur_model->getJs($this->post('id'));
				$this->ajaxReturn(200, '修改成功');
			}
			else
			{
				$this->ajaxReturn(300, '修改失败');
			}
		}
		else
		{
			$id = (int)$this->get('id');
			$info = $this->cur_model->where("id=$id")->find();
			if($info) $this->assign('options', unserialize($info['content']));
			$this->assign('info', $info);
			$this->assign('state_arr', $this->state_arr);
			$this->assign('more_arr', $this->more_arr);
			$this->display('Vote/action');
		}
	}

	public function deleteAction()
	{
		$ids = $this->request('ids');
		if(!empty($ids) && $this->cur_model->where("id IN($ids)")->delete())
		{
		    $idArr = explode(',', $ids);
		    $dir = getUploadAddr().'vote/';
		    foreach ($idArr as $k=>$id)
		    {
		        $fileName = realpath($dir."vote_{$id}.js");
		        if(File::isFile($fileName))
		        {
		            File::del($fileName);
		        }
		    }
			$this->ajaxReturn(200, '删除成功');
		}
		else
		{
			$this->ajaxReturn(300, '删除失败');
		}
	}
	
	
	/**
	 * 获取代码
	 */
	public function getCodeAction()
	{
	    $id = (int)$this->request('id');
	    $this->assign('js', $this->cur_model->getJs($id));
	    $this->assign('html', $this->cur_model->getHtml($id));
	    $this->display();
	}
	
	
	/**
	 * 导出至Excel文件
	 */
	public function exportAction(){
		$data[0] = array('编号', '投票标题', '投票人数','内容');
		$voteList = $this->cur_model->field('id,name,total_count,content')->order('id DESC')->select();
		if(is_array($voteList)){
		    foreach ($voteList as &$v)
		    {
    		    $content = null;
    			$contentArr = unserialize($v['content']);
    			foreach ($contentArr as $k2=>$v2)
    			{
    			    $num = $k2+1;
    			    $content .= "{$num}、{$v2['name']}(投票总数：{$v2['count']})\t\n";
    			}
    			$v['content'] = $content;
		    }
			$data = array_merge($data, $voteList);
		}
		$xls = new Excel('UTF-8', false, '投票列表');
		$xls->addArray($data);
		$xls->generateXML('vote_'.date('YmdHis'));
	}
 
}