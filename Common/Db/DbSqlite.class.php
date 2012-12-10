<?php
/**
 * Sqlite 数据库驱动器
 * 
 * @author maojianlw@139.com
 * @since 2012-08-27
 */

class DbSqlite extends Db implements DbInterface
{

    
    /**
     * 初始化
     * 
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        if(!extension_loaded('sqlite'))
        {
            throw_exception(language('SYSTEM:module.not.loaded', array('sqlite')));
        }
        if(!isset($config['mode']))
        {
            $config['mode'] = 0666;
        }
        $this->config = $config;
    }

    /**
     * (non-PHPdoc)
     * @see DbInterface::connect()
     */
    public function connect($pconnect = false)
    {
        $sqliteError = null;
        $conn = $pconnect === true ? 'sqlite_popen' : 'sqlite_open';
        $this->linkID = $conn($this->config['dbname'], $this->config['mode'], $sqliteError);
        if(!$this->linkID)
        {
            throw_exception($sqliteError);
        }
        return $this->linkID;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::selectDb()
     */
    public function selectDb($dbName)
    {
        return;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::query()
     */
    public function query($sql)
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        $this->queryStr = $sql;
        if($this->queryID) $this->free();
        $this->queryID = sqlite_query($this->linkID, $sql);
        if($this->queryID === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = sqlite_num_rows($this->queryID);
            return $this->fetchAll();
        }
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::execute()
     */
    public function execute($sql)
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        $this->queryStr = $sql;
        if($this->queryID) $this->free();
        $result = sqlite_exec($this->linkID, $sql);
        if($result === false)
        {
            $this->error();
            return false;
        }
        else
        {
            $this->numRows = sqlite_changes($this->linkID);
            $this->insertID = sqlite_last_insert_rowid($this->linkID);
            return $this->numRows;
        }
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::fetchAll()
     */
    public function fetchAll()
    {
        $data = array();
        if($this->numRows > 0)
        {
            for($i=0; $i<$this->numRows; $i++)
            {
                $data[$i] = sqlite_fetch_array($this->queryID, SQLITE_ASSOC);
            }
            sqlite_seek($this->queryID, 0);
        }
        return $data;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::insertID()
     */
    public function insertID()
    {
        return $this->insertID;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::affectedRows()
     */
    public function affectedRows()
    {
        return $this->numRows;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::errno()
     */
    public function errno()
    {
        return $this->erron = sqlite_last_error($this->linkID);
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::error()
     */
    public function error()
    {
        $this->error = sqlite_error_string($this->errno());
        if($this->queryStr != '') $this->error .= "[SQL]:{$this->queryStr}";
        Log::sql($this->error);
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::close()
     */
    public function close()
    {
        if($this->linkID && !sqlite_close($this->linkID))
        {
            throw_exception($this->error());
        }
        $this->linkID = 0;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::fields()
     */
    public function fields($tabeleName)
    {
        $fileds = array();
        if($list = $this->query("PRAGMA TABLE_INFO({$tabeleName})")){
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
        return $fileds;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::tables()
     */
    public function tables($dbName='')
    {
        $result = $this->query('SELECT name FROM sqlite_master WHERE type="table" UNION ALL SELECT name FROM sqlite_temp_master WHERE type="table" ORDER BY name');
        $tables = array();
        foreach($result as $k => $v)
        {
            $tables[$k] = current($v);
        }
        return $tables;
    }

    /**
     * (non-PHPdoc)
     * @see DbInterface::startTrans()
     */
    public function startTrans()
    {
        $this->checkContent();
        if(!$this->linkID) return false;
        if($this->transTimes == 0)
        {
            sqlite_query($this->linkID, 'BEGIN TRANSACTION');
        }
        $this->transTimes++;
        return;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::commit()
     */
    public function commit()
    {
        if($this->transTimes > 0)
        {
            if(!sqlite_query($this->linkID, 'COMMIT TRANSACTION'))
            {
                throw_exception($this->error());
            }
            $this->transTimes = 0;
        }
        return;
    }

    /**
     * (non-PHPdoc)
     * @see DbInterface::rollback()
     */
    public function rollback()
    {
        if($this->transTimes > 0)
        {
            if(!sqlite_query($this->linkID, 'ROLLBACK TRANSACTION'))
            {
                throw_exception($this->error());
            }
            $this->transTimes = 0;
        }
        return;
    }


    /**
     * (non-PHPdoc)
     * @see DbInterface::free()
     */
    public function free()
    {
        $this->queryID = 0;
    }


    /**
     * (non-PHPdoc)
     * @see Db::setLimit()
     */
    public function setLimit($options) {
        $limit = $options['limit'];
        $limitStr = null;
        if($limit)
        {
            $limit = explode(',', $limit);
            if(count($limit) > 1)
            {
                $limitStr = " LIMIT {$limit[1]} OFFSET {$limit[0]}";
            }
            else
            {
                $limitStr = " LIMIT {$limit[0]}";
            }
        }
        return $limitStr;
    }


}