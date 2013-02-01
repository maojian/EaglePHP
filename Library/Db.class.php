<?php

/**
 * 数据库抽象类
 * @author maojianlw@139.com
 * @since 2012-2-1
 */

class Db
{

   	protected $linkID = '';
   	protected $queryID ='';

   	protected $numRows = 0;
   	protected $transTimes = 0; // 支持事务，事务个数
   	protected $insertID = '';
   	protected $queryStr = '';

   	protected $dbName = '';
   	protected $dbDriver = '';
   	protected $tablePrefix = '';

   	protected $error = '';
   	protected $erron = '';

    protected $config = array(); // 数据库配置信息
    protected $replaceSql = 'SELECT #FIELDS# FROM #TABLE##JOIN##WHERE##GROUP##HAVING##ORDER##LIMIT# #UNION# #LOCK#';
    
    protected static $connectNum = 0;//链接次数

    /**
     * 返回数据库实例
     */
    public static function getInstance($flag = __DEFAULT_DATA_SOURCE__)
    {
        $config = self::parseCfg($flag);
        $dbname = $config['dbname'];
        static $_db_cache_ = array();
        $name = "__DB_CACHE_{$flag}_{$dbname}__";
        if(isset($_db_cache_[$name]))
        {
            $db = $_db_cache_[$name];
        }
        else
        {
            // 优先采用系统配置中的数据库驱动器
            $dbtype = (empty($flag)) ? getCfgVar('cfg_mysql_type') : $flag;
            if(!in_array($dbtype, array('mysql', 'mysqli', 'pdo', 'oracle', 'sqlite')))
            {
                $dbtype = $config['dbtype'];
                if(in_array(strtolower($dbtype),array('mssql'),true))
                {
                    $dbtype = 'DbPdo';
                }
            }
            $className = 'Db'.ucfirst($dbtype);
            $db = new $className($config);
            $db->dbName = $dbname;
            	
            $db->dbDriver = strtolower($config['dbdriver']);
            $db->tablePrefix = strtolower($config['dbprefix']);
            self::$connectNum++;
            $db->connect(false);
            $_db_cache_[$name] = $db;
            unset($config);
        }
        return $db;
    }
    
    
    /**
     * 检查超时，尝试三次链接
     * 
     * @return void
     */
    public function checkTimeOut()
    {
        if(self::$connectNum >= 3) throw_exception('Attempt to connect to the database three timeout！');
    }


    /**
     * 初始化数据库连接
     *
     * @return void
     */
    public function checkContent()
    {
        $this->checkTimeOut();
        if(!is_resource($this->linkID))
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
     * 返回数据库驱动器
     */
    public function getDbDriver()
    {
        return $this->dbDriver;
    }

    /**
     * 返回当前数据库名称
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * 返回数据库表前缀
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * 增加记录
     */
    public function insert($data, $options)
    {
        if(empty($data)) return false;
        foreach($data as $key => $val)
        {
            $val = $this->setValue($val);
            if(is_scalar($val))
            {
                $fields[] = $this->setSign($key);
                $values[] = $val;
            }
        }
        $sql = ((isset($options['replace']) && $options['replace'] === true) ? 'REPLACE' : 'INSERT').
				' INTO '.$this->setTable($options).
				'('.implode(',', $fields).') VALUES ('.implode(',', $values).')'.
        $this->setLock($options);
         
        return $this->execute($sql);
    }

    /**
     * 修改记录
     */
    public function update($data, $options)
    {
        if(empty($data)) return false;
        foreach($data as $key => $val)
        {
            $val = $this->setValue($val);
            if(is_scalar($val))
            {
                $set[] = $this->setSign($key).'='.$val;
            }
        }
         
        $sql = 'UPDATE '.
        $this->setTable($options).' SET '.implode(',', $set).
        $this->setWhere($options).
        $this->setOrder($options).
        $this->setLimit($options).
        $this->setLock($options);

        return $this->execute($sql);
    }
     
    
    /**
     * 删除记录
     * 
     * @return mixed
     */
    public function delete($options)
    {
        if(!isset($options['where'])) return false;
        $sql = 'DELETE FROM '.
        $this->setTable($options).
        $this->setWhere($options).
        $this->setOrder($options).
        $this->setLimit($options).
        $this->setLock($options);
        return $this->execute($sql);
    }


   	/**
   	 * 查询记录
   	 *
   	 * @return array
   	 */
    public function select($options = array())
    {
        $sql = $this->setSql($options);
        $cache = (isset($options['cache']) ? $options['cache'] : false);
        if($cache)
        {
            $key = (!empty($cache['key']) ? $cache['key'] : md5($sql));
            $val = cache($key, '', $cache['expire'], $cache['type'], $options['table']);
            if($val !== false) return $val;
        }
        $data = $this->query($sql);
        if($cache !== false && $data)
        {
            cache($key, $data, $cache['expire'], $cache['type'], $options['table']);
        }
        return $data;
    }
    
    
    /**
     * 设置Sql where语句
     * 
     * @param array $options
     * @return string
     */
    public function setWhere($options)
    {
        $where = isset($options['where']) ? $options['where'] : '';
        if($where)
        {
            if(is_array($where))
            {
                $where = implode(' AND ', $where);
            }
            $where = " WHERE {$where}";
        }
        return $where;
    }
    

    /**
     * 定义表连接，为防止表名为数据库关键字，自动为表名添加`符号区分。
     * 
     * @param array $options
     */
    public function setTable($options)
    {
        if(!isset($options['table'])) return '';
        $tableNames = $options['table'];
        if(is_string($tableNames))
        {
            $tableNames = strtolower($tableNames);
            $tableNames = explode(',', $tableNames);
            foreach ($tableNames as &$tableName)
            {
                $tableName = !empty($this->tablePrefix) ? $this->tablePrefix.$tableName : $tableName;
                $tableName = $this->setSign($tableName);
            }
        }
        elseif(is_array($tableNames))
        { //模型对象table方法传递数组为支持表别名定义，如：model('user')->table(array('user'=>'u', 'role'=>'r'))->select();
            foreach ($tableNames as $tableName=>$alias)
            {
                $tableName = !empty($this->tablePrefix) ? $this->tablePrefix.$tableName : $tableName;
                $data[] = $this->setSign($tableName).' '.$alias;
            }
            $tableNames = $data;
        }
        return implode(',', $tableNames);
    }

    public function setJoin($options)
    {
        $join = isset($options['join']) ? $options['join'] : '';
        if(!empty($join))
        {
            if(stripos($join, 'JOIN') === false)
            {
                $join = "LEFT JOIN {$join}";
            }
            return ' '.$join;
        }
    }

    public function setOrder($options)
    {
        return isset($options['order']) && ($order = $options['order']) ? " ORDER BY {$order} " : '';
    }

    public function setGroup($options)
    {
        return isset($options['group']) && ($group = $options['group']) ? " GROUP BY {$group} " : '';
    }

    public function setHaving($options)
    {
        return isset($options['having']) && ($having = $options['having']) ? " HAVING {$having} " : '';
    }

    public function setLimit($options)
    {
        $limit = isset($options['limit']) ? $options['limit'] : '';
        if(!empty($limit) && (is_numeric($limit) || is_string($limit)))
        {
            return " LIMIT {$limit} ";
        }
    }

    public function setField($options)
    {
        return isset($options['field']) && ($field = $options['field']) ? $field : '*';
    }

    public function setUnion($options)
    {
        return isset($options['union']) && ($union = $options['union']) ? $union : '';
    }

    public function setLock($options)
    {
        return isset($options['lock']) && ($options['lock'] === true) ? ' FOR UPDATE ' : '';
    }

    public function serPrimary($options)
    {
        return isset($options['primary']) && ($primary = $options['primary']) ? $primary : '';
    }

    public function setSql($options=array())
    {
        return str_replace(
        array('#FIELDS#','#TABLE#','#JOIN#','#WHERE#','#GROUP#','#HAVING#','#ORDER#','#LIMIT#','#UNION#','#PRIMARY#','#LOCK#'),
        array(
        $this->setField($options),
        $this->setTable($options),
        $this->setJoin($options),
        $this->setWhere($options),
        $this->setGroup($options),
        $this->setHaving($options),
        $this->setOrder($options),
        $this->setLimit($options),
        $this->setUnion($options),
        $this->serPrimary($options),
        $this->setLock($options),
        ), $this->replaceSql);
    }


    /**
     * 值处理
     *
     * @param string $val
     * @return string
     */
    public function setValue(&$val)
    {
        if(is_string($val))
        {
            $val = '\''.$this->escapeStr($val).'\'';
        }elseif(is_null($val))
        {
            $val = 'null';
        }
        elseif(is_array($val) && array_key_exists('exp', $val))
        {
            $val = $this->escapeStr($val['exp']);
        }
        return $val;
    }


    /**
     * 为字段添加`符号，避免关键字引起歧义，只针对mysql处理
     *
     * @param string $val
     * @return string
     */
    public function setSign($val)
    {
        if(strtolower($this->dbDriver) == 'mysql')
        {
            $val = '`'.trim($val).'`';
        }
        return $val;
    }


    /**
     * 获得最近的错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

     
    /**
     * 解析数据库连接配置
     *
     * @param string $flag
     */
    protected static function parseCfg($flag)
    {
        $config = import('Config.Database', false, ROOT_DIR);
        if(isset($config[$flag]))
        {
            return $config[$flag];
        }
        else
        {
            throw_exception(language('SYSTEM:db.flag.not.exists', array($flag)));
        }
        return false;
    }


    /**
     * 获得最近执行的SQL语句
     *
     * @return void
     */
    public function getLastSql()
    {
        return $this->queryStr;
    }

    public function getInsertID()
    {
        return $this->insertID;
    }


    /**
     * SQL特殊字符处理
     *
     * @param string $str
     * @return string
     */
    public function escapeStr($str)
	{
	    return ($str);
	}

	
}
