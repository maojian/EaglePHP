<?php

/**
 * 照片管理
 * @author maojianlw@139.com
 * @since  2011-12-29
 */

class PhotoController extends CommonController {
	
	private $curModel;
	
	public function __construct(){
		$this->curModel = model('photo');
	}
	
	
	/**
	 * 获取相册
	 */
	protected function getAlbum(){
		if($albums = model('album')->field('id,title')->select()){
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
		if($this->isPost()){
			$_POST['uploadtime'] = Date::format();
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
		if($this->isPost()){
			$_POST['uploadtime'] = Date::format();
			if($this->curModel->save()){
				$this->ajaxReturn(200, '修改成功', '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, '修改失败', '');
			}
		}else{
			$id = (int)$this->get('id');
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
		$ids = $this->request('ids');
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
		if($this->isPost() && count($this->file()) > 0){
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
				$thumbInfo = Image::thumb($uploadDir.$originalFile, $uploadDir.$thumbnailFile, '', $this->post('width'), $this->post('height'));
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
    	$id = (int)$this->request('id');
		$info = $this->curModel->where("id=$id")->find();
    	if($this->isPost()){
    		$x = (int)$this->post('x');
    		$y = (int)$this->post('y');
    		$w = (int)$this->post('w');
    		$h = (int)$this->post('h');
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