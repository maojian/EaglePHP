<?php
/**
 * 微软官方提供的php连接SQL Server驱动器(Sqlsrv)
 * 
 * @author maojianlw@139.com
 * @since 2012-2-11
 */

class DbSqlsrv extends Db implements DbInterface
{

    /**
     * 如果你的SQL Server为2000以上，请用下面这条查询效率更高的sql语句。下面的setLimit方法也需要自行修改下
     * protected $replaceSql = 'SELECT E1.* FROM (SELECT ROW_NUMBER() OVER (#ORDER#) AS ROW_NUMBER, eaglephp.* FROM (SELECT #DISTINCT# #FIELDS# FROM #TABLE##JOIN##WHERE##GROUP##HAVING#) AS eaglephp) AS E1 WHERE #LIMIT#';
     * @var string
     */
    protected $replaceSql = 'SELECT #FIELDS# FROM #TABLE# WHERE #PRIMARY# > (SELECT ISNULL(MAX(#PRIMARY#),0) FROM (SELECT #LIMIT# #PRIMARY# FROM #TABLE##JOIN##WHERE##GROUP##HAVING##ORDER# #UNION# #LOCK#) eaglephp )#ORDER#';

    
    /**
     * 初始化
     * 
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        if(!extension_loaded('sqlsrv'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('sqlsrv')));
        }
        $this->config = $config;
    }

    
    /**
     * 连接数据库
     * 
     * @param bool $pconnect
     * @return Object
     */
    public function connect($pconnect = false)
    {
        extract($this->config);
        $host = $dbhost.($dbport ? ", {$dbport}" : '');
        $connInfo = array('Database'=>$dbname, 'UID'=>$dbuser, 'PWD'=>$dbpwd, 'CharacterSet'=>$dbcharset, 'ReturnDatesAsStrings'=>true);
        $this->linkID = sqlsrv_connect($host, $connInfo);
        if(!$this->linkID)
        {
            $this->error();
        }
        return $this->linkID;
    }


    /**
     * 选择数据库
     * 
     * @return string
     */
    public function selectDb($dbName)
    {
        return $dbName;
    }
     

    /**
     * 执行查询
     * 
     * @param string $sql
     * @return mixed
     */
    public function query($sql)
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->queryID) $this->free();
        $this->queryStr = $sql;
        $params = array();
        $options =  array('Scrollable' => SQLSRV_CURSOR_KEYSET );
        $this->queryID = sqlsrv_query($this->linkID, $sql, $params, $options);
        if($this->queryID === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = $this->affectedRows();
            return $this->fetchAll();
        }
    }
     
     
    /**
     * 设置字段加分页
     * 
     * @see Db::setField()
     * @return string
     */
    public function setField($options) 
    {
        $limit = $options['limit'];
        if($limit && strpos($limit, ',') !== false)
        {
            $limitArr = array_filter(explode(',', $limit));
            $limit = $limitArr[1];
        }
        return (($limit ? " TOP {$limit} " : ' ').(!empty($options['field']) ? $options['field'] : '*'));
    }
     
     
    /**
     * 设置分页
     * 
     * @see Db::setLimit()
     * @return string
     */
    public function setLimit($options)
    {
        $limit = $options['limit'];
        if($limit && strpos($limit, ',') !== false)
        {
            $limitArr = array_filter(explode(',', $limit));
            $limit = $limitArr[0];
        }
        else
        {
            $limit = 0;
        }
        return (" TOP {$limit} ");
    }
     
    
    /**
     * 执行语句
     * 
     * @param string $sql
     * @return mixed
     */
    public function execute($sql)
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->queryID) $this->free();
        $this->queryStr = $sql;
        $params = array();
        $options =  array('Scrollable' => SQLSRV_CURSOR_KEYSET );
        $this->queryID = sqlsrv_query($this->linkID, $sql, $params, $options);
        if($this->queryID === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = $this->affectedRows();
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
    public function fetchAll(){
        $data = array();
        if($this->numRows > 0)
        {
            while ($row = sqlsrv_fetch_array($this->queryID, SQLSRV_FETCH_ASSOC))
            {
                $data[] = $row;
            }
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
        $result = sqlsrv_query($this->linkID, 'SELECT @@IDENTITY AS insertID');
        list($insertID) = sqlsrv_fetch_array($result);
        sqlsrv_free_stmt($result);
        return $insertID;
    }
     
     
    /**
     * 返回上一个操作所影响的行数
     * 
     * @return mixed
     */
    public function affectedRows()
    {
        return ($this->queryID) ? sqlsrv_num_rows($this->queryID) : false;
    }
     
     
    /**
     * 返回上一个操作中的错误信息的数据编码
     * 
     * @return int
     */
    public function errno()
    {
        $errorInfo = sqlsrv_errors();
        return $errorInfo[0]['code'];
    }
     
     
    /**
     * 返回上一个操作中产生的文本错误信息
     * 
     * @return void
     */
    public function error()
    {
        $errorInfo = sqlsrv_errors();
        $this->error = $errorInfo[0]['message'];
        if($this->queryStr) $this->error .= "[SQL]:{$this->queryStr}";
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
        if($this->linkID && !sqlsrv_clolse($this->linkID)) $this->error();
        $this->linkID = 0;
    }
     
     
    /**
     * 获得表字段结构信息
     * 
     * @param string $tableName
     * @return array
     */
    public function fields($tabeleName)
    {
        $fileds = array();
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
                $fileds[$field['name']] = array(
					'name' => $field['name'],
					'type' => $field['type'],
					'notnull' => (bool)($field['allow_null'] == 'yes'),
					'default' => $field['default_value'],
					'primary' => (bool)($field['is_primary'] == 'yes'),
					'autoinc' => (bool)($field['is_autoinc'] == 'yes')
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
        if($list = $this->query('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE=\'BASE TABLE\''))
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
        if($this->transTimes == 0)
        {
            sqlsrv_begin_transaction($this->linkID);
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
        if($this->transTimes > 0)
        {
            $result = sqlsrv_commit($this->linkID);
            $this->transTimes = 0;
            if(!$result) throw_exception($this->error());
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
            $result = sqlsrv_rollback($this->linkID);
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
        sqlsrv_free_stmt($this->queryID);
        $this->queryID = 0;
    }


}