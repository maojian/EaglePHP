<?php
class UserController extends Controller
{

    private $userModel = null;
    
    public function __construct()
    {
        $this->userModel = model('user');
    }
    
    
    /**
     * 注册
     */
    public function registerAction()
    {
        if($this->isPost())
        {
            $username = $this->post('username');
            $nickname = $this->post('nickname');
            $password = $this->post('password');
            $confirm = $this->post('confirm');
            $email = $this->post('email');
            $captcha = $this->post('captcha');
            $message = '';
            
            if(Session::get('verify') != md5($captcha)){
                $message = '验证码错误.';
            }
            elseif($this->userModel->field('uid')->where("username='{$username}'")->find())
            {
                $message = '用户名已存在.';
            }
        	elseif(!$nickname)
            {
                $message = '昵称不能为空.';
            }
            elseif(!$password)
            {
                $message = '密码不能为空.';
            }
            elseif($password != $confirm)
            {
                $message = '输入的两次密码不一致.';
            }
            elseif(!Validator::isEmail($email))
            {
                $message = '邮箱错误.';
            }
            elseif($this->userModel->field('uid')->where("email='{$email}'")->find())
            {
                $message = '用户名已存在.';
            }
            if(!$message)
            {
                $data['username'] = $username;
                $data['nickname'] = $nickname;
                $data['password'] = md5($password);
                $data['email'] = $email;
                $data['reg_time'] = Date::format();
                $data['reg_ip'] = HttpRequest::getClientIP();
                $data['status'] = 'N';
                $data['activationkey'] = md5(mt_rand().mt_rand().mt_rand().mt_rand().mt_rand());
                if($data['uid'] = $this->userModel->add($data))
                {
                    $this->saveUserToSession($data);
                    redirect(__URL__.'&a=verify');
                }
                $message = '保存失败,请稍后再试.';
            }
            $this->assign('message', $message);
        }
        $this->assign('class', 'on01');
        $this->assign('title', '注册我的通行证');
        $this->display();
    }
    
    
    /**
     * 保存用户至session
     * @param array $data
     */
    protected function saveUserToSession($data)
    {
        $this->userModel->setUser('uid', $data['uid']);
        $this->userModel->setUser('username', $data['username']);
        $this->userModel->setUser('nickname', $data['nickname']);
        $this->userModel->setUser('avatar', isset($data['avatar']) ? $data['avatar'] : __APP_RESOURCE__.'imgs/9.jpg');
        $this->userModel->setUser('reg_time', $data['reg_time']);
        $this->userModel->setUser('email', $data['email']);
        $this->userModel->setUser('status', $data['status']);
        $this->userModel->setUser('activationkey', $data['activationkey']);
    }
    
    
    /**
     * 登录
     */
    public function loginAction()
    {
        if($this->isPost())
        {
            $username = $this->post('username');
            $password = $this->post('password');
            $remember = $this->post('remember');
            if($data = $this->userModel->where("username='{$username}' AND password='".md5($password)."'")->find())
            {
                $this->saveUserToSession($data);
                if($remember == 1)
                   Cookie::set('member_name', $username, false, time()+(60*60*24*30));
                else 
                   Cookie::set('member_name', null, false, -1800);
                redirect(__ROOT__);
            }
            redirect(__PROJECT__.'member/index.php?c=user&a=login', 3, '用户名或密码错误！');
        }
        $this->assign('member_name', Cookie::get('member_name'));
        $this->assign('title', '登录通行证');
        $this->display();
    }
    
    
    /**
     * 邮箱验证
     */
    public function verifyAction()
    {
        
        if($this->userModel->isActive()) redirect(__PROJECT__, 3, '您已经通过了邮箱验证！', 2);
        $email = $this->userModel->getUser('email');
        $this->assign('email', $email);
        $this->assign('email_host', 'http://mail.'.substr($email, strpos($email, '@')+1));
        $this->assign('title', '验证电子邮件');
        $this->assign('class', 'on02');
        $this->display();
        abortConnect();
        $this->userModel->sendVerifyEmail();
    }
    
    /**
     * 账号激活
     */
    public function activeAction()
    {
        $u = $this->get('u');
        $k = $this->get('k');
        $data = $this->userModel->field('uid,status')->where("username='{$u}' AND activationkey='{$k}'")->find();
        if($data)
        {
            if($data['status'] == 'N')
            {
                $this->userModel->where("uid={$data['uid']}")->save(array('status'=>'Y'));
                if($this->userModel->isLogin()) $this->userModel->setUser('status', 'Y'); 
                redirect(__ROOT__, 3, '恭喜，您的账号已成功激活！', 1);
            }
            else
            {
                redirect(__PROJECT__, 3, '您已经通过了邮箱验证！', 2);
            }
        }
        redirect(__PROJECT__, 3, '用户不存在！');
    }
    
    /**
     * 修改电子邮件
     */
    public function editEmailAction()
    {
        if($this->isPost())
        {
            $email = $this->post('email');
            if(Validator::isEmail($email))
            {
                $uid = $this->userModel->getUser('uid');
                $this->userModel->where("uid=$uid")->save(array('email'=>$email, 'status'=>'N'));
                $this->userModel->setUser('email', $email); 
                redirect(__URL__.'&a=verify', 3, '电子邮件地址修改成功。', 1);
            }
            $this->assign('message', '无效的邮箱，请重新输入!');
        }
        $this->assign('class', 'on02');
        $this->assign('email', $this->userModel->getUser('email'));
        $this->assign('title', '修改电子邮件');
        $this->display();
    }
    
    /**
     * 用户名和邮箱数据检验
     */
    public function checkAction()
    {
        $name = $this->get('name');
        $value = $this->get('value');
        $where = $data = '';
        if($name == 'username') $where = "username='{$value}'";
        if($name == 'email') $where = "email='{$value}'";
        if($where)
        {
            $data = $this->userModel->field('uid')->where($where)->find();
            $this->ajaxReturn(200, (empty($data) ? true : false));
        }
        else
        {
            $this->ajaxReturn(200, false);
        }
    }
    
    
    /**
     * 找回密码
     */
    public function findPwdAction()
    {
        $isSendEmail = false;
        if($this->isPost())
        {
            $username = $this->post('username');
            $email = $this->post('email');
            $this->assign('email_host', 'http://mail.'.substr($email, strpos($email, '@')+1));
            $message = null;
            if(!Validator::isEmail($email))
            {
                redirect(__ACTION__, 3, '无效的邮箱');
            }
            if($info = $this->userModel->field('password,email')->where("username='{$username}'")->find())
            {
                if($email != $info['email'])
                {
                    redirect(__ACTION__, 3, '您输入的email地址不是您的注册邮箱');
                }
                $this->assign('send_status', 'success');
                $isSendEmail = true;
            }
            else
            {
                redirect(__ACTION__, 3, '您输入的用户名不存在');
            }
        }
        $this->assign('help_email', getCfgVar('cfg_adminemail'));
        $this->assign('title', '找回密码');
        $this->display();
        
        // 输出页面信息，与客户端连接断开后再执行email的发送，提高页面响应速度
        if($isSendEmail)
        {
            abortConnect();
            $this->userModel->sendfindPwdEmail($username, $info['password'], $email);
        } 
            
    }
    
    
    /**
     * 重置密码
     */
    public function resetPwdAction()
    {
        $key = $this->request('key');
        $arr = explode('.', base64_decode($key));
        $username = isset($arr[0]) ? $arr[0] : '';
        $old_time = isset($arr[1]) ? $arr[1] : '';
        $md5 = isset($arr[2]) ? $arr[2] : '';

        if(!$username || !$old_time || !$md5)
        {
            redirect(__PROJECT__, 3, '非法链接参数');
        }
        elseif(!($info = $this->userModel->field('uid,password')->where("username='{$username}'")->find()))
        {
            redirect(__PROJECT__, 3, '无效的参数链接');
        }
        elseif($md5 != md5($username.'+'.$info['password']))
        {
            redirect(__PROJECT__, 3, '无效链接');
        }
        elseif(Date::dateDiff('H', $old_time, Date::getTimeStamp()) >= 24)
        {
            redirect(__URL__.'&a=findPwd', 3, '链接已过期，请重新找回密码');  // 链接超过24小时为无效
        }
        
        if($this->isPost())
        {
            $pwd1 = $this->post('password');
            $pwd2 = $this->post('password2');
            if(!$pwd1 || !$pwd2)
            {
                redirect(__ACTION__.'&key='.$key, 3, '密码不能为空');
            }
            elseif($pwd1 != $pwd2)
            {
                redirect(__ACTION__.'&key='.$key, 3, '您输入的两次密码不一致');
            }
            elseif(strlen($pwd1) < 6 || strlen($pwd1) > 16)
            {
                redirect(__ACTION__.'&key='.$key, 3, '密码请限制在6-16位字符内');
            }
            $this->userModel->where("uid={$info['uid']}")->save(array('password'=>md5($pwd1)));
            $this->assign('send_status', 'success');
        }
        
        $this->assign('key', $key);
        $this->assign('help_email', getCfgVar('cfg_adminemail'));
        $this->assign('title', '密码重置');
        $this->display();
    }
    
    
    /**
     * 验证码
     */
    public function verifyCodeAction()
    {
        Image::buildImageVerify(4,1,'jpeg',50,24);
    }
    
    /**
     * 退出
     */
    public function logoutAction()
    {
        unset($_SESSION);
        Session::destory();
        redirect(__PUB__);
    }
    
    
    /**
     * 第三方Oauth登录
     */
    public function oauthAction()
    {
    	Session::delete('oauth_data');
    	Session::delete('access_token');
    	$oauthObj = $this->getOauthObj();
    	$oauthObj->login($this->getCallbackURL());
    }
	
    
    /**
     * 第三方回调地址
     */
    public function callbackAction()
    {
    	$oauthObj = $this->getOauthObj();
    	$access_token = Session::get('access_token');
    	$typeArr = array('qq'=>1, 'sina'=>'2');
    	$typeId = $typeArr[strtolower($this->get('type'))];
    	if(!$access_token)
    	{
    		$keys['code'] = $this->get('code');
			$keys['redirect_uri'] = $this->getCallbackURL();
			$tokenArr = $oauthObj->getAccessToken('code', $keys);

			// 检查oauth表中是否绑定
			if(!($oauthInfo = model('oauth')->field('uid')->where("openid='{$tokenArr['openid']}' AND type=$typeId")->find()))
			{
				$userInfo = $oauthObj->getUserInfo($tokenArr['openid']);
				$userInfo['type'] = $typeId;
				$data = array('tokenArr'=>$tokenArr, 'userInfo'=>$userInfo);
				Session::set('oauth_data', $data);
				redirect(__URL__.'&a=bind');
			}
			else
			{
				$this->saveUserToSession($this->userModel->where("uid={$oauthInfo['uid']}")->find());
				redirect(__ROOT__);
			}
    	}
    	redirect(__URL__.'&a=bind');
    }
    
    /**
     * 绑定第三方账号
     */
    public function bindAction()
    {
    	$oauthData = Session::get('oauth_data');
    	if(HttpRequest::isAjaxRequest())
    	{
    		$sessUserData = $oauthData['userInfo'];
    		$type = $this->get('type');
    		$email = $this->get('email');
    		$password = md5($this->get('password'));
    		$password2 = md5($this->get('password2'));
    		if($type == 1) // 直接登录
    		{
    			// 验证邮箱和密码是否正确
    			if($userInfo = $this->userModel->where("email='{$email}' AND password='{$password}'")->find())
    			{
    				$tmpUserData = array();
    				if(empty($userInfo['nickname'])) $tmpUserData['nickname'] = $sessUserData['name'];
    				if(empty($userInfo['avatar'])) $tmpUserData['avatar'] = $sessUserData['avatar'];
    				if(empty($userInfo['gender'])) $tmpUserData['gender'] = $sessUserData['gender'];
    				if(count($tmpUserData) > 0) $this->userModel->where("uid={$userInfo['uid']}")->save($tmpUserData);
    				
    				$tmpOauthData = array();
    				$tmpOauthData['uid'] = $userInfo['uid'];
    				$tmpOauthData['openid'] = $oauthData['tokenArr']['openid'];
    				$tmpOauthData['data'] = json_encode($oauthData['tokenArr']);
    				$tmpOauthData['type'] = $oauthData['userInfo']['type'];
    				$tmpOauthData['reg_time'] = Date::format();
    				model('oauth')->add($tmpOauthData);
    				$this->saveUserToSession(array_merge($userInfo, $tmpUserData));
    				
    				$this->ajaxReturn(200, url(__ROOT__));
    			}
    			else
    			{
    				$this->ajaxReturn(300, '密码错误');
    			}
    		}
    		else  // 新注册绑定账号
    		{
    			$tmpUserData = array();
    			$tmpUserData['username'] = substr($email, 0, strpos($email, '@')).rand(1000, 9999);
    			$tmpUserData['nickname'] = $sessUserData['name'];
    			$tmpUserData['password'] = $password;
    			$tmpUserData['email'] = $email;
    			$tmpUserData['avatar'] = $sessUserData['avatar'];
    			$tmpUserData['gender'] = $sessUserData['gender'];
    			$tmpUserData['reg_time'] = Date::format();
    			$tmpUserData['reg_ip'] = HttpRequest::getClientIP();
    			$tmpUserData['status'] = 'N';
                $tmpUserData['activationkey'] = md5(mt_rand().mt_rand().mt_rand().mt_rand().mt_rand());
                if($uid = $this->userModel->add($tmpUserData))
                {
                	$tmpUserData['uid'] = $uid;
                	$this->saveUserToSession($tmpUserData);
                	
                	$tmpOauthData = array();
    				$tmpOauthData['uid'] = $uid;
    				$tmpOauthData['openid'] = $oauthData['tokenArr']['openid'];
    				$tmpOauthData['data'] = json_encode($oauthData['tokenArr']);
    				$tmpOauthData['type'] = $oauthData['userInfo']['type'];
    				$tmpOauthData['reg_time'] = Date::format();
    				model('oauth')->add($tmpOauthData);
    				
                	$this->ajaxReturn(200, url(__URL__.'&a=verify'));
                }
                
    		}
    	}
    	if(!$oauthData) redirect(__ROOT__);
    	$this->assign('title', '创建账号邮箱');
    	$this->assign('userInfo', $oauthData['userInfo']);
    	$this->display();
    }
    
    /**
     * 获取oauth登录对象
     */
	protected function getOauthObj()
    {
    	$type = strtolower($this->get('type'));
    	switch ($type)
    	{
    		case 'qq':
    			import('Sdk.SNS.QQ');
    			return new QQ(getCfgVar('qq_appid'), getCfgVar('qq_appkey'));
    		case 'sina':
    			import('Sdk.SNS.Sina');
    			return new Sina(getCfgVar('sina_client_id'), getCfgVar('sina_client_secret'));
    		default:
    			throw_exception("OUATH_API_{$type} not exists.");
    	}
    }
    
    /**
     * 获取回调地址
     */
    protected function getCallbackURL()
    {
    	return HttpRequest::getHostInfo().url(__ROOT__.'?c=user&a=callback&type='.$this->get('type'));
    }
        
}