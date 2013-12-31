<?php
/**
 * EaglePHP框架系统工具类管理
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 * @since 2012-05-23
 * @version 1.8
 */

class ToolController extends CommonController{
 
     private $dbObj = null;
     
     /**
      * 端口扫描
      */
     public function portscanAction(){
           if($this->isPost()){
                $ip = $this->post('ip');
                $port = $this->post('port');
                if($port=='' || $ip==''){
                    $this->ajaxReturn(300, 'IP或端口请填写完整');
                }
                $port_arr = explode(',', $port);
                foreach ($port_arr as $port){
                    $fp = @fsockopen($ip, $port, $errno, $error, 1);
                    $result = ($fp) ? '<span style="font-weight:bold;color:#080;">Open</span>' : '<span style="font-weight:bold;color:#f00;">Close</span>';
                    $data[] = array('result'=>"{$ip}:{$port} ---------------------------- {$result}");
                    @fclose($fp);
                }
                $this->assign('data', $data);
           }else{
                $_POST['ip'] = '127.0.0.1';
                $_POST['port'] = '21,25,80,110,135,139,445,1433,3306,3389,5631,43958';
           }
           $this->display('Tool/portscan');
           abortConnect();
     }
      
     /**
      * PHP环境
      */
     public function phpenvAction(){
           $param = $this->request('param');
           if($param){
                $this->assign('value', $this->getCfg($param));
           }
           $this->assign('list', $this->getServerInfo());
           $this->display();
     }
     
     
     /**
      * 安全信息
      */
     public function secinfoAction(){
           $list[] = array('Server software', $_SERVER['SERVER_SOFTWARE']);
           $list[] = array('Disabled PHP Functions', $this->getCfg('disable_functions'));
           $list[] = array('Open base dir', $this->getCfg('open_basedir'));
           $list[] = array('Safe mode exec dir', $this->getCfg('safe_mode_exec_dir'));
           $list[] = array('Safe mode include dir', $this->getCfg('safe_mode_include_dir'));
           $list[] = array('CURL support', function_exists('curl_version') ? 'enabled' : 'no');
           
           $db_arr = array();
           if(function_exists('mysql_get_client_info')){
                $db_arr[] = 'Mysql ('.mysql_get_client_info().')';
           }
           if(function_exists('mssql_connect')){
                $db_arr[] = 'MSSQL';
           }
           if(function_exists('sqlsrv_connect')){
                $db_arr[] = 'sqlsrv';
           }
           if(function_exists('oci_connect')){
                $db_arr[] = 'Oracle';
           }
           if(function_exists('pg_connect')){
                $db_arr[] = 'PostgreSQL';
           }
           
           $list[] = array('Supported databases', implode(', ', $db_arr));
           
           if(__DS__ != '\\'){
                $userful = array('gcc','lcc','cc','ld','make','php','perl','python','ruby','tar','gzip','bzip','bzip2','nc','locate','suidperl');
        		$danger = array('kav','nod32','bdcored','uvscan','sav','drwebd','clamd','rkhunter','chkrootkit','iptables','ipfw','tripwire','shieldcc','portsentry','snort','ossec','lidsadm','tcplodg','sxid','logcheck','logwatch','sysmask','zmbscap','sawmill','wormscan','ninja');
        		$downloaders = array('wget','fetch','lynx','links','curl','get','lwp-mirror');
        		
        		$list[] = array('Readable /etc/passwd', @is_readable('/etc/passwd') ? 'yes' : 'no');
        		$list[] = array('Readable /etc/shadow', @is_readable('/etc/shadow') ? 'yes' : 'no');
        		$list[] = array('OS version', @file_get_contents('/proc/version'));
        		$list[] = array('Distr name', @file_get_contents('/etc/issue.net'));
        		
        		function which($pr){
        		     $path = execute("which $pr");
        		     return ($path ? $path : $pr);
        		}
        		
        		
        		if($this->getCfg('safe_mode') != 'No'){
        		      $return_arr = array();
        		      foreach ($userful as $v){
        		          if(which($v)){
        		              $return_arr[] = $v;
        		          }
        		      }
        		      $list[] = array('Userful', implode(', ', $return_arr));
        		      
        		      $return_arr = array();
        		      foreach ($danger as $v){
        		          if(which($v)){
        		              $return_arr[] = $v;
        		          }
        		      }
        		      $list[] = array('Danger', implode(', ', $return_arr));
        		      
        		      $return_arr = array();
        		      foreach ($downloaders as $v){
        		          if(which($v)){
        		              $return_arr[] = $v;
        		          }
        		      }
        		      $list[] = array('Downloaders', implode(', ', $return_arr));
        		      
        		      $list[] = array('Hosts', @file_get_contents('/etc/hosts'));
        		      $list[] = array('HDD space', execute('df -h'));
        		      $list[] = array('Mount options', @file_get_contents('/etc/fstab'));
        		      
        		}
        		
           }else{
                $list[] = array('OS Version', execute('ver'));
                $list[] = array('Account Settings', nl2br(execute('net accounts')));
                $list[] = array('User Accounts', nl2br(execute('net user')));
                $list[] = array('IP Configurate', nl2br(execute('ipconfig -all')));
           }
           
           $list = auto_charset($list);
           $this->assign('list', $list);
           $this->display();
           abortConnect();
     }
     
     
	/**
     * 获得php配置选项 
     */
    protected function getCfg($option_name){
    	$result = get_cfg_var($option_name);
    	if($result === 0){
    		return 'No';
    	}elseif($result === 1){
    		return 'Yes';
    	}else{
    		return $result ? $result : 'No';	
    	}
    }
     
     
	/**
     * 获得服务器信息
     */
    protected function getServerInfo(){
    	$dis_func = get_cfg_var('disable_functions');
    	$upsize = $this->getCfg('file_uploads') ? $this->getCfg('upload_max_filesize') : 'Not allowed';
		$adminmail = isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : $this->getCfg('sendmail_from');
		!$dis_func && $dis_func = 'No';	
		$info = array(
			array('Server Time',date('Y-m-d H:i:s')),
			array('Server Domain',$_SERVER['SERVER_NAME']),
			array('Server IP',gethostbyname($_SERVER['SERVER_NAME'])),
			array('Server OS',PHP_OS),
			array('Server OS Charset',$_SERVER['HTTP_ACCEPT_LANGUAGE']),
			array('Server Software',$_SERVER['SERVER_SOFTWARE']),
			array('Server Web Port',$_SERVER['SERVER_PORT']),
			array('PHP run mode',strtoupper(php_sapi_name())),
			array('The file path',__FILE__),
	
			array('PHP Version',PHP_VERSION),
			array('PHPINFO','<a href="'.__ROOT__.'index/phpInfo" target="_blank" style="text-decoration:underline;color:blue" >Yes</a>'),
			array('Safe Mode',$this->getCfg('safe_mode')),
			array('Administrator',$adminmail),
			array('allow_url_fopen',$this->getCfg('allow_url_fopen')),
			array('enable_dl',$this->getCfg('enable_dl')),
			array('display_errors',$this->getCfg('display_errors')),
			array('register_globals',$this->getCfg('register_globals')),
			array('magic_quotes_gpc',$this->getCfg('magic_quotes_gpc')),
			array('memory_limit',$this->getCfg('memory_limit')),
			array('post_max_size',$this->getCfg('post_max_size')),
			array('upload_max_filesize',$upsize),
			array('max_execution_time',$this->getCfg('max_execution_time').' second(s)'),
			array('disable_functions', $dis_func)
    	);
    	return $info;
    }
     
    
    
    /**
     * mysql管理
     */
    public function mysqlAction(){
         if($url_param = $this->request('url_param')){
              parse_str(base64_decode(urldecode($url_param)), $param_arr);
              $_REQUEST = $_GET = array_merge($_REQUEST, $param_arr);
         }
         if($this->request('dbhost')){    
              $dbhost = $this->request('dbhost');
              $dbuser = $this->request('dbuser');
              $dbname = $this->request('dbname');
              $go = $this->request('go');
              
              $config = array(
                     'dbdriver' => 'mysql',
                     'dbtype' => 'mysql',
                     'dbhost' => $dbhost,
                     'dbport' => $this->request('dbport'),
                     'dbuser' => $dbuser,
                     'dbpwd' => $this->request('dbpwd'),
                     'dbname' => $dbname,
                     'dbcharset' => $this->request('dbcharset')
              );
              
              $this->assign('url_param', urlencode(base64_encode(http_build_query($config))));
              
              
              $this->dbObj = new DbMysql($config);
              if(!$this->dbObj){return false;}
              
              $this->dbObj->connect(true);
              $dbs = $this->dbObj->query('SHOW DATABASES'); // 显示所有数据库
              $db_arr = array();
              if(is_array($dbs)){
                   foreach ($dbs as $db_name){
                        $db_name = current($db_name);
                        $db_arr[$db_name] = $db_name;
                   }
              }
              $this->assign('db_arr', $db_arr);
              
              // 如果选择了数据库就显示mysql服务端信息
              if($dbname){
                  $this->assign('mysql_info', 'MySQL '.mysql_get_server_info()." runing in {$dbhost} as {$dbuser}@{$dbhost}");
              }
              
              // 当前表
              $table = $this->request('table');
              $this->assign('table', $table);
              
              switch ($go){
                  case 'record': // 查看表记录
                       $this->assign('field_arr', $this->getFieldInfo($table));
                       $data = $this->getTableData($table);
                       $this->assign('list', $data['list']);
                       $this->assign('page', $data['page']);
                       $_REQUEST['query'] = $this->dbObj->getLastSql();
                       break;
                  case 'struct': // 查看表结构信息
                       $data = $this->getTableStruct($table);
                       $this->assign('list1', $data['list1']);   
                       $this->assign('list2', $data['list2']);
                       $this->display('Tool/mysql_struct');
                       return;
                       break;
                  case 'add':  // 添加表数据
                       $this->addData($table);
                       break;
                  case 'update': // 修改表数据
                       $this->updateData($table);
                       break;
                  case 'delete': // 删除表数据
                       $this->deleteData($table);
                       break;
                  case 'drop':  // 删除表
                       $this->dropTable($table);
                       break;
                  case 'truncate':  // 清空表
                       $this->truncateTable($table);
                       break;
                  case 'export': // 导出表
                       $this->exportTable();
                       break;
                  default:
                       // 文本框SQL命令执行
                       if($this->request('query')){
                           $this->assign('list', $this->querySql($this->request('query', self::_NO_CHANGE_VAL_, false)));
                           $_REQUEST['go'] = 'query';
                       }elseif($dbname){
                           $data = $this->getTableState();
                           $this->assign('table_status', $data['list']);
                           $this->assign('attach_arr', $data['attach_arr']);
                           $this->assign('save_path', realpath(DATA_DIR.getCfgVar('cfg_backup_dir')).__DS__.$dbname.'_'.date('Ymd').'.sql');
                       }
                       break;
              }
         }else{
              $_REQUEST['dbhost'] = 'localhost';
              $_REQUEST['dbport'] = '3306';
              $_REQUEST['dbuser'] = 'root';
              $_REQUEST['dbcharset'] = 'utf8'; 
         }    
         
         $charset_arr = array('armscii8','ascii','big5','binary','cp1250','cp1251','cp1256','cp1257','cp850','cp852','cp866','cp932','dec8','euc-jp','euc-kr','gb2312','gbk','geostd8','greek','hebrew','hp8','keybcs2','koi8r','koi8u','latin1','latin2','latin5','latin7','macce','macroman','sjis','swe7','tis620','ucs2','ujis','utf8');
         $this->assign('charset_arr', $charset_arr);
         $this->display();
    }
    
    
    /**
     * 获取表的状态信息
     * @return array
     */
    private function getTableState(){
         $status_arr = $this->dbObj->query('SHOW TABLE STATUS');
         if(is_array($status_arr)){
              $rows = $length = 0;
              foreach ($status_arr as $table){
                  $row = $table['Rows'];
                  $size = (int)$table['Data_length'];
                  $rows += $row;
                  $length += $size;
                  $list[] = array(
                           'name' => $table['Name'],
                           'rows' => $row,
                  		   'data_length' => getFileSize($size),
                           'create_time' => $table['Create_time'],
                           'update_time' => $table['Update_time'],
                           'engine' => $table['Engine'],
                           'collation' => $table['Collation'],
                           'comment' => $table['Comment'],
                  );
              }
              $attach_arr['count'] = count($status_arr);
              $attach_arr['rows'] = $rows;
              $attach_arr['length'] = getFileSize($length);
         }
         return array('list'=>$list, 'attach_arr'=>$attach_arr);
    }
    
    
    /**
     * 获得字段信息
     * @param string $table
     */
    private function getFieldInfo($table){
         $columns = $this->dbObj->query("SHOW FULL COLUMNS FROM `$table`");
         $fields = array();
         foreach ($columns as $column){
              $type_str =  $column['Type'];
              $matchs = array();
              preg_match("/\d+/", $type_str, $matchs);
              $length = isset($matchs[0]) ? $matchs[0] : '';
              $type_arr = explode('(', $type_str);
              $fields[] = array('name'=>$column['Field'], 'type'=>$type_arr[0], 'length'=>$length, 'key'=>strtolower($column['Key']), 'auto'=>$column['Extra'], 'default'=>$column['Default'], 'comment'=>$column['Comment'], 'null'=>strtolower($column['Null']));
         }
         return $fields;
    }
    
    /**
     * 获取表数据
     * @param string $table
     */
    private function getTableData($table, $list=null){
         $columns = $this->getFieldInfo($table);
         $page = null;
         if($list == null){
             $c_arr = $this->dbObj->query("SELECT COUNT(*) AS count FROM `$table`");
             $count = $c_arr[0]['count'];
             $page = $this->page($count);
    		 $list = $this->dbObj->query("SELECT * FROM `$table` LIMIT {$page['limit']},{$page['numPerPage']}");
         }
		 foreach ($list as &$val){
		     $where = null;
		     foreach ($val as $k=>&$v){
		         $v = htmlspecialchars($v);
		         if(strlen($v) > 32) $v = utf8Substr($v, 0, 32).'...';
		     }
		     
		     // 条件语句封装
             foreach ($columns as $column){
                 $field_name = $column['name'];
                 $filed_val = addslashes($val[$field_name]);
	             if($column['key'] == 'uni' || $column['key'] == 'pri' || $column['auto'] == 'auto_increment'){
	                  $where = " AND {$field_name}='{$filed_val}'";
	                  break;
	             }else{
	                  $where .= " AND {$field_name}='{$filed_val}'";
	             }
             }
		     $val['eg_base64_where'] = base64_encode($where);
		 }
         return array('list'=>$list, 'page'=>$page);
    }
    
    
    /**
     * 获取表结构信息
     * @param string $table
     */
    private function getTableStruct($table){
         $list1 = $this->dbObj->query("SHOW FULL COLUMNS FROM `{$table}`");
         $list2 = $this->dbObj->query("SHOW INDEX FROM `{$table}`");
         return array('list1'=>$list1, 'list2'=>$list2);
    }
    
    
    /**
     * 添加数据
     * @param string $table
     */
    private function addData($table){
         if($this->isPost()){
              $key_str = null;
              $val_str = null;
              foreach($_POST as $k=>$v){
                  $key_str .= "$k,";
                  $val_str .= "'{$v}',";
              }
              $sql = "INSERT INTO `$table`(".trim($key_str, ',').") VALUES(".trim($val_str, ',').")";
              if($this->dbObj->execute($sql)){
                  $this->ajaxReturn(200, '添加成功', '', 'closeCurrent');  
              }else{
                  $this->ajaxReturn(300, '添加失败');
              }
         }else{
              $this->assign('list', $this->getFieldInfo($table));
              $this->display('Tool/mysql_action');
              exit;
         }
    }
    
    /**
     * 修改数据
     * @param string $table
     */
    private function updateData($table){
         $where = base64_decode($this->request('eg_base64_where'));
         if($this->isPost()){
              unset($_POST['eg_base64_where']);
              foreach($_POST as $k=>$v){
                  $sql .= "$k='{$v}',";
              }
              $sql = trim($sql, ',');
              $sql = "UPDATE `$table` SET {$sql} WHERE 1=1 $where LIMIT 1";
              if($where && $this->dbObj->execute($sql)){
                  $this->ajaxReturn(200, '修改成功', '', 'closeCurrent');  
              }else{
                  $this->ajaxReturn(300, '修改失败');
              }
         }else{
              $fileds = $this->getFieldInfo($table);
              $info = $this->dbObj->query("SELECT * FROM `{$table}` WHERE 1=1 {$where}");
              foreach ($fileds as &$f){
                  $f['value'] = $info[0][$f['name']];
              }
              $this->assign('list', $fileds);
              $this->display('Tool/mysql_action');
              exit;
         }
    }
    
    
    /**
     * 删除数据
     * @param string $table
     */
    private function deleteData($table){
         $where = base64_decode($this->request('eg_base64_where'));
         if($where){
              if($this->dbObj->execute("DELETE FROM `$table` WHERE 1=1 $where LIMIT 1")){
                  $this->ajaxReturn(200, '删除成功');
              }
         }
         $this->ajaxReturn(300, '删除失败');
    }
    
    
    /**
     * 删除表
     * @param string $table
     */
    private function dropTable($table){
         $this->dbObj->execute("DROP TABLE `$table`");
         $this->ajaxReturn(200, '删除成功');
    }
    
    /**
     * 清空表
     * @param string $table
     */
    private function truncateTable($table){
         $this->dbObj->execute("TRUNCATE TABLE `$table`");
         $this->ajaxReturn(200, '清空成功');
    }
    
    
    /**
     * 执行SQL语句，支持多行
     * @param string $query
     */
    private function querySql($query){
         $query_arr = array_filter(explode(";", $query));
         foreach ($query_arr as $sql){
             $sql = trim($sql);
             $table = null; $fields = null; $result = null;
             if(empty($sql)) continue;
             if(stripos($sql, 'select') === 0){
                 $result = $this->dbObj->query($sql);
                 $affected_rows = count($result);
                 $type = 'query';
                 if($result){
                     $fields = array_keys($result[0]);
                     if(preg_match('/^select\s+\*\s+from\s+`{0,1}([\w]+)`{0,1}\s{0,1}/i', $sql, $matchs)){
                         $table = $matchs[1];
                         $data = $this->getTableData($table, $result);
                         $result = $data['list']; 
                     }
                 }
             }else{
                 $affected_rows = $this->dbObj->execute($sql);
                 $type = 'execute';
             }
             
             $list[] = array(
             			'sql' => $sql,
                        'type' => $type,
                        'affected_rows' => $affected_rows,
                        'fields' => $fields,
                        'result' => $result,
                        'table' => $table
             );
         }
         return $list;         
    }
    
    
    
    private function changeVal($val){
         $val = str_replace("\r", "\\r", $val);
         $val = str_replace("\n", "\\n", $val);
         return addslashes($val);
    }
    
    
    
    /**
     * 导出已选表
     */
    private function exportTable(){
         $file_save = $this->request('file_save');
         $file_path = $this->request('file_path');
         $ids = $this->request('ids');
         
         if($file_save == 'true'){
             $dir = dirname($file_path);
             if(!is_dir($dir)){
                 $this->ajaxReturn(300, $dir.' 目录不存在!');
             }
         }

         if($ids){
             $tables = explode(',', $ids);
             $i = 0;
             $line = ";\r\n\r\n";
             foreach ($tables as $t){
                 $bak_str .= "DROP TABLE IF EXISTS `{$t}`{$line}";
                 $info = $this->dbObj->query("SHOW CREATE TABLE `{$t}` ");
                 $bak_str .= $info[0]['Create Table'].$line;
                 
                 $fields = $this->dbObj->fields($t);
                 $field_arr = array_keys($fields);
                 $field_str = '`'.implode('`, `', $field_arr).'`';
                 
                 $insert = "INSERT INTO `$t`({$field_str}) VALUES \r\n";
                 $list = $this->dbObj->query("SELECT * FROM `$t`");
 
                 if(is_array($list)){
                     $z = 0;
                     $max = 100;
                     $count = count($list)-1;
                     foreach($list as $k=>$row){
                      
                         $insert_val = null;
                         foreach($fields as $fieldName=>$val){
                            $type = $val['type'];
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
                 $i++;
             }
             
             if($file_save == 'checked'){
                 if(file_put_contents($file_path, $bak_str)){
                     $this->ajaxReturn(200, "已成功备份 {$i} 张表！");
                 }else{
                     $this->ajaxReturn(300, '备份失败');
                 }
             }else{
                 header("Content-Type: application/text; charset=utf-8");
                 header("Content-Disposition: inline; filename=\"" . ($file_name ? $file_name : $this->request('dbname').'_'.Date::format('Ymd')) . ".sql\"");
                 exit($bak_str);
             }
         }
    }
    
    
    
    
}