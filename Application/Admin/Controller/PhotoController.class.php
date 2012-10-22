<?php

/**
 * 照片管理
 * @author maojianlw@139.com
 * @since  2011-12-29
 */

class PhotoController extends CommonController {
	
	private $curModel;
	
	public function __construct(){
		$this->curModel = M('photo');
	}
	
	
	/**
	 * 获取相册
	 */
	protected function getAlbum(){
		if($albums = M('album')->field('id,title')->select()){
			foreach($albums as $v){
				$data[$v['id']] = $v['title'];
			}
		}
		return $data;
	}
	
	/**
	 * 列表页
	 */
	public function indexAction(){
		$page = $this->page($this->curModel->where(true)->count());
		$list = $this->curModel->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();	
		$albums = $this->getAlbum();
		if(is_array($list)){
			foreach($list as &$v){
				$v['albumName'] = $albums[$v['albumid']];
				$v['thumbnail'] = $v['thumbnail'].'?code='.rand(10000,99999);
			}
		}
		$this->assign('albums', $albums);
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
	}
	

	/**
	 * 添加
	 */
	public function addAction(){
		if(count($_POST) > 0){
			$_POST['uploadtime'] = date('Y-m-d H:i:s');
			$_POST['uid'] = $this->uid;
			if($this->curModel->add()){
				$this->ajaxReturn(200, '添加成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
			$this->assign('albums', $this->getAlbum());
			$this->display();
		}
	}
	
	/**
	 * 修改
	 */
	public function updateAction(){
		if(count($_POST) > 0){
			$_POST['uploadtime'] = date('Y-m-d H:i:s');
			if($this->curModel->save()){
				$this->ajaxReturn(200, '修改成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '修改失败', '');
			}
		}else{
			$id = (int)$_REQUEST['id'];
			$info = $this->curModel->where("id=$id")->find();
			$this->assign('info',$info);
			$this->assign('albums', $this->getAlbum());
			$this->display();
		}
	}
	
	/**
	 * 删除
	 */
	public function deleteAction(){
		$ids = $_REQUEST['ids'];
		$where = "id IN($ids)";
		if(!empty($ids)){
			$list = $this->curModel->field('original,middle,thumbnail')->where($where)->select();
			$this->curModel->where($where)->delete();
			if($list){
				foreach($list as $fileInfo){
					$uploadDir = getUploadAddr();
					$originalFile = $uploadDir.$fileInfo['original'];
					$middleFile = $uploadDir.$fileInfo['middle'];
					$thumbnailFile = $uploadDir.$fileInfo['thumbnail'];
					if(file_exists($originalFile)){
						unlink($originalFile); // 删除原图
					}
					if(file_exists($middleFile)){
						unlink($middleFile); // 删除中图
					}
					if(file_exists($thumbnailFile)){
						unlink($thumbnailFile); // 删除缩略图
					}
				}
			}
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
	
	/**
	 * 上传照片
	 */
	public function uploadAction(){
		if(count($_POST) > 0 && count($_FILES) > 0){
			$_POST['title'] = substr($_FILES['Filedata']['name'], 0, strrpos($_FILES['Filedata']['name'], '.'));
			
			$uploadDir = getUploadAddr();
			
			// 上传图片
			$originalDir = getCfgVar('cfg_original_imgdir');
			$middleDir = getCfgVar('cfg_middle_imgdir');
			$thumbnailDir = getCfgVar('cfg_thumbnail_imgdir');

			$fileName = $this->upload($uploadDir.$originalDir,'*');
			
			$originalFile = $originalDir.$fileName;
			$middleFile = $middleDir.$fileName;
			$thumbnailFile = $thumbnailDir.$fileName;
			
			// 缩略图处理
			if($fileName !== false){
				$bigInfo = Image::thumb($uploadDir.$originalFile, $uploadDir.$middleFile, '', 500, 500); 
				$thumbInfo = Image::thumb($uploadDir.$originalFile, $uploadDir.$thumbnailFile, '', $_POST['width'], $_POST['height']);
				if($bigInfo !== false && $thumbInfo !== false){
					$_POST['original'] = $originalFile;
					$_POST['middle'] = $middleFile;
					$_POST['thumbnail'] = $thumbnailFile;
					$_POST['uploadtime'] = date('Y-m-d H:i:s');
					$this->curModel->add();
				}
			}
		}else{
			$this->assign('PHPSESSID', session_id());
			$this->assign('albums', $this->getAlbum());
			$this->display();
		}
	}
    
    
    /**
     * 图片裁剪
     */
    public function cutAction(){
    	$id = (int)$_REQUEST['id'];
		$info = $this->curModel->where("id=$id")->find();
    	if(count($_POST) > 0){
    		$x = (int)$_POST['x'];
    		$y = (int)$_POST['y'];
    		$w = (int)$_POST['w'];
    		$h = (int)$_POST['h'];
    		$uploadDir = getUploadAddr();
    		$middleFile = $uploadDir.$info['middle'];
    		$thumbnailFile = $uploadDir.$info['thumbnail'];
			$thumbInfo = Image::crop($middleFile, $thumbnailFile, '', $w, $h, $x, $y);
			if($thumbInfo !== false){
				$this->ajaxReturn(200, '修改成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}	
    	}
		$this->assign('info',$info);
		$this->display();
    }
    
}
?>