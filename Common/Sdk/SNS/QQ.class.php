<?php

/**
 * 
 * QQ开放平台API
 * @author maojianlw@139.com
 * @since 2013-06-20
 * @link http://www.eaglephp.com
 *
 */

class QQ
{
	
	private $appid = null;
	
	private $appkey = null;
	
	private $access_token = null;
	
	private $open_id = null;
	
	private $APIMap = array();
	
	private $keysArr = array();

	
	
	const VERSION = '2.0';
	
    const AUTH_CODE_URL = 'https://graph.qq.com/oauth2.0/authorize';
    
    const ACCESS_TOKEN_URL = 'https://graph.qq.com/oauth2.0/token';
    
    const OPENID_URL = 'https://graph.qq.com/oauth2.0/me';
	
	const SCOPE = 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr';
	
	
	
	public function __construct($appid, $appkey, $access_token = null, $open_id = null)
	{
		$this->appid = $appid;
		$this->appkey = $appkey;
		$this->access_token = $access_token;
		$this->open_id = $open_id;
	}
	
	
	/**
	 * 登录
	 */
	public function login($url)
	{
        $state = md5(uniqid(rand(), true)); // 生成唯一随机串防CSRF攻击
        Session::set('state', $state);
        $keysArr = array(
            'response_type' => 'code',
            'client_id' => $this->appid,
            'redirect_uri' => $url,
            'scope' => self::SCOPE,
        	'state' => $state
        );
		header('Location:'.self::AUTH_CODE_URL.'?'.http_build_query($keysArr));
	}
	
	
	/**
	 * 
	 * 回调函数返回access_token和open_id
	 * 
	 */
	public function getAccessToken($type = '', $keys = '')
	{
		$state = Session::get('state');
		
		// 验证state防止CSRF攻击
        if(HttpRequest::getGet('state') != $state) throw_exception('The state does not match. You may be a victim of CSRF.');
        Session::set('state', null);
        
        $keysArr = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->appid,
            'redirect_uri' => $keys['redirect_uri'],
            'client_secret' => $this->appkey,
            'code' => $keys['code']
        );

        $response = curlRequest(self::ACCESS_TOKEN_URL.'?'.http_build_query($keysArr));
        if(strpos($response, 'callback') !== false)
        {
            $lpos = strpos($response, '(');
            $rpos = strrpos($response, ')');
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);
            if(isset($msg->error)) throw_exception($msg->error.' '.$msg->error_description);
        }
		
        $params = array();
        parse_str($response, $params);
		Session::set('access_token', $params['access_token']);
        
		// 根据token请求获取openid
        $response = curlRequest(self::OPENID_URL.'?access_token='.$params['access_token']);

        if(strpos($response, 'callback') !== false){

            $lpos = strpos($response, '(');
            $rpos = strrpos($response, ')');
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response);
        if(isset($user->error)) throw_exception($msg->error.' '.$msg->error_description);
		Session::set('open_id', $user->openid);
		$params['openid'] = $user->openid;
		unset($params['refresh_token']);
		return $params;
	}
	
	
	public function getUserInfo($uid='')
	{
		$data = $this->get_user_info($uid);
		return array('name'=>$data['nickname'], 'gender'=>$data['gender'], 'avatar'=>$data['figureurl_1']);
	}
	
	/**
	 * 
	 * 初始化数据
	 * 
	 */
	protected function _initAPI()
	{
		$this->keysArr = array(
                'oauth_consumer_key' => (int)$this->appid,
                'access_token' => Session::get('access_token'),
                'openid' => Session::get('open_id')
            );
		
		/**
		 * 
		 * 初始化APIMap
		 * 加#表示非必须，无则不传入url(url中不会出现该参数)， 'key' => 'val' 表示key如果没有定义则使用默认值val
		 * 规则 array( baseUrl, argListArr, method)
		 * 
		 * @var array
		 */
		
        $this->APIMap = array(
            /*                       qzone                    */
            'add_blog' => array(
                'https://graph.qq.com/blog/add_one_blog',
                array('title', 'format' => 'json', 'content' => null),
                'POST'
            ),
            'add_topic' => array(
                'https://graph.qq.com/shuoshuo/add_topic',
                array('richtype','richval','con','#lbs_nm','#lbs_x','#lbs_y','format' => 'json', '#third_source'),
                'POST'
            ),
            'get_user_info' => array(
                'https://graph.qq.com/user/get_user_info',
                array('format' => 'json'),
                'GET'
            ),
            'add_one_blog' => array(
                'https://graph.qq.com/blog/add_one_blog',
                array('title', 'content', 'format' => 'json'),
                'GET'
            ),
            'add_album' => array(
                'https://graph.qq.com/photo/add_album',
                array('albumname', '#albumdesc', '#priv', 'format' => 'json'),
                'POST'
            ),
            'upload_pic' => array(
                'https://graph.qq.com/photo/upload_pic',
                array('picture', '#photodesc', '#title', '#albumid', '#mobile', '#x', '#y', '#needfeed', '#successnum', '#picnum', 'format' => 'json'),
                'POST'
            ),
            'list_album' => array(
                'https://graph.qq.com/photo/list_album',
                array('format' => 'json')
            ),
            'add_share' => array(
                'https://graph.qq.com/share/add_share',
                array('title', 'url', '#comment','#summary','#images','format' => 'json','#type','#playurl','#nswb','site','fromurl'),
                'POST'
            ),
            'check_page_fans' => array(
                'https://graph.qq.com/user/check_page_fans',
                array('page_id' => '314416946','format' => 'json')
            ),
            /*                    wblog                             */

            'add_t' => array(
                'https://graph.qq.com/t/add_t',
                array('format' => 'json', 'content','#clientip','#longitude','#compatibleflag'),
                'POST'
            ),
            'add_pic_t' => array(
                'https://graph.qq.com/t/add_pic_t',
                array('content', 'pic', 'format' => 'json', '#clientip', '#longitude', '#latitude', '#syncflag', '#compatiblefalg'),
                'POST'
            ),
            'del_t' => array(
                'https://graph.qq.com/t/del_t',
                array('id', 'format' => 'json'),
                'POST'
            ),
            'get_repost_list' => array(
                'https://graph.qq.com/t/get_repost_list',
                array('flag', 'rootid', 'pageflag', 'pagetime', 'reqnum', 'twitterid', 'format' => 'json')
            ),
            'get_info' => array(
                'https://graph.qq.com/user/get_info',
                array('format' => 'json')
            ),
            'get_other_info' => array(
                'https://graph.qq.com/user/get_other_info',
                array('format' => 'json', '#name', 'fopenid')
            ),
            'get_fanslist' => array(
                'https://graph.qq.com/relation/get_fanslist',
                array('format' => 'json', 'reqnum', 'startindex', '#mode', '#install', '#sex')
            ),
            'get_idollist' => array(
                'https://graph.qq.com/relation/get_idollist',
                array('format' => 'json', 'reqnum', 'startindex', '#mode', '#install')
            ),
            'add_idol' => array(
                'https://graph.qq.com/relation/add_idol',
                array('format' => 'json', '#name-1', '#fopenids-1'),
                'POST'
            ),
            'del_idol' => array(
                'https://graph.qq.com/relation/del_idol',
                array('format' => 'json', '#name-1', '#fopenid-1'),
                'POST'
            ),
            /*                           pay                          */

            'get_tenpay_addr' => array(
                'https://graph.qq.com/cft_info/get_tenpay_addr',
                array('ver' => 1,'limit' => 5,'offset' => 0,'format' => 'json')
            )
        );
	}
	
	
	protected function _applyAPI($baseUrl, $argsList, $method)
	{
		$params = HttpRequest::getPost();
		$optionArgList = '';
		$keysArr = $this->keysArr;
		$pre = '#';
		foreach ($argsList as $k=>$v)
		{
			if(!is_string($k))
			{
				$k = $v;
				if(strpos($v, $pre) === 0)
				{
					$v = $pre;
					$k = substr($k, 1);
					if(preg_match('/-(\d$)/', $k, $matchs))
					{
						$k = str_replace($matchs[0], '', $k);
						$optionArgList[$matchs[1]][] = $k;
					}
				}
				else $v = null;
			}
			if(!isset($params[$k]) || $params[$k] === '')
			{
				if($v == $pre) continue;
				elseif($v) $params[$k] = $v;
				else
				{
					if(isset($_FILES[$k]) && $_FILES[$k]['name']!='')
					{
						$uploadDir = getUploadAddr().'QQ'.__DS__;
			    		mk_dir($uploadDir);
			    		$uploadObj = new Upload();
			    		$fileInfo = $uploadObj->uploadOne($_FILES[$k], $uploadDir);
	    				$img = $uploadDir.$fileInfo[0]['savename'];
			    		$params[$k] = "@{$img}";
					}
					else throw_exception("param {$k} not pass value.");
				}
			}
			$keysArr[$k] = $params[$k]; 
		}
		
		// 检查选填参数必填一的情形
		$i = 0;
		if(isset($optionArgList[1]))
		{
			foreach ($optionArgList[1] as $k=>$v) if(array_key_exists($v, $keysArr)) $i++;
			if(!$i) throw_exception('QQ_api_param_error,['.implode(',', $optionArgList[1]).'] must hava one value.');
		}
		
		$baseUrl .= ($method == 'GET') ? '?'.http_build_query($keysArr) : '';
		$response = curlRequest($baseUrl, $keysArr, $method);
		return json_decode($response, true);
	}
	
	
	public function __call($name, $args)
	{
		$this->_initAPI();
		if(!array_key_exists($name, $this->APIMap)) throw_exception("QQ_api_{$name} not exists.");
		
		//从APIMap获取api相应参数
        $baseUrl = $this->APIMap[$name][0];
        $argsList = $this->APIMap[$name][1];
        $method = isset($this->APIMap[$name][2]) ? $this->APIMap[$name][2] : 'GET';
        
		$responseArr = $this->_applyAPI($baseUrl, $argsList, $method);
		
		//检查返回ret判断api是否成功调用
        if($responseArr['ret'] == 0) return $responseArr;
        else throw_exception('QQ_API_'.$name.' [ret:'.$responseArr['ret'].'] '.$responseArr['msg']);
	}
	
	
}
