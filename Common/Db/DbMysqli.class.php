<?php
/**
 * Mysqli 扩展驱动器
 * @author maojianlw@139.com
 * @since 2012-02-13
 */
class DbMysqli extends Db implements DbInterface{
   
   /**
    * 初始化
    * @param array $config
    */
   public function __construct($config){
        if(!extension_loaded('mysqli')){
         throw_exception(L('SYSTEM:module.not.loaded', array('mysqli')));
        }
        $this->config = $config;
   }
 
 	/**
   	* 连接数据库
   	*/
   public function connect($pconnect=false){
    	extract($this->config);
    	$this->linkID = mysqli_connect($dbhost.':'.$dbport, $dbuser, $dbpwd, $dbname);
    	if(mysqli_connect_errno()){
    	  throw_exception(mysqli_connect_error());
    	}
    	$this->linkID->query("SET NAMES $dbcharset");
    	unset($this->config);
    	return $this->linkID;
   }
   
   /**
    * 选择数据库
    */
   public function selectDb($dbName){
        $this->dbName = $dbName;
        return mysqli_select_db($this->linkID, $dbName);
   }
   
	
   /**
    * 执行查询
    */
   public function query($sql){
        if(!$this->linkID) return false;
        if($this->queryID) $this->free();
        $this->queryStr = $sql;
        $this->queryID = $this->linkID->query($sql);
        // 支持存储过程
        if($this->linkID->more_results()){
         while(($res = $this->linkID->next_result()) != NULL){
          $res->free_result();
         }
        }
        if($this->queryID === false){
         $this->error();
         return false;
        }else{
         $this->numRows = $this->queryID->num_rows;
         return $this->fetchAll();
        }
   }
   
   
   /**
    * 执行语句
    */
   public function execute($sql){
        if(!$this->linkID) return false;
        if($this->queryID) $this->free();
        $this->queryStr = $sql;
        $this->queryID = $this->linkID->query($sql);
        if($this->queryID === false){
         $this->error();
         return false;
        }else{
         $this->numRows = $this->affectedRows();
         $this->insertID = $this->insertID();
         return $this->numRows;
        }
   }
   
   
   
   /**
    * 提取所有记录
    */
   public function fetchAll(){
        $data = array();
		if($this->numRows > 0){
			while($row = $this->queryID->fetch_assoc()){
				$data[] = $row;	
			}
			$this->queryID->data_seek(0);// 移动内部结果的指针从0行开始。
		}
		return $data;	
   }
   
   
   /**
    * 获取最后插入记录的id（如果是数据为自动增长）
    */
   public function insertID(){
        return $this->linkID->insert_id;
   }
   
   
   /**
    * 返回上一个操作所影响的行数
    */
   public function affectedRows(){
    return $this->linkID->affected_rows;
   }
   
   
   /**
    * 返回上一个操作中的错误信息的数据编码
    */
   public function errno(){
       return $this->linkID->errno;
   }
   
   
   /**
    * 返回上一个操作中产生的文本错误信息
    */
   public function error(){
        $this->error = $this->linkID->error;
        if($this->queryStr != ''){
    		$this->error .= "[SQL]:{$this->queryStr}";
    	}
    	Log::sql($this->error);
   }
   
   
   /**
    * 关闭数据库连接
    */
   public function close(){
        if($this->queryID){
           $this->free();
        }
        if($this->linkID){
           $this->linkID->close();
        }
        $this->linkID = 0;
   }
   
   
   /**
    * 获得表字段结构信息
    */
   public function fields($tabeleName){
        $fileds = array();
		if($list = $this->query("SHOW COLUMNS FROM $tabeleName")){
			foreach($list as $k=>$field){
				$fileds[$field['Field']] = array(
					'name' => $field['Field'],
					'type' => preg_replace('/\(\d+\)/', '', $field['Type']),
					'notnull' => (strtolower($field['Null']) == 'yes'),
					'default' => $field['Default'],
					'primary' => (strtolower($field['Key']) == 'pri'),
					'autoinc' => (strtolower($field['Extra']) == 'auto_increment')
				);
			}
		}
		return $fileds;
   }
   
   
   /**
    * 返回数据库中所有表
    */
   public function tables($dbName=''){
        $data = array();
   		if($list = $this->query('SHOW TABLES '.($dbName ? "FROM $dbName" : ''))){
   			foreach($list as $key=>$val){
   				$data[$key] = current($val); 
   			}
   		}
   		return $data;
   }
   
   /**
    * 开启事务
    */
   public function startTrans(){
       if(!$this->linkID) return false;
   		if($this->transTimes == 0){
   			$this->linkID->autocommit(false);
   		}
   		$this->transTimes++;
   		return;
   }
   
   /**
    * 提交事务
    */
   public function commit(){
       if($this->transTimes > 0){
   			$result = $this->linkID->commit();
   			$this->linkID->autocommit(true);
   			$this->transTimes = 0;
   			if(!$result){
   				throw_exception($this->error());
   			}
   		}
   		return true;
   }
   
   /**
    * 回滚事务
    */
   public function rollback(){
        if($this->transTimes > 0){
   			$result = $this->linkID->rollback();
   			$this->transTimes = 0;
   			if(!$result){
   				throw_exception($this->error());
   			}
   		}
   		return true;
   }
   
   
   /**
    * 释放查询结果
    */
   public function free(){
       mysqli_free_result($this->queryID);
       $this->queryID = 0;
   }
 
}
