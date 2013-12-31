<?php
/**
 * 文件管理
 * @copyright maojianlw@139.com
 * @since 2012-03-29 22:23
 */
class FileController extends CommonController{

     private $base_dir = null;
    
     public function __construct(){
         $this->base_dir = dirname(ROOT_DIR);
     }
     
     public function indexAction(){
          $active_path = $this->request('active_path');
          if(empty($active_path)){
              $active_path = '/'.basename(ROOT_DIR);
          }
          $active_path = str_replace('\\', '/', $active_path);
          $this->assign('up_path', urlencode(dirname($active_path)));
          $this->assign('active_path', $active_path);
          $this->assign('list', $this->getDirFile($active_path));
          $this->display();
     }
     
     
     /**
      * 返回指定目录下的文件列表
      */
     public function getDirFile($path){
          if($path == '/') $path = '';
          $absloute_path = $this->base_dir.$path.'/';
          $dir = opendir($absloute_path);
          $dirs = array();
          $files = array();
          while($file = readdir($dir)){
               if(in_array($file, array('.', '..'))) continue;
               $file_path = $absloute_path.$file;
               $action_path = urlencode($path.'/'.$file);
               //$file = iconv('gbk', 'utf-8', $file);
               if(is_dir($file_path)){
                   $dirs[] = array('name'=>$file, 'img'=>'dir', 'edit'=>0, 'active_path'=>$action_path);
               }else{
                   $size = getFileSize(filesize($file_path));
                   $date = date('Y-m-d H:i:s',filemtime($file_path));
                   $row = array('name'=>$file, 'size'=>$size, 'date'=>$date, 'active_path'=>$action_path);
                   $files[] = array_merge($row, $this->matchFile($file));
               }
          }
          closedir($dir);
          return array_merge($dirs, $files);
     }
     
     /**
      * 匹配文件格式
      * @param string $file
      * @return array
      */
     private function matchFile($file){
          $reg_arr = array(
          			'gif|png' => array('img'=>'gif', 'edit'=>0), 
          			'jpg' => array('img'=>'jpg', 'edit'=>0),
                    'swf|fla|fly' => array('img'=>'flash', 'edit'=>0),
                    'zip|rar|tar.gz' => array('img'=>'zip', 'edit'=>0),
                    'exe' => array('img'=>'exe', 'edit'=>0),
                    'mp3|wma' => array('img'=>'mp3', 'edit'=>0),
                    'wmv|api' => array('img'=>'wmv', 'edit'=>0),
                    'rm|rmvb' => array('img'=>'rm', 'edit'=>0),
                    'txt|inc|pl|cgi|asp|xml|xsl|aspx|cfm|log|bak' => array('img'=>'txt', 'edit'=>1),
                    'htm|html|tpl' => array('img'=>'htm', 'edit'=>1),
                    'php' => array('img'=>'php', 'edit'=>1),
                    'js' => array('img'=>'js', 'edit'=>1),
                    'css' => array('img'=>'css',  'edit'=>1)
                 );
          foreach($reg_arr as $k=>$v){
              if(preg_match("#\.({$k})#i", $file)){
                 return $v;
                 break;
              }
          }
          return array('img'=>'file_unknow', 'edit'=>0);
     }
     
     /**
      * 编辑文件
      */
     public function editAction(){
          $file = $this->base_dir.$this->request('file');
          if($content = $this->request('content', self::_NO_CHANGE_VAL_, false)){
              if(!is_writable($file)) $this->ajaxReturn(300, '文件无法写入！');
              file_put_contents($file, $content);
              $this->ajaxReturn(200, '文件编辑成功！');
          }
          if(!file_exists($file)) $this->ajaxReturn(300, '文件不存在！');
          if(!is_readable($file)) $this->ajaxReturn(300, '文件无法读取！');
          
          $this->assign('content', file_get_contents($file));
          $this->display();
     }
     
     /**
      * 删除文件
      */
     public function deleteAction(){
          $file = $this->base_dir.$this->request('file');
          if(is_file($file)){
              if(@unlink($file)) $this->ajaxReturn(200, '文件已删除成功！');
          }elseif(is_dir($file)){
              rm_dir($file);
              $this->ajaxReturn(200, '目录已删除成功！');
          }
          $this->ajaxReturn(300, '删除失败，您可能没有权限操作！');
     }
     
     /**
      * 创建文件或文件夹
      */
     public function createAction(){
          $type = $this->request('type');
          $file = $this->base_dir.$this->request('file');
          $file_name = $this->request('file_name');
          if($this->isPost()){
              $path = $file.'/'.$file_name;
              if($type == 'file'){
                   if(file_exists($path)){
                       $this->ajaxReturn(300, '文件已经存在！');
                   }
                   if(file_put_contents($path, $this->post('content', self::_NO_CHANGE_VAL_, false))){
                        $this->ajaxReturn(200, '文件创建成功！');
                   }else{
                        $this->ajaxReturn(300, '文件创建失败！');
                   }
              }else{
                   if(is_dir($path)){
                       $this->ajaxReturn(300, '目录已经存在！');
                   }
                   if(mk_dir($path)){
                        $this->ajaxReturn(200, '目录创建成功！');
                   }else{
                        $this->ajaxReturn(300, '目录创建失败！');
                   }
              }
          }
          $this->assign('file', $file);
          $this->display();
     }
     
	 /**
      * 改名
      */
     public function renameAction(){
          $file = $this->base_dir.$this->request('file');
          if($new_name = $this->request('new_name')){
              $new_name = dirname($file).'/'.$new_name;
              if(is_writable($file) && $new_name!=$file){
                  if(rename($file, $new_name)){
                      $this->ajaxReturn(200, '文件重命名成功！');
                  }
              }
              $this->ajaxReturn(200, '文件重命名失败！');
          }
          $this->assign('old_name', basename($file));
          $this->display();
     }
     
	/**
     * 移动
     */
     public function moveAction(){
          $file = $this->base_dir.$this->request('file');
          if($news_dir = $this->request('new_dir', self::_NO_CHANGE_VAL_, false)){
              $new_dir = rtrim(ltrim($news_dir, '/'), '/');
              if($new_dir == '' || preg_match('#\.\.#', $new_dir))
              {
                  $this->ajaxReturn(300, '路径不合法，请再重新输入！');
              }
              $new_dir = $this->request('cur_dir', self::_NO_CHANGE_VAL_, false).'/'.$new_dir.'/';
              if(!is_dir($new_dir)){
                  mk_dir($new_dir);
              }
              if(is_readable($file) && is_readable($new_dir) && is_writable($new_dir)){
                  if(copy($file, $new_dir.basename($file))){
                      unlink($file);
                      $this->ajaxReturn(200, '文件移动成功！');
                  }
              }
              $this->ajaxReturn(300, '文件移动失败！');
          }
          $this->assign('move_name', basename($file));
          $this->assign('cur_dir', dirname($file));
          $this->display();
     }
     
     
    /**
     * 空间大小
     */
     public function spaceAction(){
          $file = $this->request('file');
          $size = getFileSize(checkFileSize($this->base_dir.$file));
          echo "<br/><i>目录 <b>{$file}</b> 的使用状况：{$size}</i>";
     }
     
     /**
      * 上传文件
      */
     public function uploadAction(){
          $path = $this->base_dir.$this->request('file');
          if($this->isPost() && count($this->file()) > 0){
		     $upload_boj = new Upload();
             $upload_boj->saveRule = '';
             $upload_boj->allowTypes = '';
             $upload_boj->upload($path.'/');
    	  }else{
             $this->assign('PHPSESSID', session_id());
             $this->assign('cur_dir', $this->request('file'));
             $this->display();
    	  }
     }
     
     
}