<?php
/**
 * 数据模型对象基类
 * 
 * @author maojianlw@139.com
 * @since 2012-08-02 update
 * @copyright EaglePHP Group
 */

class Model 
{
	
	protected $fieldPath = null;
	protected $name = null; // 模型名称
	protected $tableName = null;  // 表名
	protected $db = null; // 数据库对象
	protected $pk = 'id'; // 默认主键名称
	protected $tablePrefix = ''; // 数据库表前缀
	
	protected $fields = array(); // 字段信息
	protected $data = array(); // 客户端提交数据
   	protected $options = array(); // SQL执行参数
   	
   	protected $isExists = true; // 模型对象是否存在标记
   	
   		
	/**
	 * 初始化数据连接
	 * 
	 * @param string $name
	 * @param string $flag
	 * @return object
	 */
   	public static function getModel($name=null, $flag=null)
   	{
   		static $m_cache = array();
   		$cacheName = "__{$flag}_{$name}_Model__";
		if(!isset($m_cache[$cacheName]))
		{
		    if(strpos($name, '.') !== false) // 是否调用其他app下面的模型对象
		    {
		        $nameArr = explode('.', $name);
		        $app = $nameArr[0]; // 应用名称
		        $name = $nameArr[1]; // 模型名称
		        $appDir = dirname(APP_DIR).__DS__.ucfirst($app).__DS__;
		    }
		    else
		    {
		        // 解决不同应用调用模型对象出现目录重叠的问题
		        $traceArr = debug_backtrace();
		        if(strrpos($file=$traceArr[1]['file'], 'Model.class.php'))
		        {
		            // 取得应用目录名称
		            $appDir = dirname(APP_DIR).__DS__.basename(dirname(dirname($file))).__DS__;
		        }
		        else
		        {
		            $appDir = APP_DIR;
		        }
		    }

		    $modelName = ucfirst($name) . 'Model';
		    import("Model.{$modelName}", true, $appDir);
			if(!($modelIsExists = class_exists($modelName, true))) $modelName = __CLASS__;
			$modelObj = new $modelName();
			$modelObj->isExists = $modelIsExists;
	   		$modelObj->db = Db::getInstance($flag);
	   		$modelObj->fieldPath = getCfgVar('cfg_orm_dir').__DS__.$modelObj->db->getDbName().__DS__;
	   		$modelObj->name = ($name) ? $name : $this->_getModelName();
	   		$modelObj->tablePrefix = $modelObj->db->getTablePrefix();
	   		$modelObj->_getTableName();
			$m_cache[$cacheName] = $modelObj;
		}
		return $m_cache[$cacheName];
   	}
   	
   	
	/**
	 * 魔术方法，针对特定的方法进行处理
	 * 
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args)
	{
		$name = strtolower($method);
		$arg = isset($args[0]) ? $args[0] : '';
		if(in_array($name, array('where', 'table', 'join', 'order', 'group', 'limit', 'having', 'field', 'lock'), true))
		{
			if($arg !== '')
			{
				if($name == 'where')
				{
			        if($arg === true)
			        {
			            $arg = $this->_autoWhereMode();
			        }
			        if(isset($this->options[$name]) && ($val = $this->options[$name]))
			        {
			           $arg .= ($arg ? ' AND ' : '').$val;
			        }
			    }
				$this->options[$name] = $arg;
			}
		}
		elseif(in_array($name, array('count', 'avg', 'sum', 'min', 'max'), true))
		{
			if(isset($this->options['field']))
			{
				$field = $this->options['field'];
			}
			else
			{
				$field = ($arg) ? $arg : '*';
			}
			return $this->totalSelect(strtoupper($name)."($field) AS ts_{$name}");
		}
		elseif($name == 'cache')
		{
		    $arg1 = isset($args[0]) ? $args[0] : 0; 
		    if($arg1 !== false)
		    {
		        $cache = array();
		        $cache['expire'] = (int)$arg1; // 有效期
    		    $cache['type'] = isset($args[1]) ? $args[1] : ''; // 缓存类型
    		    $cache['key'] = isset($args[2]) ? $args[2] : ''; // 缓存名称
    		    $this->options['cache'] = $cache;
		    }
		}
		elseif(substr($name,0,5) == 'getby')
		{
			$field = substr($name,5);
			if(!$this->fields) $this->_checkTableInfo();
			$options['where'] = $this->_packWhere($this->fields['_type'][$field], $field, $arg, false);
			return $this->find($options);
		}
		else
		{
			throw_exception(__CLASS__."::{$method} , method not exists.");
		}
		return $this;
	}
	
	
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}
	
	public function __get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public function getFieldList($tableName='')
	{
	    return $this->_checkTableInfo($tableName);
	}
	
	
	/**
	 * 统计查询
	 */
	protected function totalSelect($field, $where='')
	{
		if(empty($where) && isset($this->options['where'])){
			$where = $this->options['where'];
		}
		$this->options['where'] = $where;
		$this->options['field'] = $field;
		$options = $this->_getOptions();
		$this->options['limit'] = 1;
		$result = $this->db->select($options);
		$this->options = array();
		if($result){
			return reset($result[0]);
		}else{
			return null;
		}
	}
	
	
	/**
	 * 过滤数组中的空值
	 * @param string $val
	 * @return bool
	 */
	protected function _arrayFilterVal($val)
	{
	    return $val === '' ? false : true;
	}
	

	/**
	 * 自动提取表单中的查询条件封装成where语句
	 */
	protected function _autoWhereMode()
	{
		static $w_cache = array();
		$cache_name = '__AUTO_WHERE_MODE_STR__'; 
		if(isset($w_cache[$cache_name])){
			$where = $w_cache[$cache_name];
		}else{
			$data = array_filter($this->_getFormParams(), array($this, '_arrayFilterVal'));
			$fields = $this->_checkTableInfo();
			$where = '';
			if($data){
			    $whereArr = null;
				foreach($data as $key=>$val){
					$sep = '__';
					if(strpos($key, $sep) !== false){
						$arr = explode($sep, $key);
						$tableName = $arr[0];
						$fieldName = $arr[1];
	 					$fields2 = $this->_checkTableInfo($tableName);
	 					if(array_key_exists($fieldName, $fields2['_type']))
	 						$whereArr[] = $this->_packWhere($fields2['_type'][$fieldName], $tableName.'.'.$fieldName, $val);
					}elseif(array_key_exists($key, $fields['_type'])){
						$whereArr[] = $this->_packWhere($fields['_type'][$key], $this->_getTableName().'.'.$key, $val);
					}
				}
				if($whereArr)
					$where = implode(' AND ', $whereArr);
			}
			$w_cache[$cache_name] = $where;
		}
		return $where;
	}
	
	
	/**
	 * 数据类型转换
	 */
	protected function _packWhere($type, $field, $val, $isLike = true)
	{
		if(strpos($type, 'int') !== false){
			$sql = $field .'='. (int)$val;
		}elseif(in_array($type, array('float', 'double'))){
			$sql = $field .'='. (float)$val;
		}elseif($type == 'datetime'){
			$sql = "DATE_FORMAT($field, '%Y-%m-%d')='$val'";
		}else{
			$sql = ($isLike) ? "{$field} LIKE '%{$val}%'" : "$field='{$val}'";
		}
		return $sql;
	}
	
	
	/**
	 * 获得表单提交的参数
	 * @return array
	 */
	protected function _getFormParams()
	{
		$params = array();
		switch (HttpRequest::getRequestMethod())
		{
		    case 'POST':
		        $params = HttpRequest::getPost();
		        break;
		    case 'GET':
		        $params = HttpRequest::getGet();
		}
		return $params;
	}
	
	
	/**
	 * 设置数据
	 */
	protected function _setData($data='')
	{
		if(empty($this->fields)){
			$this->_checkTableInfo();
		}
		$data = !empty($data) ? $data : $this->_getFormParams();
		if(is_array($data))
		foreach($data as $key=>&$val){
			if(!array_key_exists($key, $this->fields['_type'])){
				unset($data[$key]);
			}else{
			    if(!is_scalar($val)){
			        continue;
			    }
				$type = strtolower($this->fields['_type'][$key]);
				if(strpos($type, 'int') !== false){
					$val = (int)$val;
				}elseif(in_array($type, array('float', 'double'))){
					$val = (float)$val;
				}elseif(is_bool($val)){
					$val = (string)$val;
				}
			}
		}
		
		return $data;
	}
	
	
	/**
	 * 获取表信息
	 */
	protected function _checkTableInfo($tableName = '')
	{
		$tableName = ($tableName) ? $tableName : $this->_getTableName();
		$this->fields = fileRW("{$this->fieldPath}{$tableName}");
		if(!$this->fields)
			$this->_getFields($tableName);
		return $this->fields;
	}
	
	protected function _getPk()
	{
	    $this->_checkTableInfo();
		return (isset($this->fields['_pk'])) ? $this->fields['_pk'] : $this->pk;
	}
	
	
	/**
	 * 获取当前数据对象名称
	 */
	protected function _getModelName()
	{
		if(empty($this->name)){
			$this->name = ucfirst(substr(get_class($this), 0, -5));
		}
		return $this->name;
	}
	
	/**
	 * 获取当前模型对象映射表名
	 */
	protected function _getTableName()
	{
		$this->tableName = $this->tablePrefix.strtolower($this->name);
		return $this->tableName;
	}
	
	
	public function tableName()
	{
	    return $this->_getTableName();
	}
	
	/**
	 * 获取数据库db对象
	 */
	public function getDb()
	{
		return $this->db;
	}
	
	/**
	 * 获取字段信息，如果信息不存在，则查询数据库自动缓存
	 */
	public function _getFields($tableName)
	{
		$fields = $this->db->fields(($tableName));
		if(!empty($fields)){
		    //$this->map = array_keys($fields);
    		$this->fields['_autoinc'] = false;
    		foreach($fields as $key=>$val){
    			$type[$key] = $val['type'];
    			if($val['primary']){
    				$this->fields['_pk'] = $key;
    				if($val['autoinc']){
    					$this->fields['_autoinc'] = true;
    				}
    			}
    		}
    		$this->fields['_type'] = $type;
    		fileRW("{$this->fieldPath}{$tableName}", $this->fields);
    		return $this->fields; 
		}else{
		    return false;
		}
	}
	
	protected function _getOptions($options=array())
	{
		if(is_array($options))
			$options = array_merge($this->options, $options);

		$this->options = array(); // 置为空，防止下次重复冲突。
		if(!isset($options['table'])){
			$options['table'] = $this->_getModelName();
		}
		if(!isset($options['primary'])){
			$options['primary'] = $this->_getPk();
		}
		return $options;
	}
	
	/**
	 * 新增数据前的回调方法
	 */
    protected function _beforeAdd(&$data, $options)
    {
    
    }
	
    /**
     * 新增数据后的回调方法
     */
	protected function _afterAdd(&$data, $options)
	{
	
	}
	
	/**
	 * 修改数据前的回调方法
	 */
	protected function _beforeUpdate(&$data, $options)
	{
	
	}
	
	/**
	 * 修改数据后的回调方法
	 */
	protected function _afterUpdate(&$data, $options)
	{
	
	}
	
	
	/**
	 * 增加
	 */
	public function add($data='', $options = array())
	{
		$data = $this->_setData($data);
		if($this->_beforeAdd($data, $options) === false)return false;
		$result = $this->db->insert($data, $this->_getOptions($options));
		if($result !== false){
			$insertID = $this->db->getInsertID();
			if($insertID){
				$data[$this->_getPk()] = $insertID;
				$this->_afterAdd($data, $options);
				return $insertID;
			}
		}
		return $result;
	}
	
	/**
	 * 替换 
	 */
	public function replace($data='', $options=array())
	{
		$options['replace'] = true;
		return $this->add($data, $options);
	}
	
	/**
	 * 修改
	 */
	public function save($data='', $options = array())
	{
		$data = $this->_setData($data);
		if($this->_beforeUpdate($data, $options) === false) return false;
		$options = $this->_getOptions($options);
		if(!isset($options['where'])){
			$pk = $this->_getPk();
			$pkValue = $data[$pk];
			if(isset($data[$pk])){
				$options['where'] = "$pk={$pkValue}";
				unset($data[$pk]);
			}
		}
		$result = $this->db->update($data, $options);
		if($result !== false){
			if(isset($pkValue)) $data[$pk] = $pkValue;
			$this->_afterUpdate($data, $options);
		}
		return $result;
	}
	
	/**
	 * 删除
	 */
	public function delete($options = array())
	{
		$data = $this->_setData();
		// 如果传递的值为空，则删除数据对象中的对应的记录
		if(empty($options) && empty($this->options)){
			$options = $data[$this->_getPk()];
		}
		if(is_numeric($options) || is_string($options)){
			$pk = $this->_getPk();
			$where = strpos($options, ',') ? " $pk IN($options)" : "$pk=$options";
			$options = array();
			$options['where'] = $where;
		}
		return $this->db->delete($this->_getOptions($options));
	}
	
	/**
	 * 查询
	 */
	public function select($options = array())
	{

	    if(in_array($this->db->getDbDriver(), array('sqlsrv', 'mssql'))){
	      $options['primary'] = $this->_getPk(); // 在此获取主键是为了兼容sql server 2000的分页模式。
	    }
		$result = $this->db->select($this->_getOptions($options));
		if($result === false){
			return false;
		}
		if(is_null($result)){
			return null;
		}
		return $result;
	}
	
	/**
	 * 查找一条
	 */
	public function find($options = array())
	{
		$this->options['limit'] = 1;
		$result = $this->db->select($this->_getOptions($options));
		if($result === false){
			return false;
		}
		if(is_null($result)){
			return null;
		}
		$this->data = isset($result[0]) ? $result[0] : '';
		return $this->data;
	}
	
	public function query($sql)
	{
		if(empty($sql)) return false;
		$sql = str_replace('#__', $this->tablePrefix, $sql);
		return $this->db->query($sql);
	}
	
	public function execute($sql)
	{
		if(empty($sql)) return false;
		$sql = str_replace('#__', $this->tablePrefix, $sql);
		return $this->db->execute($sql);
	}
	
	public function getLastSql()
	{
		return $this->db->getLastSql();
	}
	
	public function getInsertID()
	{
		return $this->db->getInsertID();
	}
	
	public function getDbError()
	{
		return $this->db->error();
	}
	
	public function startTrans()
	{
		$this->db->commit();
		$this->db->startTrans();
		return;
	}
	
	public function commit()
	{
		return $this->db->commit();
	}
	
	public function rollback()
	{
		return $this->db->rollback();
	}
	
	/**
	 *	判断表名是否存在
	 */
	public function tableExists($tableName='')
	{
		static $tables;
		$tableName = $tableName ? $tableName : $this->_getTableName();
		$tables = $tables ? $tables : $this->getDb()->tables();
		return in_array($tableName, $tables) ? true : false;
	}
	
	/**
	 * 判断当前模型是否存在
	 */
	public function isExists(){
		return $this->isExists;
	}
	
}

