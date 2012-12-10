<?php
/**
 * PDO数据库抽象类
 * 
 * @since 2012-2-7
 * @author maojianlw@139.com
 */

class DbPdo extends Db implements DbInterface
{

    /**
     * 初始化
     * 
     * @param array $config
     */
    public function __construct($config) 
    {
        if(!class_exists('PDO'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('PDO')));
        }
        $this->config = $config;
    }
    

    /**
     * 连接数据库
     * @see DbInterface::connect()
     * @return object
     */
    public function connect($pconnect = false)
    {
        try
        {
            extract($this->config);
            if(!isset($dbparams) || !is_array($dbparams)) $dbparams = array();
            
            // 注：此处如果sqlsrv驱动器采用长连接模式会导致内存泄露，出现浏览器崩溃现象，请慎用。
            if($pconnect) $dbparams[PDO::ATTR_PERSISTENT] = true;
            
            // PDO::ERRMODE_EXCEPTION
            $dbparams[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT; 

            // 根据不同的驱动器组装成DSN
            switch ($this->dbDriver)
            {
                case 'sqlsrv':
                    $dbparams[PDO::SQLSRV_ATTR_DIRECT_QUERY] = true;
                    $dsn = "sqlsrv:Server={$dbhost}; Database={$dbname}";
                    break;
                default:
                    $dsn = "{$dbdriver}:host={$dbhost};port={$dbport};dbname={$dbname}";
                    break;
            }

            $this->linkID = new PDO($dsn, $dbuser, $dbpwd, $dbparams);
            $this->linkID->exec("SET NAMES {$dbcharset}");
        }
        catch(PDOException $e)
        {
            throw_exception("PDO {$dbdriver} => ".$e->getMessage());
        }
        return $this->linkID;
    }

    
    /**
     * 选择数据库
     * 
     * @param string $dbName
     * @return mixed
     */
    public function selectDb($dbName)
    {
        return $this->linkID->exec("use $dbName");
    }
     

    /**
     * 执行查询
     * 
     * @param sring $sql
     * @return array
     */
    public function query($sql)
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        $this->queryStr = $sql;
        if(!empty($this->queryID)) $this->free();
        $this->queryID = $this->linkID->prepare($sql);
        if($this->queryID === false)
        {
            $this->error();
            return false;
        }
        $result = $this->queryID->execute();
        if($result === false)
        {
            $this->error();
            return false;
        }
        else
        {
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
        $this->queryStr = $sql;
        if(!empty($this->queryID)) $this->free();
        $this->queryID = $this->linkID->prepare($sql);
        if($this->queryID === false)
        {
            $this->error();
            return false;
        }
        $result = $this->queryID->execute();
        if($result === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = $result;
            if(preg_match('/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i',$sql))
            {
                $this->insertID = $this->insertID();
            }
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
        $result = $this->queryID->fetchAll(constant('PDO::FETCH_ASSOC'));
        $this->numRows = count($result);
        return $result;
    }
     
     
    /**
     * 获取最后插入记录的id（如果是数据为自动增长）
     * 
     * @return int
     */
    public function insertID()
    {
        switch($this->dbDriver)
        {
            case 'ibase':
            case 'sqlsrv':
            case 'mssql':
            case 'mysql':
                return $this->linkID->lastInsertId();
                break;
        }
    }
     
     
    /**
     * 返回上一个操作所影响的行数
     * 
     * @return int
     */
    public function affectedRows()
    {
         
    }
     
     
    /**
     * 返回上一个操作中的错误信息的数据编码
     * 
     * @return int
     */
    public function errno()
    {
        return $this->queryID->errorCode();
    }
     
     
    /**
     * 返回上一个操作中产生的文本错误信息
     * 
     * @return void
     */
    public function error()
    {
        if($this->queryID)
        {
            $errInfo = $this->queryID->errorInfo();
            $this->error = $errInfo[2];
        }
        else
        {
            $this->error = '';
        }
        
        if($this->queryStr != '')
        {
            $this->error .= "[SQL]:{$this->queryStr}";
        }
        Log::sql($this->error);
    }
     
     
    /**
     * 关闭数据库连接
     * 
     * @return void
     */
    public function close()
    {
        $this->free();
        $this->linkID = null;
    }
     
     
    /**
     * 获得表字段结构信息
     * 
     * @param string $tabeleName
     * @return array
     */
    public function fields($tabeleName)
    {
        $fields = array();
        switch($this->dbDriver)
        {
            case 'mssql':
            case 'sqlsrv':
                $sql = "SELECT
             			a.name,
                      is_autoinc=case when COLUMNPROPERTY(a.id,a.name,'IsIdentity')=1 then 'yes'else 'no' end,
                      is_primary=case when exists(SELECT 1 FROM sysobjects where xtype='PK' and name in (
                         SELECT name FROM sysindexes WHERE indid in(
                         SELECT indid FROM sysindexkeys WHERE id = a.id AND colid=a.colid
                      ))) then 'yes' else 'no' end,
                      type=b.name,
                      allow_null=case when a.isnullable=1 then 'yes' else 'no' end,
                      default_value=isnull(e.text,'')
                      FROM syscolumns a
                      left join systypes b on a.xtype=b.xusertype
                      inner join sysobjects d on a.id=d.id and (d.xtype='U' or d.xtype='V')
                      left join syscomments e on a.cdefault=e.id
                      where d.name='{$tabeleName}'
                      order by a.colorder";
                 
                $list = $this->query($sql);
                if(is_array($list))
                {
                    foreach($list as $k=>$field)
                    {
                        $fields[$field['name']] = array(
              					'name' => $field['name'],
              					'type' => $field['type'],
              					'notnull' => (bool)($field['allow_null'] == 'yes'),
              					'default' => $field['default_value'],
              					'primary' => (bool)($field['is_primary'] == 'yes'),
              					'autoinc' => (bool)($field['is_autoinc'] == 'yes')
                        );
                    }
                }
                break;
                
            case 'mysql':
                $sql = "DESCRIBE {$tabeleName}";
                if($list = $this->query($sql))
                {
                    foreach($list as $k=>$field)
                    {
                        $fields[$field['Field']] = array(
            					'name' => $field['Field'],
            					'type' => preg_replace('/\(\d+\)/', '', $field['Type']),
            					'notnull' => (strtolower($field['Null']) == 'yes'),
            					'default' => $field['Default'],
            					'primary' => (strtolower($field['Key']) == 'pri'),
            					'autoinc' => (strtolower($field['Extra']) == 'auto_increment')
                        );
                    }
                }
                break;
                
            case 'sqlite':
                if($list = $this->query("PRAGMA TABLE_INFO({$tabeleName})"))
                {
                    foreach($list as $k=>$field)
                    {
                        $fileds[$field['name']] = array(
            					'name' => $field['name'],
            					'type' => preg_replace('/\(\d+\)/', '', $field['type']),
            					'notnull' => (bool)$field['notnull'],
            					'default' => $field['dflt_value'],
            					'primary' => $field['pk'],
            					'autoinc' => $field['pk']
                        );
                    }
                }
                break;
                
        }
        return $fields;
    }
     
     
    /**
     * 返回数据库中所有表
     * 
     * @param string $dbName
     * @return array
     */
    public function tables($dbName='')
    {
        switch($this->dbDriver)
        {
            case 'mssql':
            case 'sqlsrv':
                $sql = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE=\'BASE TABLE\'';
                break;
                
            case 'mysql':
                $sql = 'SHOW TABLES '.($dbName ? " FROM $dbName" : '');
                break;
                
            case 'sqlite':
                $sql = 'SELECT name FROM sqlite_master WHERE type="table" UNION ALL SELECT name FROM sqlite_temp_master WHERE type="table" ORDER BY name';
                break;
                
        }
        
        $data = array();
        if($list = $this->query($sql))
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
    public function startTrans(){
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->transTimes == 0){
            $this->linkID->beginTransaction();
        }
        $this->transTimes++;
        return;
    }
    
     
    /**
     * 提交事务
     * 
     * @return bool
     */
    public function commit()
    {
        if($this->transTimes > 0)
        {
            $result = $this->linkID->commit();
            $this->transTimes = 0;
            if(!$result) $this->error();
        }
        return true;
    }
    
     
    /**
     * 回滚事务
     * 
     * @return void
     */
    public function rollback()
    {
        if($this->transTimes > 0)
        {
            $result = $this->linkID->rollBack();
            $this->transTimes = 0;
            if(!$result) $this->error();
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
        $this->queryID = null;
    }
     
     
    /**
     * 析构函数
     * 
     * @return void
     */
    public function __destruct()
    {
   		$this->close();
   }
}