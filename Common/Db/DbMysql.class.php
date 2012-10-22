<?php

/**
 * mysql驱动器
 * @author maojianlw@139.com
 * @since 2012-2-1
 */

define('CLIENT_MULTI_RESULTS', 131072);

class DbMysql extends Db implements DbInterface{
 
   /**
    * 初始化构造函数
    */
   public function __construct($config){
        if(!extension_loaded('mysql')){
           throw_exception(L('SYSTEM:module.not.loaded', array('mysql')));
        }
   		$this->config = $config;
   }
   
   /**
    * 连接数据库
    */
   public function connect($pconnect=false){
   		extract($this->config);
   		if($pconnect){
   			$this->linkID = mysql_pconnect($dbhost.':'.$dbport, $dbuser, $dbpwd, CLIENT_MULTI_RESULTS);
   		}else{
   			$this->linkID = mysql_connect($dbhost.':'.$dbport, $dbuser, $dbpwd, true, CLIENT_MULTI_RESULTS);
   		}
   		$this->query("SET NAMES $dbcharset");
   		$this->selectDb($dbname);
   		unset($this->config);
   		return $this->linkID;
   }
  
   /**
    * 选择数据库
    */
   public function selectDb($dbName){
   		$this->dbName = $dbName;
   		return (is_resource($this->linkID) && $this->dbName) ? mysql_select_db($dbName, $this->linkID) : 0;
   }
   
   /**
    * 执行查询
    */
   public function query($sql){
   		if(!$this->linkID) return false;
   		if($this->queryID) $this->free();
   		$this->queryStr = $sql;
   		$this->queryID = mysql_query($sql, $this->linkID);
   		if($this->queryID === false){
   			$this->error();
   			return false;
   		}else{
   			$this->numRows = $this->affectedRows();
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
   		$this->queryID = mysql_query($sql, $this->linkID);
   		if($this->queryID === false){
   			$this->error();
   			return false;
   		}else{
   			$this->numRows = $this->affectedRows();
   			if(preg_match('/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i',$sql)){
				$this->insertID = $this->insertID();
			}
   			return $this->numRows;
   		}
   }
      
   
   /**
    * 提取所有记录
    */
   public function fetchAll(){
   		$data = array();
		if($this->numRows > 0){
			while($row = mysql_fetch_assoc($this->queryID)){
				$data[] = $row;	
			}
			mysql_data_seek($this->queryID, 0);// 移动内部结果的指针从0行开始。
		}
		return $data;	
   }
   
   /**
    * 获取最后插入记录的id（如果是数据为自动增长）
    */
   public function insertID(){
		return ($this->linkID ? mysql_insert_id($this->linkID) : false);	   	
   }
   
   /**
    * 返回上一个操作所影响的行数
    */
   public function affectedRows(){
		return ($this->linkID) ? mysql_affected_rows($this->linkID) : 0;
   }
   
   /**
    * 返回上一个操作中的错误信息的数据编码
    */
   public function errno(){
		return $this->errno = mysql_errno($this->linkID);   	
   }
   
   /**
    * 返回上一个操作中产生的文本错误信息
    */
   public function error(){
   		$this->error = mysql_error($this->linkID);
   		if($this->queryStr != ''){
   			$this->error .= "[SQL]:{$this->queryStr}";
   		}
   		Log::sql($this->error);
   }
      
   /**
    * 返回表中字段信息
    */
   public function fields($tabeleName){
		$fileds = array();
		if($list = $this->query("SHOW COLUMNS FROM `$tabeleName`")){
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
    * 开始事务
    */
   public function startTrans(){
   		if(!$this->linkID) return false;
   		if($this->transTimes == 0){
   			mysql_query('START TRANSACTION', $this->linkID);
   		}
   		$this->transTimes++;
   		return;
   }
   
   
   /**
    * 提交事务
    */
   public function commit(){
   		if($this->transTimes > 0){
   			$result = mysql_query('COMMIT', $this->linkID);
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
   			$result = mysql_query('ROLLBACK', $this->linkID);
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
		if(is_resource($this->queryID)){
			mysql_free_result($this->queryID);
		}
		$this->queryID = 0;
   }
   
   /**
    * 关闭数据库连接
    */
   public function close(){
   		if($this->queryID) $this->free();
   		return ($this->linkID ? mysql_close($this->linkID) : $this->linkID=0);
   }
    
   /**
    * 析构函数
    */ 
   public function __destruct(){
   		$this->close();
   }

}
