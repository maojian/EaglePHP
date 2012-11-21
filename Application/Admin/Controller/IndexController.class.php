<?php
class IndexController extends CommonController {
   
    public function indexAction(){
        $this->getWeather();
    	$this->assign('modules', M('module')->getMenuTree());
    	$this->assign('info', $this->getServerInfo());
    	$this->assign('welcome', $this->getWelcome());
    	$this->display();
    }
    
    
    
    private function getWeather(){
        $city_id = $_POST['city_id'];
        //$city_id = 101280101;
        if(empty($city_id)) return false;
        $flag = 'cache_weather_'.$city_id;
        $text = H($flag);
        if(empty($text))
        {
            $text = curlRequest("http://m.weather.com.cn/data/{$city_id}.html", '', 'get');
            $arr = json_decode($text, true);
            $wi_arr = $arr['weatherinfo'];
            
            $dd_style = 'margin-left:5px;text-align:left;line-height:22px;padding:0;margin:0;';
            $text = <<<EOT
                    <dl style="clear:both; margin:0 auto;margin-top:10px;">
                      <dt style="float:right; height:300px;margin-right:20px;">
                      	<img src="http://m.weather.com.cn/img/b{$wi_arr['img1']}.gif"/>&nbsp;
                      	<img src="http://m.weather.com.cn/img/b{$wi_arr['img2']}.gif"/>
                      </dt>
                      <dd style="{$dd_style}margin-bottom:4px;_margin-bottom:4px;font-size:14px;font-weight:bold;">{$wi_arr['city']}</dd>
                      <dd style="{$dd_style}">{$wi_arr['weather1']}</dd>
                      <dd style="{$dd_style}">温度：{$wi_arr['temp1']}</dd>
                      <dd style="{$dd_style}">风力：{$wi_arr['wind1']}</dd>
    				  <dd style="{$dd_style}">洗车：{$wi_arr['index_xc']}</dd>
    				  <dd style="{$dd_style}">旅游：{$wi_arr['index_tr']}</dd>
    				  <dd style="{$dd_style}">晨练：{$wi_arr['index_cl']}</dd>
    				  <dd style="{$dd_style}">晾衣：{$wi_arr['index_ls']}</dd>
    				  <dd style="{$dd_style}">过敏：{$wi_arr['index_ag']}</dd>
    				  <dd style="{$dd_style}">紫外线：{$wi_arr['index_uv']}</dd>
    				  <dd style="{$dd_style}">穿衣指数：{$wi_arr['index_d']}</dd>
    				  <dd style="{$dd_style}">人体舒适度：{$wi_arr['index_co']}</dd>
                    </dl>
EOT;
            if(is_array($wi_arr)) H($flag, $text, 60 * 60 * 3);
        }
        exit($text);
    }
    
    
    
    /**
     *  获取欢迎用户信息
     */
    private function getWelcome(){
        $period = Date::getPeriodOfTime();
        $date = Date::format('Y年m月d日 H时i分s秒');
        $week = Date::getWeek();
        $info = Date::dateInfo('GZ');
        $ip = HttpRequest::getClientIP();
        $address = HttpRequest::getIpLocation($ip);
        $text = "{$period}好，欢迎".self::$adminUser['username'];
        $text .= "&nbsp;&nbsp;{$date}&nbsp;&nbsp;($week)&nbsp;&nbsp;{$info}年";
        $text .= "&nbsp;&nbsp;您的IP是：[{$ip}]";
        if($address) $text .= "&nbsp;&nbsp;来自：".$address;
        return $text;
    }

    
    /**
     * 获得php配置选项 
     */
    protected function getCfg($option_name){
    	$result = get_cfg_var($option_name);
    	if($result === 0){
    		return 'No';
    	}elseif($result === 1){
    		return 'Yes';
    	}else{
    		return $result ? $result : 'No';	
    	}
    }
    
    /**
     * 获得服务器信息
     */
    protected function getServerInfo(){
    	$dis_func = get_cfg_var('disable_functions');
    	$upsize= $this->getCfg('file_uploads') ? $this->getCfg('upload_max_filesize') : 'Not allowed';
		$adminmail=isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : $this->getCfg('sendmail_from');
		!$dis_func && $dis_func = 'No';	
		$info = array(
			array('服务器时间',date('Y-m-d H:i:s')),
			array('服务器主机',$_SERVER['SERVER_NAME']),
			array('服务器IP',gethostbyname($_SERVER['SERVER_NAME'])),
			array('EaglePHP版本',getCfgVar('cfg_sys_version').' <a href="http://www.eaglephp.com/" target="_blank">[查看最新版]</a>'),
			array('操作系统',PHP_OS),
			//array('Server OS Charset',$_SERVER['HTTP_ACCEPT_LANGUAGE']),
			array('服务器软件',$_SERVER['SERVER_SOFTWARE']),
			array('服务器端口',$_SERVER['SERVER_PORT']),
			array('PHP运行模式',strtoupper(php_sapi_name())),
			//array('The file path',__FILE__),
	
			array('PHP版本',PHP_VERSION),
			array('PHP信息',(IS_PHPINFO ? '<a href="'.__ROOT__.'system/phpinfo" target="_blank" style="text-decoration:underline;color:blue" >Yes</a>' : 'No')),
			array('安全模式',$this->getCfg('safe_mode')),
			array('管理员',$adminmail),
			//array('allow_url_fopen',$this->getCfg('allow_url_fopen')),
			//array('enable_dl',$this->getCfg('enable_dl')),
			array('显示错误',$this->getCfg('display_errors')),
			//array('register_globals',$this->getCfg('register_globals')),
			array('magic_quotes_gpc',$this->getCfg('magic_quotes_gpc')),
			array('memory_limit',$this->getCfg('memory_limit')),
			array('post_max_size',$this->getCfg('post_max_size')),
			array('upload_max_filesize',$upsize),
			//array('max_execution_time',$this->getCfg('max_execution_time').' second(s)'),
			//array('disable_functions', $dis_func)
    	);
    	return $info;
    }
    
}
?>