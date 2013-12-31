<?php
/**
 * 数据库备份与还原
 * @author maojianlw@139.com
 * @link http://eaglephp.googlecode.com/
 */
class DatabaseController extends CommonController{

    private $cur_model = null;
    private $data_dir = null;
 
    public function __construct(){
        $this->data_dir = DATA_DIR.getCfgVar('cfg_backup_dir').__DS__;
        $this->cur_model = model('db');
    }
    
    public function indexAction(){
        $type = $this->get('type');
        if(method_exists($this, $type)){
            $this->$type();
        }else{
            $this->assign('list', $this->cur_model->query('SHOW TABLE STATUS'));
            $this->display();
        }
    }
    
    /**
	 * 删除备份文件
     */
    private function delBak(){
        $ids = $this->request('ids');
        $i = 0;
        if($ids){
             $date = $this->request('date');
             $tables = explode(',', $ids);
             foreach ($tables as $table){
                 if(unlink($this->data_dir.$date.__DS__.$table)) $i++;
             }
        }
        $this->ajaxReturn(200, "成功删除  $i 个备份文件！");
    }

    /**
     * 优化表
     */
    private function optimize(){
        $table = $this->request('id');
        if($this->cur_model->execute("OPTIMIZE TABLE `{$table}` ")){
             $this->ajaxReturn(200, "执行优化表： $table  OK！");
        }else{
             $this->ajaxReturn(300, "执行优化表： $table  失败，原因是：".$this->cur_model->getDbError());
        }
    }
    
    /**
     * 优化所有表
     */
    private function optimizeAll(){
        $ids = $this->request('ids');
        $i = 0;
        if($ids){
             $tables = explode(',', $ids);
             foreach ($tables as $table){
                 if($this->cur_model->execute("OPTIMIZE TABLE `{$table}` ")) $i++;
             }
        }
        $this->ajaxReturn(200, "已成功优化 $i 张表！");
    }
    
    /**
     * 修复所有表
     */
    private function repairAll(){
        $ids = $this->request('ids');
        $i = 0;
        if($ids){
             $tables = explode(',', $ids);
             foreach ($tables as $table){
                 if($this->cur_model->execute("REPAIR TABLE `{$table}` ")) $i++;
             }
        }
        $this->ajaxReturn(200, "已成功修复 $i 张表！");
    }
    
    /**
     * 修复表
     */
    private function repair(){
        $table = $this->request('id');
        if($this->cur_model->execute("REPAIR TABLE `{$table}` ")){
             $this->ajaxReturn(200, "修复表： $table  OK！");
        }else{
             $this->ajaxReturn(300, "修复表： $table  失败，原因是：".$this->cur_model->getDbError());
        }
    }
    
    /**
     * 表结构
     */
    private function show(){
         $table = $this->get('id');
         $info = $this->cur_model->query("SHOW CREATE TABLE `{$table}` ");
         $this->assign('info', $info[0]['Create Table']);
         $this->display('Database/show');
    }
    
    private function changeVal($val){
         $val = str_replace("\r", "\\r", $val);
         $val = str_replace("\n", "\\n", $val);
         return addslashes($val);
    }
    
    private function regainVal($val){
         $val = str_replace("\\r", "\r", $val);
         $val = str_replace("\\n", "\n", $val);
         return $val;
    }
    
    /**
     * 数据库备份
     */
    private function bak(){
         $ids = $this->post('ids');
         if($ids){
             $tables = explode(',', $ids);
             $data_dir = $this->data_dir.date('Y-m-d').__DS__;
             if(!is_dir($data_dir)){
                 mk_dir($data_dir);
             }
             
             $i = 0;
             $line = ";\r\n\r\n";
             foreach ($tables as $t){
                 $bak_str = "DROP TABLE IF EXISTS `{$t}`{$line}";
                 $info = $this->cur_model->query("SHOW CREATE TABLE `{$t}` ");
                 $bak_str .= $info[0]['Create Table'].$line;
                 
                 $fields = $this->cur_model->getFieldList($t);
                 $field_arr = array_keys($fields['_type']);
                 $field_str = '`'.implode('`, `', $field_arr).'`';
                 
                 $insert = "INSERT INTO `$t`({$field_str}) VALUES \r\n";
                 $list = model($t)->select();
 
                 if(is_array($list)){
                     $z = 0;
                     $max = 100;
                     $count = count($list)-1;
                     foreach($list as $k=>$row){
                      
                         $insert_val = null;
                         foreach($fields['_type'] as $fieldName=>$type){
                            $value = $this->changeVal($row[$fieldName]);
                            if(strpos($type, 'int') !== false){
              					$value = (int)$value;
              				}elseif(in_array($type, array('float', 'double'))){
              					$value = (float)$value;
              				}else{
              				    $value = "'{$value}'";
              				}
              				$insert_val .= $value.', ';
                         }
                         
                         // value 值串联
                         $insert_val = '('.rtrim($insert_val, ', ').')';
                         if($k == $count){
                            $sql = ($count == 0 ? $insert : '').$insert_val.';';
                         }elseif($z == $max-1){
                            $sql = $insert_val.';';
                         }elseif($z == 0 || $z == $max){
                            $sql = $insert.$insert_val.',';
                            $z = 0;
                         }else{
                            $sql = $insert_val.',';
                         }
                         $bak_str .= $sql."\r\n";
                         $z++;
                         
                     }
                 }
                 
                 if(file_put_contents($data_dir.$t.'.bak', $bak_str)) $i++;
             }
         }
         $this->ajaxReturn(200, "已成功备份 {$i} 张表！");
    }
    
    /**
     * 数据库还原
     */
    public function doneAction(){
        $ids = $this->post('ids');
        if($ids){
             // 正式对表数据还原
             $date = $this->get('date');
             $table_arr = explode(',', $ids);
             foreach ($table_arr as $k=>$table){
                 $data = file_get_contents($this->data_dir.$date.__DS__.$table);
                 $sql_arr = explode(";\r\n", $data);
                 foreach ($sql_arr as $sql){
                     $this->cur_model->execute($this->regainVal($sql));
                 }
             }
             $this->ajaxReturn(200, '已成功还原 '.($k+1).' 张表！');
        }
        
        if(!is_dir($this->data_dir)){
           mk_dir($this->data_dir);
        }
        
        // 读取目录
        $dir = opendir($this->data_dir);
        $dateArr = array();
        while($file = readdir($dir)){
            if($file == '.' || $file == '..'){
               continue;
            }elseif(is_dir($this->data_dir.$file)){
               $dateArr[$file] = $file;
               $date = $file;
            }
        }
        closedir($dir);
        
        // 读取备份文件
        //$date = ($_POST['date'] ? $_POST['date'] : ($date ? $_POST['date']=$date : ''));
        $postDate = $this->post('date');
        $date = $postDate ? $postDate : $date;
        $this->post('date', $date);
        
        $data_dir = $this->data_dir.$date.__DS__;
        $dir = opendir($data_dir);
        $list = array();
        while ($file = readdir($dir)){
           if($file == '.' || $file == '..'){
              continue;
           }elseif(is_file($data_dir.$file)){
              $list[] = array('id'=>$file, 'name'=>$file, 'size'=>getFileSize(filesize($data_dir.$file)));
           }
        }
        closedir($dir);
        
        $this->assign('dateArr', $dateArr);
        $this->assign('list', $list);
        $this->display();
    }
    
    
    
}