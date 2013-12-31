<?php
/**
 * 微信公众平台API
 * 
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 */
class WeixinChat
{
	
	private $token;
	
	private $appid;
	
	private $appsecret;
	
	private $access_token;
	
	// 接收的数据
	private $_receive = array();
	
	private $_reply = '';
	
	// 接口错误码
	private $errCode = '';
	
	// 接口错误信息
	private $errMsg = '';
	
	// 微信oauth登陆获取code
	const CONNECT_OAUTH_AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
	
	// 微信oauth登陆通过code换取网页授权access_token
	const SNS_OAUTH_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
	
	// 微信oauth登陆刷新access_token（如果需要）
	const SNS_OAUTH_REFRESH_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?';
	
	// 通过ticket换取二维码
	const SHOW_QRCODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?';
	
	// 微信oauth登陆拉取用户信息(需scope为 snsapi_userinfo)
	const SNS_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo?';
	
	// 请求api前缀
	const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
	
	// 自定义菜单创建
	const MENU_CREATE_URL = '/menu/create?';
	
	// 自定义菜单查询
	const MENU_GET_URL = '/menu/get?';
	
	// 自定义菜单删除
	const MENU_DELETE_URL = '/menu/delete?';
	
	// 获取 access_token
	const AUTH_URL = '/token?grant_type=client_credential&';

	// 获取用户基本信息
	const USER_INFO_URL = '/user/info?';
	
	// 获取关注者列表
	const USER_GET_URL = '/user/get?';
	
	// 查询分组
	const GROUPS_GET_URL = '/groups/get?'; 
	
	// 创建分组
	const GROUPS_CREATE_URL = '/groups/create?';
	
	// 修改分组名
	const GROUPS_UPDATE_URL = '/groups/update?';
	
	// 移动用户分组
	const GROUPS_MEMBERS_UPDATE_URL = '/groups/members/update?';
	
	// 发送客服消息
	const MESSAGE_CUSTOM_SEND_URL = '/message/custom/send?';
	
	// 创建二维码ticket
	const QRCODE_CREATE_URL = '/qrcode/create?';
	
	
	
	/**
	 * 初始化配置数据
	 * @param array $options
	 */
	public function __construct($options)
	{
		$this->token = isset($options['token']) ? $options['token'] : '';
		$this->appid = isset($options['appid']) ? $options['appid'] : '';
		$this->appsecret = isset($options['appsecret']) ? $options['appsecret'] : '';
	}
	
	
	/**
	 * 获取发来的消息
	 * 当普通微信用户向公众账号发消息时，微信服务器将POST消息的XML数据包到开发者填写的URL上。
	 */
	public function getRev()
	{
		$postStr = file_get_contents('php://input');
		if($postStr)
		{
			$this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			//Log::info(var_export($this->_receive, true));
		}
		return $this;
	}
	
	
	/**
	 * 获取微信服务器发来的消息
	 */
	public function getRevData()
	{
		return $this->_receive;
	}
	
	
	/**
	 * 获取接收者
	 */
	public function getRevTo()
	{
		return isset($this->_receive['ToUserName']) ? $this->_receive['ToUserName'] : false;
	}
	
	
	/**
	 * 获取消息发送者（一个OpenID）
	 */
	public function getRevFrom()
	{
		return isset($this->_receive['FromUserName']) ? $this->_receive['FromUserName'] : false;
	}
	
	
	/**
	 * 获取接收消息创建时间 （整型）
	 */
	public function getRevCTime()
	{
		return isset($this->_receive['CreateTime']) ? $this->_receive['CreateTime'] : false;
	}
	
	
	/**
	 * 获取接收消息类型（text、image、voice、video、location、link、event）
	 */
	public function getRevType()
	{
		return isset($this->_receive['MsgType']) ? $this->_receive['MsgType'] : false;
	}
	
	
	/**
	 * 获取接收消息编号
	 */
	public function getRevId()
	{
		return isset($this->_receive['MsgId']) ? $this->_receive['MsgId'] : false;
	}
	
	
	/**
	 * 获取接收消息文本
	 * 通过语音识别接口，用户发送的语音，将会同时给出语音识别出的文本内容。（需申请服务号的高级接口权限）
	 */
	public function getRevText()
	{
		if(isset($this->_receive['Content'])) return trim($this->_receive['Content']);
		elseif(isset($this->_receive['Recognition'])) return trim($this->_receive['Recognition']);
		else return false;
	}
	
	
	/**
	 * 获取接收图片消息
	 */
	public function getRevImage()
	{
		if(isset($this->_receive['PicUrl'])){
			return array(
				   	'picUrl' => $this->_receive['PicUrl'],  //图片链接
					'mediaId' => $this->_receive['MediaId'] //图片消息媒体id，可以调用多媒体文件下载接口拉取数据。
				   );
		}
		return false;
	}
	
	
	/**
	 * 获取接收语音消息
	 */
	public function getRevVoice()
	{
		if(isset($this->_receive['MediaId'])){
			return array(
				   	'mediaId' => $this->_receive['MediaId'],  //语音消息媒体id，可以调用多媒体文件下载接口拉取数据。
					'format' => $this->_receive['Format'] //语音格式，如amr，speex等
				   );
		}
		return false;
	}
	
	
	/**
	 * 获取接收视频消息
	 */
	public function getRevVideo()
	{
		if(isset($this->_receive['MediaId'])){
			return array(
				   	'mediaId' => $this->_receive['MediaId'],  		   //视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
					'thumbMediaId' => $this->_receive['ThumbMediaId']  //视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
				   );
		}
		return false;
	}	
	
	
	/**
	 * 获取用户地理位置
	 */
	public function getRevLocation()
	{
		if(isset($this->_receive['Location_X'])){
			return array(
				   	'locationX' => $this->_receive['Location_X'],  //地理位置维度
					'locationY' => $this->_receive['Location_Y'],  //地理位置经度
					'scale' => $this->_receive['Scale'], //地图缩放大小
					'label' => $this->_receive['Label'] //地理位置信息
				   );
		}
		//开通了上报地理位置接口的公众号，用户在关注后进入公众号会话时，会弹框让用户确认是否允许公众号使用其地理位置。
		//弹框只在关注后出现一次，用户以后可以在公众号详情页面进行操作。
		elseif(isset($this->_receive['Latitude'])) 
		{
			return array(
				   	'latitude' => $this->_receive['Latitude'],  //地理位置纬度
					'longitude' => $this->_receive['Longitude'], //地理位置经度
			 		'precision' => $this->_receive['Precision'] // 地理位置精度
				   );
		}
		return false;
	}
	
	
	/**
	 * 获取接收链接消息
	 */
	public function getRevLink()
	{
		if(isset($this->_receive['Title'])){
			return array(
				   	'title' => $this->_receive['Title'],  //消息标题
					'description' => $this->_receive['Description'],  //消息描述
					'url' => $this->_receive['Url'] //消息链接
				   );
		}
		return false;
	}
	
	
	/**
	 * 获取接收事件类型
	 * 事件类型如：subscribe(订阅)、unsubscribe(取消订阅)、click
	 */
	public function getRevEvent()
	{
		if(isset($this->_receive['Event']))
		{
			return array(
					'event' => strtolower($this->_receive['Event']), 
					'key'=> isset($this->_receive['EventKey']) ? $this->_receive['EventKey'] : ''
				   );
		}
		return false;
	}
	
	
	/**
	 * 设置回复文本消息
	 * @param string $content
	 * @param string $openid
	 */
	public function text($content='')
	{
		$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
					</xml>";
		
		$this->_reply = sprintf($textTpl, 
									$this->getRevFrom(),
									$this->getRevTo(), 
									Date::getTimeStamp(), 
									'text', 
									$content
								);
		return $this;
	}
	
	
	/**
	 * 设置回复音乐信息
	 * @param string $title
	 * @param string $desc
	 * @param string $musicurl
	 * @param string $hgmusicurl
	 */
	public function music($title, $desc, $musicurl, $hgmusicurl='')
	{
		$textTpl = '<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Music>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							<MusicUrl><![CDATA[%s]]></MusicUrl>
							<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
						</Music>
					</xml>';
		//<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
		
		$this->_reply = sprintf($textTpl, 
									$this->getRevFrom(),
									$this->getRevTo(), 
									Date::getTimeStamp(), 
									'music', 
									$title,
									$desc,
									$musicurl,
									$hgmusicurl
								);
		return $this;
	}
	
	
	/**
	 * 回复图文消息
	 * @param array
	 */
	public function news($data)
	{
		$count = count($data);
		$subText = '';
		if($count > 0)
		{
			foreach($data as $v)
			{
				$tmpText = '<item>
						<Title><![CDATA[%s]]></Title> 
						<Description><![CDATA[%s]]></Description>
						<PicUrl><![CDATA[%s]]></PicUrl>
						<Url><![CDATA[%s]]></Url>
						</item>';
				
				$subText .= sprintf(
								$tmpText, $v['title'], 
								isset($v['description']) ? $v['description'] : '', 
								isset($v['picUrl']) ? $v['picUrl'] : '', 
								isset($v['url']) ? $v['url'] : ''
							);
			}
		}
		
		$textTpl = '<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime><![CDATA[%s]]></CreateTime>
						<MsgType><![CDATA[news]]></MsgType>
						<ArticleCount><![CDATA[%d]]></ArticleCount>
						<Articles>%s</Articles>
					</xml>';
		
		$this->_reply = sprintf(
							$textTpl, 
							$this->getRevFrom(), 
							$this->getRevTo(), 
							Date::getTimeStamp(), 
							$count, 
							$subText
						);
		return $this;
	}
	
	
	/**
	 * 回复消息
	 * @param array $msg
	 * @param bool $return
	 */
	public function reply()
	{
		header('Content-Type:text/xml');
		echo $this->_reply;
		exit;
	}
	
	
	/**
	 * 自定义菜单创建
	 * @param array 菜单数据
	 */
	public function createMenu($data)
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$result = curlRequest(self::API_URL_PREFIX.self::MENU_CREATE_URL.'access_token='.$this->access_token, $this->jsonEncode($data), 'post');
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return true;
		}
		
		return false;
	}
	
	
	/**
	 * 自定义菜单查询
	 */
	public function getMenu()
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$result = curlRequest(self::API_URL_PREFIX.self::MENU_GET_URL.'access_token='.$this->access_token);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 自定义菜单删除
	 */
	public function deleteMenu()
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$result = curlRequest(self::API_URL_PREFIX.self::MENU_DELETE_URL.'access_token='.$this->access_token);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return true;
		}
		
		return false;
	}
	
	
	/**
	 * 获取用户基本信息
	 * @param string $openid 普通用户的标识，对当前公众号唯一
	 */
	public function getUserInfo($openid)
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$result = curlRequest(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 获取关注者列表
	 * @param string $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
	 */
	public function getUserList($next_openid='')
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$result = curlRequest(self::API_URL_PREFIX.self::USER_GET_URL.'access_token='.$this->access_token.'&next_openid='.$next_openid);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 查询分组
	 */
	public function getGroup()
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$result = curlRequest(self::API_URL_PREFIX.self::GROUPS_GET_URL.'access_token='.$this->access_token);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 创建分组
	 * @param string $name 分组名字（30个字符以内）
	 */
	public function createGroup($name)
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		$data = array('group' => array('name' => $name));
		$result = curlRequest(self::API_URL_PREFIX.self::GROUPS_CREATE_URL.'access_token='.$this->access_token, $this->jsonEncode($data), 'post');
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return true;
		}
		
		return false;
	}
	
	
	/**
	 * 修改分组名
	 * @param int $id 分组id，由微信分配
	 * @param string $name 分组名字（30个字符以内）
	 */
	public function updateGroup($id, $name)
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$data = array('group' => array('id' => $id, 'name' => $name));
		$result = curlRequest(self::API_URL_PREFIX.self::GROUPS_UPDATE_URL.'access_token='.$this->access_token, $this->jsonEncode($data), 'post');
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return true;
		}
		
		return false;
	}
	
	
	/**
	 * 移动用户分组
	 * 
	 * @param string $openid 用户唯一标识符
	 * @param int $to_groupid 分组id
	 */
	public function updateGroupMembers($openid, $to_groupid)
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$data = array('openid' => $openid, 'to_groupid' => $to_groupid);
		$result = curlRequest(self::API_URL_PREFIX.self::GROUPS_MEMBERS_UPDATE_URL.'access_token='.$this->access_token, $this->jsonEncode($data), 'post');
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return true;
		}
		
		return false;
	}
	
	
	/**
	 * 发送客服消息
	 * 当用户主动发消息给公众号的时候（包括发送信息、点击自定义菜单clike事件、订阅事件、扫描二维码事件、支付成功事件、用户维权），
	 * 微信将会把消息数据推送给开发者，开发者在一段时间内（目前为24小时）可以调用客服消息接口，通过POST一个JSON数据包来发送消息给普通用户，在24小时内不限制发送次数。
	 * 此接口主要用于客服等有人工消息处理环节的功能，方便开发者为用户提供更加优质的服务。
	 * 
	 * @param string $touser 普通用户openid
	 */
	public function sendCustomMessage($touser, $data, $msgType = 'text')
	{
		$arr = array();
		$arr['touser'] = $touser;
		$arr['msgtype'] = $msgType;
		switch ($msgType)
		{
			case 'text': // 发送文本消息
				$arr['text']['content'] = $data; 
				break;
			
			case 'image': // 发送图片消息
				$arr['image']['media_id'] = $data;
				break;
				
			case 'voice': // 发送语音消息
				$arr['voice']['media_id'] = $data;
				break;
				
			case 'video': // 发送视频消息
				$arr['video']['media_id'] = $data['media_id']; // 发送的视频的媒体ID
				$arr['video']['thumb_media_id'] = $data['thumb_media_id']; // 视频缩略图的媒体ID
				break;
			
			case 'music': // 发送音乐消息
				$arr['music']['title'] = $data['title'];// 音乐标题
				$arr['music']['description'] = $data['description'];// 音乐描述
				$arr['music']['musicurl'] = $data['musicurl'];// 音乐链接
				$arr['music']['hqmusicurl'] = $data['hqmusicurl'];// 高品质音乐链接，wifi环境优先使用该链接播放音乐
				$arr['music']['thumb_media_id'] = $data['title'];// 缩略图的媒体ID
				break;
			
			case 'news': // 发送图文消息
				$arr['news']['articles'] = $data; // title、description、url、picurl
				break;
		} 
		
		if(!$this->access_token && !$this->checkAuth()) return false;
	
		$result = curlRequest(self::API_URL_PREFIX.self::MESSAGE_CUSTOM_SEND_URL.'access_token='.$this->access_token, $this->jsonEncode($arr), 'post');
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return true;
		}
		
		return false;
	}
	
	
	
	/**
	 * 获取access_token
	 */
	public function checkAuth()
	{
		
		// 从缓存中获取access_token
		$cache_flag = 'weixin_access_token';
		$access_token = cache($cache_flag);
		if($access_token) 
		{
			$this->access_token = $access_token;
			return true;
		}
		
		// 请求微信服务器获取access_token 
		$result = curlRequest(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$this->appid.'&secret='.$this->appsecret);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0))
			{
				$this->error($jsonArr);
			}
			else
			{
				$this->access_token = $jsonArr['access_token'];
				$expire = isset($jsonArr['expires_in']) ? intval($jsonArr['expires_in'])-100 : 3600;
				// 将access_token保存到缓存中
				cache($cache_flag, $this->access_token, $expire, Cache::FILE); 
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * 微信oauth登陆->第一步：用户同意授权，获取code
	 * 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
	 * snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
	 * 直接在微信打开链接，可以不填此参数。做页面302重定向时候，必须带此参数
	 * 
	 * @param string $redirect_uri 授权后重定向的回调链接地址
	 * @param string $scope 应用授权作用域 0为snsapi_base，1为snsapi_userinfo
	 * @param string $state 重定向后会带上state参数，开发者可以填写任意参数值
	 */
	public function redirectGetOauthCode($redirect_uri, $scope=0, $state='')
	{
		$scope = ($scope == 0) ? 'snsapi_base' : 'snsapi_userinfo';
		$url = self::CONNECT_OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
		redirect($url);
	}
	
	
	/**
	 * 微信oauth登陆->第二步：通过code换取网页授权access_token
	 * 
	 * @param string $code
	 */
	public function getSnsAccessToken($code)
	{
		$result = curlRequest(self::SNS_OAUTH_ACCESS_TOKEN_URL.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 微信oauth登陆->第三步：刷新access_token（如果需要）
	 * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，
	 * refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。
	 * 
	 * @param string $refresh_token 填写通过access_token获取到的refresh_token参数
	 */
	public function refershToken($refresh_token)
	{
		$result = curlRequest(self::SNS_OAUTH_REFRESH_TOKEN_URL.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 微信oauth登陆->第四步：拉取用户信息(需scope为 snsapi_userinfo)
	 * 如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过access_token和openid拉取用户信息了。
	 * 
	 * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
	 * @param string $openid 用户的唯一标识
	 */
	public function getSnsUserInfo($access_token, $openid)
	{
		$result = curlRequest(self::SNS_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 创建二维码ticket
	 * 每次创建二维码ticket需要提供一个开发者自行设定的参数（scene_id），分别介绍临时二维码和永久二维码的创建二维码ticket过程。
	 * 
	 * @param int $scene_id 场景值ID，临时二维码时为32位整型，永久二维码时最大值为1000
	 * @param int $type 二维码类型，0为临时,1为永久
	 * @param int $expire 该二维码有效时间，以秒为单位。 最大不超过1800。
	 */
	public function createQrcode($scene_id, $type=0, $expire=1800)
	{
		if(!$this->access_token && !$this->checkAuth()) return false;
		
		$data = array();
		$data['action_info'] = array('scene' => array('scene_id' => $scene_id));
		$data['action_name'] = ($type == 0 ? 'QR_SCENE' : 'QR_LIMIT_SCENE');
		if($type == 0) $data['expire_seconds'] = $expire;
		
		$result = curlRequest(self::API_URL_PREFIX.self::QRCODE_CREATE_URL.'access_token='.$this->access_token, $this->jsonEncode($data), 'post');
		if($result)
		{
			$jsonArr = json_decode($result, true);
			if(!$jsonArr || (isset($jsonArr['errcode']) && $jsonArr['errcode'] > 0)) $this->error($jsonArr);
			else return $jsonArr;
		}
		
		return false;
	}
	
	
	/**
	 * 通过ticket换取二维码
	 * 获取二维码ticket后，开发者可用ticket换取二维码图片。请注意，本接口无须登录态即可调用。
	 * 提醒：TICKET记得进行UrlEncode
	 * ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。
	 * 错误情况下（如ticket非法）返回HTTP错误码404。
	 * 
	 * @param string $ticket
	 */
	public function getQrcodeUrl($ticket)
	{
		return self::SHOW_QRCODE_URL.'ticket='.urlencode($ticket);
	}
	
	
	/**
	 * 记录接口产生的错误日志
	 */
	public function error($data)
	{
		$this->errCode = $data['errcode'];
		$this->errMsg = $data['errmsg'];
		Log::info('WEIXIN API errcode:['.$this->errCode.'] errmsg:['.$this->errMsg.']');
	}
	
	
	/**
	 * 将数组中的中文转换成json数据
	 * @param array $arr
	 */
	public function jsonEncode($arr) {
    	$parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
               if ($i != $keys [$i]) { //A key fails at position check.
                  $is_list = false; //It is an associative array.
                  break;
               }
            }
        }
                foreach ( $arr as $key => $value ) {
                        if (is_array ( $value )) { //Custom handling for arrays
                                if ($is_list)
                                        $parts [] = $this->jsonEncode ( $value ); /* :RECURSION: */
                                else
                                        $parts [] = '"' . $key . '":' . $this->jsonEncode ( $value ); /* :RECURSION: */
                        } else {
                                $str = '';
                                if (! $is_list)
                                        $str = '"' . $key . '":';
                                //Custom handling for multiple data types
                                if (is_numeric ( $value ) && $value<2000000000)
                                        $str .= $value; //Numbers
                                elseif ($value === false)
                                $str .= 'false'; //The booleans
                                elseif ($value === true)
                                $str .= 'true';
                                else
                                        $str .= '"' . addslashes ( $value ) . '"'; //All other things
                                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                                $parts [] = $str;
                        }
                }
                $json = implode ( ',', $parts );
                if ($is_list)
                        return '[' . $json . ']'; //Return numerical JSON
                return '{' . $json . '}'; //Return associative JSON
        }

        
	/**
	 * 检验签名
	 */
	public function checkSignature()
	{
        $signature = HttpRequest::getGet('signature');
        $timestamp = HttpRequest::getGet('timestamp');
        $nonce = HttpRequest::getGet('nonce');
        		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		
		return ($tmpStr == $signature ? true : false);
	}
	
	
	/**
	 * 验证token是否有效
	 */
	public function valid()
	{
		if($this->checkSignature()) exit(HttpRequest::getGet('echostr'));
	}
	
}