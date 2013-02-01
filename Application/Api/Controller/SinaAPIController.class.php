<?php

/**
 * 新浪API管理
 * @author maojianlw@139.com
 * @since 2011-12-27
 */

class SinaAPIController extends ApiCommonController{

	private $curModel = null;
	const COOKIE_FILE = 'D:/EaglePHPCookie.log';
	
	/**
	 * 初始化
	 */
    public function __construct() {
    	set_time_limit(0);
    	$this->curModel = model('user');	
    }
    
	
	/**
	 * 登录
	 */
	private function login($username,$password){
		if($username && $password){
			$preLoginData = curlRequest('http://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su='.base64_encode($username).'&client=ssologin.js(v1.3.16)','','post',self::COOKIE_FILE);
			preg_match('/sinaSSOController.preloginCallBack\((.*)\)/',$preLoginData,$preArr);
			$jsonArr = json_decode($preArr[1],true);
			if(is_array($jsonArr)){
				$postArr = array(
		            'entry'          => 'weibo',
		            'gateway'        => 1,
		            'from'           => '',
		            'savestate'      => 7,
		            'useticket'      => 1,
		            'ssosimplelogin' => 1,
		            'su'             => base64_encode(urlencode($username)),
		            'service'        => 'miniblog',
		            'servertime'     => $jsonArr['servertime'],
		            'nonce'          => $jsonArr['nonce'],
		            'pwencode'       => 'wsse',
		            'sp'             => sha1(sha1(sha1($password)).$jsonArr['servertime'].$jsonArr['nonce']),
		            'encoding'       => 'UTF-8',
		            'url'            => 'http://weibo.com/ajaxlogin.php?framelogin=1&callback=parent.sinaSSOController.feedBackUrlCallBack',
		            'returntype'     => 'META'
        		);
        		$loginData = curlRequest('http://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.3.16)',$postArr,'post',self::COOKIE_FILE);
				if($loginData){
					$matchs = array();
					preg_match('/replace\(\'(.*?)\'\)/',$loginData,$matchs);
					$loginResult = curlRequest($matchs[1],'','post',self::COOKIE_FILE);
					$loginResultArr = array();
					preg_match('/feedBackUrlCallBack\((.*?)\)/',$loginResult,$loginResultArr);
					//$userInfo = json_decode($loginResultArr[1],true);
					//Log::info(var_export($loginResultArr[1]));
				}else{
					throw_exception('Login sina fail.');
				}
			}else{
				throw_exception($preLoginData);
			}
		}else{
			throw_exception('Param error.');
		}
	}
	
	
	/**
	 * 分析新浪微博页面内容
	 */
	private function fetch($content){
		$content = str_replace(chr(10),'',$content);
		preg_match("/<div class=\"interPer_tal_ls clearfix\" id=\"pl_search_searchResult\">(.*)<!-- 分页 -->/", $content, $data);
		$xml = str_replace('<!-- 分页 -->','',$data[0]);
		$xml = preg_replace("/class=\"female\" \/>(.*?)<\/p>/","class=\"female\" /><span>$1</span></p>",$xml);
		$xml = preg_replace("/class=\"male\"  \/>(.*?)<\/p>/","class=\"male\"  /><span>$1</span></p>",$xml);
		$xmlArr = XML_unserialize($xml);
		$xmlArr = $xmlArr['div']['dl'];
		
		foreach($xmlArr as $key=>$val){
			
			if(is_numeric($key)){
				$fensi  = preg_replace("/粉丝(.*)人/","$1",$val['dd']['p'][3]);
				if($fensi >= 800){
					$nickname = $val['dt']['a']['0 attr']['title'];
					$photo = $val['dt']['a'][0]['img attr']['src'];
					$addr = $val['dd']['p'][1]['span'];
					$addrArr = explode(',',$addr);
					$province = $addrArr[0];
					$city = $addrArr[1];
					$account = $val['dt']['a']['0 attr']['href'];
					$accountArr = explode('/',$account);
					$account = $accountArr[count($accountArr)-1];
					$tag = $val['dd']['p'][2];
					$inserData = array('nickname'=>$nickname, 
									   'account'=>$account, 
                                       'tag'=>$tag, 
                                       'photo'=>$photo, 
                                       'province'=>$province,
                                       'city'=>$city,  
                                       'fans'=>$fensi, 
                                       'createtime'=>date('Y-m-d H:i:s')
                                 );
                    if(!($this->curModel->where("account='{$account}'")->find())){
                    	$this->curModel->add($inserData);
                    }
				}else{
					exit('少于800');
				}
			}
		}
		
	}
	
	
	/**
	 * 关注
	 */
	public function follow($uid){
		$postData['_t'] = 0;
		$postData['f'] = 1;
		$postData['location'] = 'profile';
		$postData['refer_flag'] = '';
		$postData['refer_sort'] = 'profile';
		$postData['uid'] = $uid;
		
		//'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		$headers = array(
			'Host: weibo.com',
			/*
			'User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:8.0) Gecko/20100101 Firefox/8.0',
			'Accept-Language: zh-cn,zh;q=0.5',
			'Accept-Encoding: gzip, deflate',
			'Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7',
			'Connection: keep-alive',
			'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
			*/
			'X-Requested-With: XMLHttpRequest',
			'Referer: http://weibo.com'
		);
		
		$content = curlRequest('http://weibo.com/aj/f/followed',$postData,'post',self::COOKIE_FILE,$headers);
		$jsonArr = json_decode($content,true);
		if($jsonArr['code'] != 100000){
			throw_exception($jsonArr['msg']);
		}
	}
	
	
    
    /**
     * 抓取新浪微博页面内容
     */
    public function swoopAction(){
    	$username = $this->request('username');
		$password = $this->request('password');
	
		$this->login($username,$password);
	
		$user = $this->curModel->field('photo')->limit('100,100')->order('fans DESC')->select();
		foreach($user as $u){
			preg_match('/.sinaimg.cn\/(.*?)\//',$u['photo'],$matchs);
			$uid = $matchs[1];
			$this->follow($uid);
		}
		exit;
		
		//$content = file_get_contents('bb.htm');
		//echo $content;
		//echo urldecode('prov%3D44%26city%3D1%26mcPre%3D43_1%26level%3D2%26location%3D%25E5%25B9%25BF%25E5%25B7%259E%26');
		//&prov=44&city=3
		//广州1.东莞19.深圳3.佛山6 
		curlRequest('http://club.weibo.com/list?prov=44&city=19','','post',self::COOKIE_FILE);
		for($i=100;$i<=200;$i++){
			$content = curlRequest('http://club.weibo.com/list?sex=3&op=fans&page='.$i,'','post',self::COOKIE_FILE);
			//echo $content;
			$this->fetch($content);
		}
    }
    
}
?>