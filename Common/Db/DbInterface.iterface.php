<?php
/**
 * 数据库接口，后续扩展其他数据库以此接口规范为准。
 * 
 * @author maojianlw@139.com
 * @since 2012-2-1
 */

interface DbInterface
{
   
   /**
    * 连接数据库
    */
   public function connect($pconnect=false);
   
   /**
    * 选择数据库
    */
   public function selectDb($dbName);
   
	
   /**
    * 执行查询
    */
   public function query($sql);
   
   
   /**
    * 执行语句
    */
   public function execute($sql);
   
   
   
   /**
    * 提取所有记录
    */
   public function fetchAll();
   
   
   /**
    * 获取最后插入记录的id（如果是数据为自动增长）
    */
   public function insertID();
   
   
   /**
    * 返回上一个操作所影响的行数
    */
   public function affectedRows();
   
   
   /**
    * 返回上一个操作中的错误信息的数据编码
    */
   public function errno();
   
   
   /**
    * 返回上一个操作中产生的文本错误信息
    */
   public function error();
   
   
   /**
    * 关闭数据库连接
    */
   public function close();
   
   
   /**
    * 获得表字段结构信息
    */
   public function fields($tabeleName);
   
   
   /**
    * 返回数据库中所有表
    */
   public function tables($dbName='');
   
   /**
    * 开启事务
    */
   public function startTrans();
   
   /**
    * 提交事务
    */
   public function commit();
   
   /**
    * 回滚事务
    */
   public function rollback();
   
   
   /**
    * 释放查询结果
    */
   public function free();
   
   
}


