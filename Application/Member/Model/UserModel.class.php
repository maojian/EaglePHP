<?php
class UserModel extends Model
{
    
    const SESSION_NAME = 'member_info';
    
    public function isLogin()
    {
        return is_array($this->getUser());
    }
    
    public function setUser($name, $value)
    {
        $member_info = $this->getUser();
        $member_info[$name] = $value;
        HttpRequest::setSession(self::SESSION_NAME, $member_info);
        return;
    }
    
    public function getUser($name='')
    {
        $member_info = HttpRequest::getSession(self::SESSION_NAME);
        if(empty($name)) return $member_info;
        return isset($member_info[$name]) ? $member_info[$name] : null;
    }
    
    public function isActive()
    {
        return ($this->getUser('status') == 'Y') ? true : false;
    }
    
    
    public function sendVerifyEmail()
    {
        $username = $this->getUser('username');
        $email = $this->getUser('email');
        $activationkey = $this->getUser('activationkey');
        $host = HttpRequest::getHostInfo();
        $date = Date::format('Y年m月d日');
        $cfg_webname = getCfgVar('cfg_webname');
        $cfg_adminemail = getCfgVar('cfg_adminemail');
        $url = $host.url(__PUB__.'member/?c=user&a=active&k='.$activationkey.'&u='.$username);
        $message = '<p style="font-size:14px;">亲爱的用户'.$username.'：</p>
                <p style="font-size:14px; text-indent:2em">当您收到这封信的时候，说明您的注册的电子邮件是有效的。电子邮件通过有效验证后，您的密码安全将更有保障，同时将能为您提供更加及时、安全、有效的专业服务。</p>
                <p style="font-size:14px; text-indent:2em">请点击下面的链接完成验证：</p>
                <p style="font-size:14px; text-indent:2em"><a href="'.$url.'" target="_blank">'.$url.'</a></p>
                <p style="font-size:14px; text-indent:2em">如果点击上面的链接后不能跳转，请您将链接地址复制到您的浏览器地址栏中直接访问。</p>
                <p style="font-size:14px; text-indent:2em">非常感谢您对我们工作的关心和支持！如有任何疑问，欢迎您随时联系我们。</p>
                <br>
                <hr>
                <br>
                <table width="0" border="0" cellpadding="0" cellspacing="0" style="font-size:12px; color:#999999; font-family:Verdana, Geneva, sans-serif">
                  <tbody>
                  <tr>
                    <td height="22px" width="70px"><strong>'.$cfg_webname.'</strong></td>
                    <td><a href="'.$host.'" target="_blank">'.$host.'</a></td>
                  </tr>
                  <tr>
                    <td colspan="2" height="60px;">
            		客服邮箱：<a href="mailto:'.$cfg_adminemail.'" target="_blank">'.$cfg_adminemail.'</a></td>
                  </tr>
                </tbody></table>
                <p align="left" style="font-size:12px; color:#999999;">
                <span style="border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: rgb(204, 204, 204);" t="5" times="">'.$date.'</span>
                </p>';
        $subject = "亲爱的{$username}，请验证邮件以完成通行证注册[{$date}]";
        sendMail($email, $subject, $message);
    }
    
    
    public function sendfindPwdEmail($username, $password, $email)
    {
        $host = HttpRequest::getHostInfo();
        $date = Date::format('Y年m月d日');
        $cfg_webname = getCfgVar('cfg_webname');
        $cfg_adminemail = getCfgVar('cfg_adminemail');
        $url = $host.url(__PUB__.'member/?c=user&a=resetPwd&key='.base64_encode($username.'.'.Date::getTimeStamp().'.'.md5($username.'+'.$password)));
        $message = '<p style="font-size:14px;">亲爱的用户'.$username.'：</p>
                    <p style="font-size:14px; text-indent:2em">感谢您使用'.$cfg_webname.'！您在'.$date.'提交的密码取回请求已被接受，请通过点击下面的链接，重新设置新密码</p>
                    <p style="font-size:14px; text-indent:2em"><a href="'.$url.'" target="_blank">'.$url.'</a></p>
                    <p style="font-size:14px; text-indent:2em">如果您无法点击此链接，请将此链接复制到浏览器地址栏后访问。</p>
                    <p style="font-size:14px; text-indent:2em">为保障您帐号的安全性，以上链接有效期为24小时！此信由'.$cfg_webname.'系统发出，系统不接收回信，请勿直接回复。</p>
                    <p style="font-size:14px; text-indent:2em">非常感谢您对我们工作的关心和支持！如有任何疑问，欢迎您随时联系我们。</p>
                    <br><hr><br>
                    <table width="0" border="0" cellpadding="0" cellspacing="0" style="font-size:12px; color:#999999; font-family:Verdana, Geneva, sans-serif">
                      <tbody><tr><td height="40px">'.$cfg_webname.'：</td></tr>
                      <tr>
                        <td><a href="'.$host.'" target="_blank">'.$host.'</a></td>
                      </tr>
                      <tr><td height="60px;">客服邮箱：'.$cfg_adminemail.'</td></tr>
                    </tbody></table>
                    <p align="right" style="font-size:12px; color:#999999;">'.$date.'</p>';
        $subject = "亲爱的{$username}，请点此邮件找回密码[{$date}]";
        sendMail($email, $subject, $message);
    }

}