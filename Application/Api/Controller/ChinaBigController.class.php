<?php

class ChinaBigController extends Controller{
	
	private $curModel = null;
	
    public function __construct(){
    	set_time_limit(0);
    	$this->curModel = M('enterprise');
    }
    
    
    public function indexAction(){
    	//exit; 1 ~ 20
    	for($i=1101; $i<=1150; $i++){//1151
    		$this->get($i);
    	}
    	echo 'ok';
    }
    
    
    public function get($page){
    	
    	$content = file_get_contents('http://www.chinabig.net/company/search-htm-kw--vip-0-type-0-catid-0-mode-0-areaid-231-size-0-mincapital--maxcapital--x-47-y-23-page-'.$page.'.html');
    	//$content = file_get_contents('data/1.htm');
    	$content = str_replace(chr(13).chr(10),'',$content);
    	$matchs = array();
    	preg_match("/<div class=\"list\">(.*)<div class=\"pages\">/", $content, $matchs);
    	
    	$xml = '<root><div class="list">'.str_replace('<div class="pages">','',$matchs[1]).'</root>';
    	$xml = str_replace('&nbsp;', '', $xml);

    	$xmlArr = XML_unserialize($xml);
    	$list = $xmlArr['root']['div'];
    	
		if($list){
			foreach($list as $key=>$val){
				
				if(!is_numeric($key)){
					continue;
				}
				
				// 获取唯一标识符
				$href = $val['table']['tr']['td'][0]['div']['a attr']['href'];
				preg_match('/http:\/\/www.chinabig.net\/(index.php\?homepage=(.*)|com\/(.*?)\/)/', $href, $biaozhiArr);
				$flag = ($biaozhiArr[2] ? $biaozhiArr[2] : $biaozhiArr[3]);
				
				if(empty($flag)){
					continue;
				}
				
				if($this->curModel->where("flag='{$flag}'")->find()){
					continue;
				}
				
				/*
				// 获取图片
				$img = $val['table']['tr']['td'][0]['div']['a']['img attr']['src'];
				*/
				
				// 获得省份和城市
				$addr = $val['table']['tr']['td'][4];
				preg_match('/\[(.*?)\/(.*?)\]/', $addr, $addrArr);
				$province = $addrArr[1];
				$city = $addrArr[2];
				
				//获得公司名称
				$name = $val['table']['tr']['td'][2]['ul']['li'][0]['a']['strong'];
				if((strpos($name,'|') !== false)){
					$nameArr = explode('|', $name);
					$name = $nameArr[0];
					if($name == '下载阿里旺旺'){
						continue;
					}
				}
				
				$row1 = array(
					'name' => addslashes($name),
					'province' => $province,
					'city' => $city,
					//'img' => $img,
					'flag' => $flag
				);
				
				$row2 = $this->introduce($flag, $name);
				$row3 = $this->beian($row2['wangzhi']);
				$row3['createtime'] = date('Y-m-d H:i:s');
				
				$data = array_merge($row1, $row2, $row3);
				$this->curModel->add($data);
				
			}
		}    	  
    }
    
    
    /**
     * 修复数据
     */
    public function updateAction(){
    	$arr = array('gzsltg');
    	if($arr)
    	foreach($arr as $flag){
    		$info = $this->curModel->where("flag='{$flag}'")->find();
    		$name = $info['name'];
    		
    		$row2 = $this->introduce($flag, $name);
			$row3 = $this->beian($row2['wangzhi']);
			$nslookup = $this->nslookup($row2['wangzhi']);
			$row3['nslookup'] = $nslookup; 
			$data = array_merge($row2, $row3);
			
			$this->curModel->where("flag='{$flag}'")->save($data);
    	}
    }
    
    
    public function editAction(){

    	$list = $this->curModel->field('id,name')->where("id>=11944 and wangzhi='' and status=0")->select(); //id>=11944 
    	
    	$i = 0;
    	foreach($list as $info){
    		$i++;
    		$id = $info['id'];
	    	//$wangzhi = strtolower($info['wangzhi']);
	    	$wangzhi = $this->google($info['name']);
	    	$data = $this->beian($wangzhi);
	    	$data['status'] = 1;
	    	$data['wangzhi'] = $wangzhi;
	    	$this->curModel->where("id=$id")->save($data);
    	}
    	echo $i;
    }
    
    
    public function xiufuAction(){
    	$arr = $this->curModel->field('id,wangzhi')->where("createtime > '2012-01-09' and id=7508 and wangzhi!='' and beianhao=''")->select();
    	$i = 0;
    	foreach($arr as $val){
    		$id = $val['id'];
    		
    		/*
    		$name = $val['name'];
    		$wangzhi = $this->google($name);

			if(empty($wangzhi) || $wangzhi == $val['wangzhi']){
				$this->curModel->where("id=$id")->save(array('status'=>1));
				continue;
			}

			$i++;
    		$row2 = $this->beian($wangzhi);
			$nslookup = $this->nslookup($wangzhi);

			$row3 = array('status'=>1, 'wangzhi'=>$wangzhi, 'nslookup'=>$nslookup);
			*/
			
			//$data = array_merge($row2, $row3);
			//$data = $this->introduce($val['flag'], $val['name']);
			$data = $this->beian($val['wangzhi']);
			if(count($data) > 0){
				echo $id;
				dump($data);
				$this->curModel->where("id=$id")->save($data);
				$i++;
			}
    	}
    	echo 'ok';
    	echo $i;
    }
    
    
    
    /**
     * 获得企业详细信息
     */
    public function introduce($flag, $name){
    	
    	$data = array();
    	if(empty($flag)){
    		return $data;
    	}
    	
    	$url = 'http://www.chinabig.net/com/'.$flag.'/introduce/';
    	$content = file_get_contents($url);
    	if($content === false){
    		$content = file_get_contents($url);
    	}
    	
    	$content = str_replace(chr(13).chr(10),'',$content);
    	
    	$matchs = array();
   
    	// 获取公司内容
    	preg_match('/style="margin:5px 0 5px 10px;padding:5px;border:#C0C0C0 1px solid;"\/>(.*?)<\/td>	<\/tr>	<\/table>	<\/div><\/div>/', $content, $matchs);
		$patterns = array('/\&nbsp;/', '/\&ldquo;/', '/\&rdquo;/', '/\r/', '/\n/', '/\t/');
		$replacements = array(' ', '(', ')', '', '', '');    	    		
		$data['content'] = trim(preg_replace($patterns, $replacements, addslashes(strip_tags($matchs[1]))));
 		
    	// 公司类型
    	preg_match('/公司类型：<\/td>	<td width="260">(.*?)<\/td>/', $content, $matchs);
    	$data['type'] = $matchs[1];
    	
    	// 公司规模
    	preg_match('/公司规模：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['guimo'] = $matchs[1];
    	
    	// 注册资本
    	preg_match('/注册资本：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['zhuceziben'] = $matchs[1];
    	
    	// 注册年份
    	preg_match('/注册年份：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['zhucenianfen'] = $matchs[1];
    	
    	/*
    	// 资料认证
    	preg_match('/资料认证：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['ziliaorenzheng'] = $matchs[1];
    	
    	// 经营模式
    	preg_match('/经营模式：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['jingyingmoshi'] = $matchs[1];
    	*/
    	
    	// 经营范围
    	preg_match('/经营范围：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['jingyingfanwei'] = $matchs[1];
    	
    	// 销售的产品
    	preg_match('/销售的产品：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['chanpin'] = $matchs[1];
    	
    	// 主营行业
    	preg_match('/主营行业：<\/td>	<td>(.*?)<\/td>			<\/table>	<\/td>/', $content, $matchs);
    	$hangye = $matchs[1];
    	if(!empty($hangye))
    		$hangye = str_replace('				',' | ',trim(strip_tags($matchs[1])));
    	$data['hangye'] = $hangye;
    	
    	// 公司地址
    	preg_match('/公司地址：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['dizhi'] = $matchs[1];
    	
    	// 公司电话
    	preg_match('/公司电话：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['dianhua'] = $matchs[1];
    	
    	
    	$wangzhi = $this->google($name);
    	
    	if(empty($wangzhi)){
    		// 公司网址
	    	preg_match('/公司网址：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
	    	$wangzhi = trim($matchs[1]);
	    	if(!empty($wangzhi)){
	    		preg_match('/arget="_blank">(.*?)<\/a>/', $wangzhi, $matchs);
	    		$urlInfo = parse_url($matchs[1]);
	    		$wangzhi = strtolower($urlInfo['host']);
	    			
		    	if(strpos($wangzhi, 'chinabig') !== false || strpos($wangzhi, 'chinahr') !== false){
		    		$wangzhi = '';
		    	}
		    	
		    	// 如：htt://www.baidu.com http://www.google.com 选取第一个
		    	if(strpos($wangzhi, ' ') !== false){
		    		$wangzhiArr = explode(' ', $wangzhi);
		    		$wangzhi = trim(strtolower($wangzhiArr[0]));
		    	}
		    	
		    	/*
		    	$nslookup = $this->nslookup($wangzhi);
		    	if(strpos($nslookup, 'alibaba') !== false){
		    		$googleWangzhi = $this->google($name);
		    		if($wangzhi != $googleWangzhi){
		    			$wangzhi = $googleWangzhi;
		    			$nslookup = $this->nslookup($wangzhi);
		    		}
		    	}
		    	*/
	    	}
    	}
    	
    	$nslookup = $this->nslookup($wangzhi);

    	$data['wangzhi'] = $wangzhi;
    	$data['nslookup'] = $nslookup;
    	
    	
    	// 联 系 人
    	preg_match('/联 系 人：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['lianxiren'] = $matchs[1];
    	
    	// 手机号码
    	preg_match('/手机号码：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['shoujihaoma'] = $matchs[1];
    	
    	/*
    	// 最近登录
    	preg_match('/最近登录：<\/td>	<td>(.*?)<\/td>/', $content, $matchs);
    	$data['zuijindenglu'] = $matchs[1];
		*/
		
    	return $data;
    }
    
    
    public function beiantestAction(){
    	dump($this->beian('www.onlense.com'));
    }
    
    
    /**
     * 查询备案
     */
    public function beian($wangzhi){
		
		$data = array();
		if(empty($wangzhi)){
			return $data;
		}
		
		if(strpos($wangzhi,'hc360') !== false){
			return $data;
		}
		
		$url = 'http://www.beianchaxun.net/search/'.$wangzhi;
		$content = file_get_contents($url);

		if($content === false){
			sleep(2);
			$content = file_get_contents($url);
		}
    	
    	$content = str_replace(chr(10).chr(13),'',$content);
    	$content = iconv('gbk', 'utf-8', $content);
    	
    	$matchs = array();
    	preg_match('/<a href="(.*?)" target="_blank">详细信息<\/a>/', $content, $matchs);
    	
    	$beianInfo = $matchs[1];
    	
    	if(empty($beianInfo)){
    		return $data;
    	}
    	
    	$url = 'http://www.beianchaxun.net'.$beianInfo;

    	// 正式获取备案信息
    	$content = file_get_contents($url);
    	if($content === false){
    		sleep(2);
			$content = file_get_contents($url);
		}
    	
    	$content = str_replace("\r", '', $content); 
    	$content = str_replace("\n", '', $content); 
    	$content = str_replace("\t", '', $content); 
    	$content = iconv('gbk', 'utf-8', $content);
		
    	// 备案/许可证号
    	preg_match('/备案\/许可证号：<\/td><td align="left" class="by1" width="30%">(.*?)<\/td>/', $content, $matchs);
    	$data['beianhao'] = trim($matchs[1]);
 		
    	// 网站负责人姓名
    	preg_match('/网站负责人姓名：<\/td><td align="left" class="by1">(.*?)<\/td>/', $content, $matchs);
    	$data['wangzhfuzeren'] = trim($matchs[1]);
    	
    	// 主办单位性质
    	preg_match('/主办单位性质：<\/td><td align="left" class="by2">(.*?)<\/td>/', $content, $matchs);
    	$data['danweixingzhi'] = trim($matchs[1]);
    	
    	// 审核通过时间
    	preg_match('/审核通过时间：<\/td><td align="left" class="by2" width="30%">(.*?)<\/td>/', $content, $matchs);
    	$data['shenheshijian'] = trim($matchs[1]);
    
		return $data;
    }
    
    
    public function nslookup($wangzhi){
    	
    	if(empty($wangzhi)){
    		return '';
    	}
    	$wangzhi = strtolower($wangzhi);
    	
    	$arr = array();
    	$returnVar = 0;
    	preg_match('/www.(.*)/', $wangzhi, $matchs);
    	if(count($matchs) > 0){
    		$wangzhi = $matchs[1];
    	}
    	exec("nslookup -qt=mx {$wangzhi}", $arr, $returnVar);
    	if(count($arr) > 0){
    		foreach($arr as $val){
    			$matchs = array();
    			preg_match('/(.*?)	MX preference = (.*?), mail exchanger = (.*)/', $val, $matchs);
    			if(!empty($matchs)){
    				$nslookupArr[] = $matchs;
    			}
    		}
    	}
    	
    	if($nslookupArr){
    		$nslookupArr = list_sort_by($nslookupArr, '2');
    		foreach($nslookupArr as $key=>$ns){
    			$level = $ns[2];
    			if($level != $nslookupArr[$key+1][2]){
    				break;
    			}
    		}
    		$nslookup = $nslookupArr[$key][3];
    	}
    	return $nslookup;
    }
    
    
    public function google($name){
    	if(empty($name)){
    		return '';
    	}
		
    	$url = 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q='.urlencode($name);  

		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, 'http://www.mysite.com/index.html');
		$body = curl_exec($ch);
		
		if(curl_errno($ch)){
			
			Log :: info(curl_error($ch)." company:$name");
			sleep(5);
			return $this->google($name);
			
		}else{
			$json = json_decode($body,true);
			$status = $json['responseStatus'];
			if($status != 200){
				sleep(3);
				return $this->google($name);	
			}
			$results = $json['responseData']['results'];
			
			if(is_array($results)){
				foreach($results as $key=>$val){
					$unescapedUrl = $val['unescapedUrl'];
					if(strpos($unescapedUrl, 'alibaba') !== false){
						continue;
					}
					$urlInfo = parse_url($unescapedUrl);
					if($urlInfo['path'] == '/'){
						return strtolower($urlInfo['host']);
						break;
					}
				}
			}
		}
		curl_close($ch);
		return '';
    }
    
    
    public function testAction(){
    	$name = '广州市金宫酒店家具有限公司';
    	$url = 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q='.urlencode($name);  
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_USERAGENT, "Internet Explorer" );
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, 'http://www.mysite.com/index.html');
		$body = curl_exec($ch);
		if(curl_errno($ch)){
			throw_exception(curl_error($ch));
		}
		curl_close($ch);  
		$json = json_decode($body,true);
    	
    	dump($json);
    }
    
    
}
?>