<?php
/**
 * oracle驱动器
 * 
 * @author maojianlw@139.com
 * @since 2012-04-09
 */

class DbOracle extends Db implements DbInterface
{
    
    
    /**
     * OCI模式
     * 
     * @var string
     */
    private $mode = OCI_COMMIT_ON_SUCCESS;
    
    
    /**
     * 匹配出的表
     * 
     * @var string
     */
    private $table = null;
    
    
    /**
     * 需替换的SQL
     * 
     * @var string
     */
    protected $replaceSql = 'SELECT * FROM (SELECT eaglephp.*, rownum AS numrow FROM (SELECT  #FIELDS# FROM #TABLE##JOIN##WHERE##GROUP##HAVING##ORDER#) eaglephp ) #LIMIT#';
    
    
    /**
     * 初始化构造函数
     * 
     * @return void
     */
    public function __construct($config)
    {
        putenv('NLS_LANG=AMERICAN_AMERICA.UTF8');
        if(!extension_loaded('oci8'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('oci8')));
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
        $conn = $pconnect ? 'oci_pconnect':'oci_new_connect';
        $this->linkID = $conn($dbuser, $dbpwd, "//{$dbhost}:$dbport/$dbname");
        if (!$this->linkID) throw_exception($this->error());
        return $this->linkID;
    }

    
    /**
     * 选择数据库
     * 
     * @param string $dbName
     * @return string
     */
    public function selectDb($dbName){
        return $dbName;
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
        $this->queryStr = $sql;
        $this->mode = OCI_COMMIT_ON_SUCCESS; // 更改事务模式
        if($this->queryID) $this->free();
        $this->queryID = oci_parse($this->linkID, $sql);
        if(oci_execute($this->queryID, $this->mode) === false)
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
        $flag = false;
        $matchs = array();
        if(preg_match('/^\s*(INSERT\s+INTO)\s+(\w+)\s+/i', $sql, $matchs))
        {
            $this->table = $matchs[2];
            $flag = (boolean)$this->query("SELECT * FROM user_sequences WHERE sequence_name='".strtoupper($this->table)."'");
        }
        $this->mode = OCI_COMMIT_ON_SUCCESS; // 更改事务模式
        if($this->queryID) $this->free();
        $this->queryID = oci_parse($this->linkID, $sql);
        if(oci_execute($this->queryID, $this->mode) === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = oci_num_rows($this->queryID);
            $this->insertID = ($flag) ? $this->insertID() : 0;
            return $this->numRows;
        }
    }

     
    /**
     * 提取所有记录
     * 
     * @return array
     */
    public function fetchAll(){
        $list = array();
        $this->numRows = oci_fetch_all($this->queryID, $list, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        foreach ($list as $k=>&$v)
        {
            $v = array_change_key_case($v, CASE_LOWER);
        }
        return $list;
    }
    
     
    /**
     * 获取最后插入记录的id（如果是数据为自动增长）
     * 
     * @return int
     */
    public function insertID()
    {
        if(!$this->table) return false;
        $value = $this->query("SELECT {$this->table}.currval FROM dual");
        return $value ? $value[0]['currval'] : 0;
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

    }
     
    
    /**
     * 返回上一个操作中产生的文本错误信息
     * 
     * @return string
     */
    public function error()
    {
        if($this->queryID)
        {
            $this->error = oci_error($this->queryID);
        }
        elseif($this->linkID)
        {
            $this->error = oci_error($this->linkID);
        }
        else
        {
            $this->error = oci_error();
        }
        return $this->error;
    }
    

    /**
     * 返回表中字段信息
     * 
     * @param string $tabeleName
     * @return array
     */
    public function fields($tabeleName)
    {
        $tabeleName = strtoupper($tabeleName);
        $fileds = array();
        if($list = $this->query("select a.column_name,data_type,decode(nullable,'Y',0,1) notnull,data_default,decode(a.column_name,b.column_name,1,0) pk from user_tab_columns a,(select column_name from user_constraints c,user_cons_columns col where c.constraint_name=col.constraint_name and c.constraint_type='P'and c.table_name='{$tabeleName}') b where table_name='{$tabeleName}' and a.column_name=b.column_name(+)"))
        {
            foreach($list as $k=>$field)
            {
                $filed_name = strtolower($field['column_name']);
                $fileds[$filed_name] = array(
					'name' => $filed_name,
					'type' => strtolower($field['data_type']),
					'notnull' => $field['notnull'],
					'default' => $field['data_default'],
					'primary' => $field['pk'],
					'autoinc' => $field['pk']
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
        $list = $this->query('SELECT table_name FROM user_tables');
        $tables = array();
        if(is_array($list))
        {
            foreach ($list as $k=>$v)
            {
                $tables[] = strtolower(current($v));
            }
        }
        return $tables;
    }
     
     
    /**
     * 开始事务
     * 
     * @return void
     */
    public function startTrans()
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->transTimes == 0){
            $this->mode = OCI_DEFAULT;
        }
        $this->transTimes ++;
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
            $result = oci_commit($this->linkID);
            if(!$result) throw_exception($this->error());
            $this->transTimes = 0;
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
            $result = oci_rollback($this->linkID);
            if(!$result) throw_exception($this->error());
            $this->transTimes = 0;
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
        oci_free_statement($this->queryID);
        $this->queryID = null;
    }
     
    
    /**
     * 关闭数据库连接
     * 
     * @return void
     */
    public function close()
    {
        $this->free();
        if(!oci_close($this->linkID)) throw_exception($this->error());
        $this->linkID = 0;
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
     
    
    /**
     * (non-PHPdoc)
     * @see Db::setLimit()
     */
    public function setLimit($options)
    {
        $limit_str = null;
        $limit = $options['limit'];
        if($limit)
        {
            $arr = explode(',', $limit);
            if(count($arr) > 1)
            {
                $limit_str = "(numrow > {$arr[0]}) AND (numrow<=".($arr[0]+$arr[1]).")";
            }
            else
            {
                $limit_str = "(numrow>0 AND numrow<={$arr[0]})";
            }
        }
        return $limit_str ? " WHERE $limit_str" : '';
   }
   
   
}
