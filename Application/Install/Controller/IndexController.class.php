<?php
/**
 * 
 * EaglePHP 安装包
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 *
 */
class IndexController extends Controller {
	
	
	public function __construct(){
		if(file_exists(DATA_DIR.'Install/INSTALL.LOCK')){
			redirect(__PUB__.'admin/index.php');
		}
	}
	
	public function indexAction() {
		$this->display();
	}

	public function oneAction() {
		$platform_arr = explode(' ', php_uname());
		$this->assign('platform', $platform_arr[0]);
		$this->assign('php_version', PHP_VERSION);
		$support_mysql = function_exists('mysql_connect');
		$support_mysqli = function_exists('mysqli_real_connect');
		if ($support_mysql || $support_mysqli) {
			$support = "<font color='#5fd300'><span>√</span></font>&nbsp;支持";
			if ($support_mysql) {
				$support .= 'mysql';
			}
			if ($support_mysql && $support_mysqli) {
				$support .= ',';
			}
			if ($support_mysqli) {
				$support .= 'mysqli';
			}
		} else {
			$support = "<font color='red'><span>×</span></font>";
		}
		
		$this->assign('support', $support);
		$dir_arr = array (
			array (
				'dir' => DATA_DIR,
				'desc' => '系统数据文件夹'
			),
			array (
				'dir' => CONF_DIR,
				'desc' => '系统配置文件夹'
			),
			array (
			    'dir' => PUB_DIR.'share/upload',
			    'desc' => '系统上传文件夹'
			)
		);
		foreach ($dir_arr as &$dir_info) {
			$dir_info['dir'] = realpath($dir_info['dir']);
			$dir_info['current_state'] = (is_writable($dir_info['dir']) ? '<font color="#5fd300"><strong>√</strong></font> 可写　' : '<font color="red"><strong>×</strong></font> 不可写');
		}

		$this->assign('dirs', $dir_arr);
		$this->display();
	}

	public function twoAction() {
		if($this->isPost()){
			$hostname = $this->post('hostname');
			$port = $this->post('port');
			$database = $this->post('database');
			$username = $this->post('username');
			$password = $this->post('password');
			$dbprefix = $this->post('dbprefix');
			if($hostname == '' || $port == '' || $database == '' || $username == ''){
				$this->assign('err_msg', '请填写完整.');
			}else{
				$dbs[__DEFAULT_DATA_SOURCE__] = array (
					'dbdriver' => 'mysql',
				    'dbtype' => 'mysql',
					'dbhost' => $hostname,
					'dbport' => $port,
					'dbuser' => $username,
					'dbpwd' => $password,
					'dbname' => $database,
					'dbcharset' => 'utf8',
				    'dbprefix' => $dbprefix
				);
				
				file_put_contents(CONF_DIR.'Database.php', "<?php\r\n \$dbs=".var_export($dbs, true). ";\r\nreturn \$dbs; \r\n?>");
				$model = model('Install');
				
				$databases = $model->query('SHOW DATABASES');
				
				$hava_db = false;
				if(is_array($databases)){
					foreach($databases as $dbName){
						$dbName = current($dbName);
						if($dbName == $database){
							$hava_db = true;
							break;
						}
					}
				}
				// 如果数据库不存在，则自动创建
				if(!$hava_db) $model->execute("CREATE DATABASE $database;");
				else redirect(__ROOT__.'?c=index&a=two', 3, '很抱歉，数据库‘'.$database.'’已经存在!');
				$this->redirect('?c=index&a=three');
			}
		}
		$this->display();
	}
	
	
	public function threeAction() {
		$path = realpath(CONF_DIR.'Database.php');
		$file_content = highlight_file($path, true);
		$this->assign('path', $path);
		$this->assign('file_content', $file_content);
		$this->display();
	}
		
	
	public function fourAction() {
		$install_model = model('install');
		$db_name = $install_model->getDb()->getDbName();
		$sql_content = file_get_contents(DATA_DIR.'Install/eaglephp.sql');
		$sql_arr = array_filter(explode(";\n", $sql_content));
		
		foreach ($sql_arr as $sql) {
			$sql = trim($sql);
			if($sql){
				$install_model->execute($sql);
			}
		}
		
		$table_arr = $install_model->query("SHOW TABLES FROM $db_name");
		if(is_array($table_arr)){
			$dbCfg = import('Database', false, CONF_DIR);
			$dbPrefix = $dbCfg[__DEFAULT_DATA_SOURCE__]['dbprefix'];
			
			foreach($table_arr as $tab){
				$tabName = array_shift($tab);
				if(!empty($dbPrefix)){
				   // 修改表前缀
				   $realTableName = $dbPrefix.$tabName;
				   $install_model->query("RENAME TABLE `{$tabName}` TO `{$realTableName}`");
				   $tabName = $realTableName;
				}
				$tables[] = $tabName;
			}
		}
		if(!Log::isError()) file_put_contents(DATA_DIR.'Install/INSTALL.LOCK', 'ok');
		$this->assign('tables', $tables);
		$this->assign('file', str_replace('\\', '//', $_SERVER['SCRIPT_FILENAME']));
		$this->display();
	}
	

}
?>