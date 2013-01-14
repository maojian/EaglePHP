<?php
class WishController extends CommonController
{
    
    private $curModel = null;
    
    public function __construct()
    {
        $this->curModel = model('wish');
    }
    
    
    public function indexAction()
    {
        $page = $this->page($this->curModel->where(true)->count());
		$list = $this->curModel->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();	
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
    }
    
    
	/**
	 * 删除
	 */
	public function deleteAction()
	{
		$ids = $this->request('ids');
		if(!empty($ids) && $this->curModel->where("id IN($ids)")->delete())
		{
			$this->ajaxReturn(200, '删除成功');
		}
		else
		{
			$this->ajaxReturn(300, '删除失败');
		}
	}


}