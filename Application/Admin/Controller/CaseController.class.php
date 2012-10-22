<?php
/**
 * 案例管理
 * @author maojianlw@139.com
 * @version 1.8
 * @since www.eaglephp.com
 */
class CaseController extends CommonController{
 
    private $cur_model;
    
    public function __construct(){
        $this->state_arr = array(0=>'开启', 1=>'关闭');
		$this->cur_model = M('case');
	}
    
	public function indexAction(){
		$page = $this->page($this->cur_model->where(true)->count());
		$list = $this->cur_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		if($list){
		    foreach ($list as &$val){
		        $val['state'] = $this->state_arr[$val['state']];
		    }
		}
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	private function uploadPhoto(){
		$uploadObj = new Upload();
	    $uploadDir = getUploadAddr();
	    $url = $_POST['url'];
	    if($url && strpos($url, 'http://') === false){
	        $this->ajaxReturn(300, '链接输入错误，正确链接如：http://www.eaglephp.com');
	    }
	    $uploadObj->allowTypes = array('image/gif','image/jpg','image/jpeg', 'image/pjpeg','image/bmp','image/x-png');
	    if(!empty($_FILES['img']['tmp_name'])){
	         $case_dir = getCfgVar('cfg_case_dir');
	         $upload_dir = $uploadDir.$case_dir;
	         $file_info = $uploadObj->uploadOne($_FILES['img'], $upload_dir);
	         if($file_info !== false){
	             $file_name = $file_info[0]['savename'];
				 Image::thumb($upload_dir.$file_name, $upload_dir.$file_name, '', 200, 130); 
	             $_POST['img'] = $case_dir.$file_name;
	         }else{
	             $this->ajaxReturn(300, $uploadObj->getErrorMsg());
	         }
	    }
	}

	public function addAction(){
		if(count($_POST) > 0){
			$_POST['create_time'] = date('Y-m-d H:i:s');
			$this->uploadPhoto();
			if(empty($_POST['img'])){
				$this->ajaxReturn(300, '图片不能为空');
			}
			if($this->cur_model->add()){
				$this->ajaxReturn(200, '添加成功');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
		    $this->assign('state_arr', $this->state_arr);
			$this->display('Case/action');
		}
	}
	

	public function updateAction(){
		if(count($_POST) > 0){
			$this->uploadPhoto();
		    if(!$_POST['img']){
		    	unset($_POST['img']);
		    }
			if($this->cur_model->save()){	
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$_REQUEST['id'];
			$info = $this->cur_model->where("id=$id")->find();
			$this->assign('info', $info);
			$this->assign('state_arr', $this->state_arr);
			$this->display('Case/action');
		}
	}

	public function deleteAction(){
		$ids = $_REQUEST['ids'];
		$sql = "id IN($ids)";
		$list = $this->cur_model->field('img')->where($sql)->select();
		foreach($list as $v){
		     $file = getUploadAddr().$v['img'];
		     if(file_exists($file)){
		         unlink($file);
		     }
		}
		if(!empty($ids) && $this->cur_model->where($sql)->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
 
}