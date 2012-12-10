<?php

/**
 * income manager class
 * @author maojianlw@139.com
 * @since 2012-1-27
 */

class IncomeController extends CommonController{

    private $curModel;
	
	public function __construct(){
		$this->curModel = model('income');
	}
	
	
	/**
	 * 列表页
	 */
	public function indexAction(){
	    $remark = $this->request('remark');
	    $startTime = $this->request('startTime');
	    $endTime = $this->request('endTime');
	    $sql = null;
	    if($remark){
	     $sql[] = "remark LIKE '%{$remark}%'";
	    }
	    if($startTime && $endTime){
	     $sql[] = "(usetime BETWEEN '{$startTime}' AND '{$endTime}')"; 
	    }
		$page = $this->page($this->curModel->where($sql)->count(), 'usetime');
		$list = $this->curModel->where($sql)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();	
		if($list)
		{
			foreach($list as &$val)
			{
				$val['money'] = number_format($val['money'], 2);
			}
		}
		$incomeSum = $this->curModel->field('money')->where($sql)->sum();
		$this->assign('incomeSum', number_format($incomeSum, 2));
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	

	/**
	 * 添加
	 */
	public function addAction(){
		if($this->isPost()){
			if($this->curModel->add()){
				$this->ajaxReturn(200, '添加成功');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
			$this->display();
		}
	}
	
	/**
	 * 修改
	 */
	public function updateAction(){
		if($this->isPost()){
			if($this->curModel->save()){
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$this->get('id');
			$info = $this->curModel->where("id=$id")->find();
			$this->assign('info',$info);
			$this->display();
		}
	}
	
	/**
	 * 删除
	 */
	public function deleteAction(){
		$ids = $this->request('ids');
		if(!empty($ids) && $this->curModel->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
	
}
?>