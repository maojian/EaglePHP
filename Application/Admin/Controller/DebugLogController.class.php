<?php

/**
 * 系统日志管理
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-12-16
 */
 
class DebugLogController extends CommonController{
	
	private $logDirArr = null;
	private $logDir = null;
	private $cur_model = null;
	
    public function __construct() {
    	$this->logDir = dirname(LOG_DIR);
    	$this->logDirArr = $this->getLogDir();
    	$this->cur_model = model('debug_log');
    }
    
    
    /**
     * 获得日志目录
     */
    private function getLogDir(){
        if(!is_dir($this->logDir)) return false;
    	$dirObj = opendir($this->logDir);
    	$logDirArr = array();
    	while($file = readdir($dirObj)){
    		if($file!='.' && $file!='..' && is_dir($this->logDir.'/'.$file)){
    			$logDirArr[$file] = $file;
    		}
    	}
    	return $logDirArr;
    }
    
    
    public function indexAction(){
    	$page = $this->page($this->cur_model->where(true)->count(),'create_time');
		$list = $this->cur_model->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		
		$this->assign('apps',$this->logDirArr);
		$this->assign('list', $list);
		$this->assign('page', $page);
		$this->display();
    }
    
    
    /**
     * 获得设置
     */
    private function getSet(){
    	$set = '<settings>
			  <start_on_axis>0</start_on_axis>
			  <plot_area>
			    <border_alpha>100</border_alpha>
			    <margins>
			      <left>50</left>
			      <right>25</right>
			      <top>50</top>
			    </margins>
			  </plot_area>
			  <axes>
			    <x>
			      <tick_length>5</tick_length>
			      <width>1</width>
			    </x>
			    <y_left>
			      <tick_length>5</tick_length>
			      <width>1</width>
			      <type>stacked</type>
			    </y_left>
			  </axes>
			  <labels>
			    <label lid="0">
			      <text><![CDATA[<b>系统日志统计报表</b>]]></text>
			      <y>20</y>
			      <align>center</align>
			    </label>
			  </labels>
			</settings>';
		exit($set);
    }
    
    
    /**
     * 获得数据
     */
    private function getXMLData(){
    	$colors = array('#FF9E01','#0D8ECF','#64004B','#489620','#FCD202');
    	$num = 0;
    	$graph = $series = null;
    	foreach($this->logDirArr as $appName){
    		$graph .= '<graph title="'.$appName.'" line_width="2" bullet="round" color="'.$colors[$num].'">';
	    	for($i=14;$i>=0;$i--){
	    		$date = date('Y-m-d',strtotime("-$i DAY"));
	    		if($num == 0){
	    			$series .= "<value xid='{$i}'>{$date}</value>";
	    		}
	    		$count = $this->cur_model->where("DATE_FORMAT(create_time,'%Y-%m-%d')='{$date}' AND app_name='{$appName}'")->count();
	    		$graph .= "<value xid='{$i}'>$count</value>";
	    	}
	    	$graph .= '</graph>';
	    	$num++;
    	}
    	
    	$data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><chart><series>{$series}</series><graphs>{$graph}</graphs></chart>";
    	exit($data);
    }
    
    
    /**
     * 报表
     */
    public function reportAction(){
    	$flag = $this->get('flag');
    	if($flag == 'set'){
    		$this->getSet();
    	}else if($flag == 'data'){
    		$this->getXMLData();
    	}
    	$this->assign('settings_file', url(__ACTION__.'&flag=set', true));
    	$this->assign('data_file', url(__ACTION__.'&flag=data', true));
    	$this->display();
    }
    
    
    
    /**
     * 导入日志
     */
    public function importAction()
    {
    	$apps = $this->logDirArr;
    	$count = 0;
    	if(is_array($apps)){
    		foreach($apps as $appName){
    			$logDirName = "{$this->logDir}/{$appName}/debug/";
    			if(!is_dir($logDirName)){
    				continue;
    			}
    			$openDir = opendir($logDirName);
    			while($file = readdir($openDir)){
    				if($file == '.' || $file == '..'){
    					continue;
    				}
    				
    				// 正式导入日志数据
    				$logFile = "{$logDirName}{$file}/{$file}_debug.log";
    				if(file_exists($logFile)){
						$files = file($logFile);
						if(is_array($files)){
							foreach($files as $val){
								$dataArr = explode(Log::getSeparator(),$val);
								$data = array(
										'create_time'=>substr($dataArr[0],1,strlen($dataArr[0])-2),
										'level_name'=>$dataArr[1],
										'error_code'=>$dataArr[2],
										'msg'=>addslashes($dataArr[3]),
										'file_path'=>addslashes($dataArr[4]),
										'line_number'=>str_replace("\r\n",'',$dataArr[5]),
										'app_name'=>$appName);
								
								if(($this->cur_model->add($data)) > 0){
									$count++;
								}
							}
						}
						rename($logFile, $logFile.date('YmdHis').'.bak');
					}
					
    			}
    		}
    	}
    	
    	$this->ajaxReturn(200, "已成功增加 {$count} 条日志！");
    	
    }
   
    
}
?>