<?php

/**
 * cost manager class
 * @author maojianlw@139.com
 * @since 2012-1-27
 */

class CostController extends CommonController{

    private $curModel;
	
	public function __construct(){
		$this->curModel = M('cost');
	}
	
	
	/**
	 * 列表页
	 */
	public function indexAction(){
		$flag = $this->getParameter('flag');
		if($flag == 'set'){
			$this->getFlashSetting();
		}elseif($flag == 'data'){
			$this->getFlashData();
		}elseif($flag == 'page'){
			$this->flashPage();
		}else{
		    $remark =$this->getParameter('remark');
		    $startTime = $this->getParameter('startTime');
		    $endTime = $this->getParameter('endTime');
		    $sql = null;
		    if($remark){
		         $sql[] = "remark LIKE '%{$remark}%'";
		    }
		    if($startTime && $endTime){
		         $sql[] = "(usetime BETWEEN '{$startTime}' AND '{$endTime}')"; 
		    }
		    
			$page = $this->page($this->curModel->where($sql)->count(), 'usetime');
			$list = $this->curModel->where($sql)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();	
			if($list)
			{
				foreach($list as &$val)
				{
					$val['money'] = number_format($val['money'], 2);
				}
			}
			$costSum = $this->curModel->field('money')->where($sql)->sum();
			$this->assign('costSum', number_format($costSum, 2));
			$this->assign('list', $list);
			$this->assign('page', $page);
			$this->display();
		}
	}
	

	/**
	 * 添加
	 */
	public function addAction(){
		if(count($_POST) > 0){
			if($this->curModel->add()){
				$this->ajaxReturn(200, '添加成功');
			}else{
				$this->ajaxReturn(300, '添加失败');
			}
		}else{
			$this->display();
		}
	}
	
	/**
	 * 修改
	 */
	public function updateAction(){
		if(count($_POST) > 0){
			if($this->curModel->save()){
				$this->ajaxReturn(200, '修改成功');
			}else{
				$this->ajaxReturn(300, '修改失败');
			}
		}else{
			$id = (int)$_REQUEST['id'];
			$info = $this->curModel->where("id=$id")->find();
			$this->assign('info',$info);
			$this->display();
		}
	}
	
	/**
	 * 删除
	 */
	public function deleteAction(){
		$ids = $_REQUEST['ids'];
		if(!empty($ids) && $this->curModel->where("id IN($ids)")->delete()){
			$this->ajaxReturn(200, '删除成功');
		}else{
			$this->ajaxReturn(300, '删除失败');
		}
	}
	
	
	/**
	 * 明细
	 */
	 public function reportAction(){
	 	
	 	$particularDate = $this->getParameter('particularDate');
	 	if($particularDate){ // 查看月消费明细
	 		$list = $this->curModel->field('remark,money,usetime')->order('usetime DESC')->where("DATE_FORMAT(usetime,'%Y-%m')='{$particularDate}'")->select();
			if($list)
			{
				foreach($list as &$val)
				{
					$val['money'] = number_format($val['money'], 2);
				}
			}
			$this->assign('list', $list);
	 		$this->display('Cost/particular');
	 		exit;
	 	}
	 	
	 	$startDate = $this->getParameter('startDate');
	 	$endDate = $this->getParameter('endDate');
	 	
	 	if(!$startDate || !$endDate){
	 		$startDate = date('Y-m', strtotime('-10 month'));
	 		$endDate = date('Y-m');
	 		
	 		$_POST['startDate'] = $startDate;
	 		$_POST['endDate'] = $endDate;
	 	}elseif($startDate > $endDate){
	 		$this->ajaxReturn(300, '开始日期必须小于或等于结束日期!');
	 	}
	 	
	 	$dateArr = null;
	 	function getDateRange($startDate, $endDate, $i, &$dateArr){
	 		$date = date('Y-m', strtotime("+{$i} month", strtotime($startDate)));
	 		$i++;
	 		if($date != $endDate){
	 			getDateRange($startDate, $endDate, $i, $dateArr);
	 		}
	 		$dateArr[] = $date;
	 	}
	 	
	 	if($startDate && $endDate)
			getDateRange($startDate, $endDate, 0, $dateArr);
		
		$incomeM = M('income');
		$accountM = M('account');
		$info = array('incomeTotal'=>0, 'accountTotal'=>0, 'costTotal'=>0);
		if(is_array($dateArr)){
			foreach($dateArr as $k=>$date){
				$sql = "DATE_FORMAT(usetime,'%Y-%m')='{$date}'";
				$incomeSum = $incomeM->field('money')->where($sql)->sum();
				$accountSum = $accountM->field('money')->where($sql)->sum();
				$costSum = $this->curModel->field('money')->where($sql)->sum();
				
				$info['incomeTotal'] += (float)$incomeSum;
				$info['accountTotal'] += (float)$accountSum;
				$info['costTotal'] += (float)$costSum;
				
				$list[] = array('date'=>$date, 'incomeSum'=>number_format($incomeSum,2), 'accountSum'=>number_format($accountSum,2), 'costTotal'=>$costSum, 'costSum'=>number_format($costSum,2));
			}
			
			$total = $info['incomeTotal'];
			$count = count($list)-1;
			for($i=$count; $i>=0; $i--){ // 统计每月结存
				$total = $total - $list[$i]['costTotal'];
				$list[$i]['balanceSum'] = number_format($total,2);
			}
			
			$info['balanceTotal'] = number_format(($info['incomeTotal'] - $info['costTotal']), 2);
			$info['incomeTotal'] = number_format($info['incomeTotal'], 2);
			$info['accountTotal'] = number_format($info['accountTotal'], 2);
			$info['costTotal'] = number_format($info['costTotal'], 2);
	
			$this->assign('info', $info);
			$this->assign('list', $list);
		}
	 	$this->display();
	 }
	 
	
	protected function flashPage(){
		$this->display('Cost/flash');
	}
	
	/**
	 * 获得flash数据
	 */
	protected function getFlashData(){
		$startDate = $_POST['startDate'];
	 	$endDate = $_POST['endDate'];
	 	
	 	if(!$startDate && !$endDate){
	 		$startDate = date('Y-m-d', strtotime('-30 DAY'));
	 		$endDate = date('Y-m-d');
	 	}
	 	elseif($startDate > $endDate){
	 		$this->ajaxReturn(300, '开始日期必须小于或等于结束日期!');
	 	}
		$list = $this->curModel->field('SUM(money) AS money,usetime')->where("(usetime BETWEEN '{$startDate}' AND '{$endDate}')")->group('usetime')->order('usetime ASC')->limit(30)->select();
		if($list){
			foreach($list as $key=>$val){
				$xml1 .= "<value xid='{$key}'>{$val['usetime']}</value>";
				$xml2 .= "<value xid='{$key}'>{$val['money']}</value>";
			}
		}
		echo $xml = "<?xml version='1.0' encoding='UTF-8'?><chart><series>{$xml1}</series><graphs><graph gid='1'>{$xml2}</graph></graphs></chart>";
	}
	
	
	/**
	 * 获得flash风格配置
	 */
	protected function getFlashSetting(){
		$xml = <<<EOT
			<?xml version="1.0" encoding="UTF-8"?>
			<settings>
			  <background>
			    <alpha>100</alpha>
			    <border_alpha>15</border_alpha>
			  </background>
			  <plot_area>
			    <margins>
			      <left>70</left>
			      <right>50</right>
			    </margins>
			  </plot_area>
			  <grid>
			    <category>
			      <alpha>5</alpha>
			    </category>
			    <value>
			      <alpha>5</alpha>
			    </value>
			  </grid>
			  <axes>
			    <category>
			      <width>1</width>
			    </category>
			    <value>
			      <width>1</width>
			    </value>
			  </axes>
			  <values>
			    <category>
			      <frequency>5</frequency>
			    </category>
			    <value>
			      <min>0</min>
			    </value>
			  </values>
			  <legend>
			    <enabled>0</enabled>
			  </legend>
			  <column>
			    <width>85</width>
			    <spacing>0</spacing>
			    <balloon_text>Anomaly in {series}: {value}C</balloon_text>
			    <grow_time>3</grow_time>
			    <sequenced_grow>1</sequenced_grow>
			  </column>
			  <line>
			    <balloon_text>Anomaly in {series}: {value}C (Smoothed)</balloon_text>
			  </line>
			  <graphs>
			    <graph gid="1">
			      <title>Anomaly</title>
			      <color>B92F2F</color>
			      <balloon_text>在 {series} 花费 {value} 元。</balloon_text>
			    </graph>
			    <graph gid="2">
			      <title>Smoothed</title>
			      <type>line</type>
			    </graph>
			  </graphs>
			  <labels>
			    <label lid="0">
			      <text><![CDATA[<b>每日消费图形报表</b>]]></text>
			      <y>25</y>
			      <align>center</align>
			    </label>
			  </labels>
			</settings>
EOT;
		echo $xml;
	}
}
?>