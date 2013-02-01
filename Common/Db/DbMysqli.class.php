<?php
/**
 * Mysqli 扩展驱动器
 * 
 * @author maojianlw@139.com
 * @since 2012-02-13
 */

class DbMysqli extends Db implements DbInterface
{
    
     
    /**
     * 初始化
     * 
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        if(!extension_loaded('mysqli'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('mysqli')));
        }
        $this->config = $config;
    }
    

    /**
     * 连接数据库
     * 
     * @param bool $pconnect
     * @return object
     */
    public function connect($pconnect=false)
    {
        extract($this->config);
        $this->linkID = mysqli_connect($dbhost.':'.$dbport, $dbuser, $dbpwd, $dbname);
        if(mysqli_connect_errno()) throw_exception(mysqli_connect_error());
        mysqli_query("SET NAMES $dbcharset");
        return $this->linkID;
    }
     
     
    /**
     * (non-PHPdoc)
     * @see Db::checkContent()
     */
    public function checkContent()
    {
        $this->checkTimeOut();
        $isResource = is_resource($this->linkID);
        if(!$isResource || ($isResource && !mysqli_ping($this->linkID)))
        {
            self::$connectNum++;
            $this->close();
            $this->linkID = $this->connect();
        }
        else
        {
            self::$connectNum = 0;
        }
    }
     
     
    /**
     * 选择数据库
     * 
     * @param string $dbName
     * @return mixed
     */
    public function selectDb($dbName)
    {
        $this->dbName = $dbName;
        return mysqli_select_db($this->linkID, $dbName);
    }
     

    /**
     * 执行查询
     * 
     * @param string $sql
     * @return array
     */
    public function query($sql)
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->queryID) $this->free();
        $this->queryStr = $sql;
        $this->queryID = $this->linkID->query($sql);
        
        // 支持存储过程
        if($this->linkID->more_results())
        {
            while(($res = $this->linkID->next_result()) != NULL)
            {
                $res->free_result();
            }
        }
        if($this->queryID === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = $this->queryID->num_rows;
            return $this->fetchAll();
        }
    }
     
     
    /**
     * 执行语句
     * 
     * @param string $sql
     * @return int
     */
    public function execute($sql)
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->queryID) $this->free();
        $this->queryStr = $sql;
        $this->queryID = $this->linkID->query($sql);
        if($this->queryID === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = $this->affectedRows();
            $this->insertID = $this->insertID();
            return $this->numRows;
        }
    }
     
     
     
    /**
     * 提取所有记录
     * 
     * @return array
     */
    public function fetchAll()
    {
        $data = array();
        if($this->numRows > 0)
        {
            while($row = $this->queryID->fetch_assoc())
            {
                $data[] = $row;
            }
            $this->queryID->data_seek(0);// 移动内部结果的指针从0行开始。
        }
        return $data;
    }
     
     
    /**
     * 获取最后插入记录的id（如果是数据为自动增长）
     * 
     * @return int
     */
    public function insertID()
    {
        return $this->linkID->insert_id;
    }
     
     
    /**
     * 返回上一个操作所影响的行数
     * 
     * @return int
     */
    public function affectedRows()
    {
        return $this->linkID->affected_rows;
    }
     
     
    /**
     * 返回上一个操作中的错误信息的数据编码
     * 
     * @return int
     */
    public function errno()
    {
        return $this->linkID->errno;
    }
     
     
    /**
     * 返回上一个操作中产生的文本错误信息
     * 
     * @return void
     */
    public function error()
    {
        $this->error = $this->linkID->error;
        if($this->queryStr != '') $this->error .= "[SQL]:{$this->queryStr}";
        Log::sql($this->error);
    }
     
     
    /**
     * 关闭数据库连接
     * 
     * @return void
     */
    public function close()
    {
        if($this->queryID) $this->free();
        if($this->linkID) $this->linkID->close();
        $this->linkID = 0;
    }
     
     
    /**
     * 获得表字段结构信息
     * 
     * @param string $tabeleName
     * @return array
     */
    public function fields($tabeleName)
    {
        $fileds = array();
        if($list = $this->query("SHOW COLUMNS FROM $tabeleName"))
        {
            foreach($list as $k=>$field)
            {
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
     * 
     * @param string $dbName
     * @return array
     */
    public function tables($dbName='')
    {
        $data = array();
        if($list = $this->query('SHOW TABLES '.($dbName ? "FROM $dbName" : '')))
        {
            foreach($list as $key=>$val)
            {
                $data[$key] = current($val);
            }
        }
        return $data;
    }
    
     
    /**
     * 开启事务
     * 
     * @return void
     */
    public function startTrans()
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->transTimes == 0)
        {
            $this->linkID->autocommit(false);
        }
        $this->transTimes++;
        return;
    }
     
    
    /**
     * 提交事务
     * 
     * @return void
     */
    public function commit()
    {
        if($this->transTimes > 0){
            $result = $this->linkID->commit();
            $this->linkID->autocommit(true);
            $this->transTimes = 0;
            if(!$result) throw_exception($this->error());
        }
        return true;
    }
     
    
    /**
     * 回滚事务
     * 
     * @return bool
     */
    public function rollback()
    {
        if($this->transTimes > 0)
        {
            $result = $this->linkID->rollback();
            $this->transTimes = 0;
            if(!$result) throw_exception($this->error());
        }
        return true;
    }
     
     
    /**
     * 释放查询结果
     * 
     * @return void
     */
    public function free()
    {
        if(is_resource($this->queryID)) mysqli_free_result($this->queryID);
        $this->queryID = 0;
   }
 
}