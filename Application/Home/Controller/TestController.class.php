<?php

/**
 * 娴嬭瘯鏂囦欢
 * @author maojianlw@139.com
 * @since 2012-1-2
 */

class TestController extends Controller
{
    
	private $loginObj = null;
	
	public function __construct()
	{		
		/*
		$taget = array('[tab1][server][0]'=>137, '[tab1][server][1]'=>122);
		
		$params = array('tab1'=>
					array('server1'=>array(137, 122),
						  'server2'=>array(100,200)
					),
					'tab2'=>
					array('server3'=>array(400, 500),
						  'server4'=>array(600, 700),
						   'aaa'=>array('bbb'=>array(1,2,3))
					),
					'name' => 'dimain',
					'abc' => array(4,5,6=>array('apple', 'lizi'))
				  );
		
				  
		function test(&$params, &$tmpKeyArr, &$data){
			$i = 0;
			foreach ($params as $k=>&$v){
				$i++;
				if(is_array($v)) {
					$tmpKeyArr[] = '['.$k.']';
					test($v, $tmpKeyArr, $data);
				}else{
					$data[implode($tmpKeyArr, '').'['.$k.']'] = $v;
				}
				if(count($params) == $i) array_pop($tmpKeyArr);
			}
		}
		
		$tmpKeyArr = array();
		$data = array();
		test($params, $tmpKeyArr, $data);
		echo '<pre>';
		print_r($data);
		print_r($params);
		echo '</pre>';
		exit;
		*/
		
		/*
		var_dump(model('news1')->isExists());
		var_dump(model('news')->isExists());
		var_dump(model('news1')->isExists());
		var_dump(model('helper')->isExists());
		var_dump(model('news1')->isExists());
		
		exit;
		import('Sdk.SNS.Sina');
		//$this->loginObj = new QQ('100369006', 'e31f9797dd40c1e2259ef1cc317f19c9', 'http://test.eaglephp.com/test/callback');
		$this->loginObj = new Sina('2822752406', '4873666fd3c395a6194f445977528c7b');
		*/
	}
	
	public function jsonAction(){
		$json = '{"brand_id":"2140","sas_id":"2825","fault_type":"120","mobile_type_id":"2","repair_type":"tiyanji","description":"2234433","imei":"356708044590774"}';
		var_dump(json_decode($json));
		echo $json;
	}
	
    public function loginAction()
    {
		$this->loginObj->login('http://test.eaglephp.com/test/callback');
    }
    
    
    public function callbackAction()
    {
    	$keys['code'] = $this->get('code');
		$keys['redirect_uri'] = 'http://test.eaglephp.com/test/callback';
		$tokenArr = $this->loginObj->getAccessToken('code', $keys);
		dump($tokenArr);		
		$ms  = $this->loginObj->home_timeline(); // done
		dump($ms);
		
    }
    
    public function methodAction()
    {
    	

    }
	
    
    public function indexAction()
    {   
		$a = 'http://www.fa68.com/Public/share/upload/news//20130805/51ff13bbb45bf.jpg';
		dump(parse_url($a));
        //dump($_COOKIE);
        //abortConnect();
        /*Log::output();
        dump($_SESSION);
        abortConnect();
        sleep(10);
        $_SESSION['test'] = 'aaaaa';
        file_put_contents('d:/test.txt', var_export($_SESSION, true));
        exit;
        //throw new TraceException('param error');
        //$_GET['aa'];
        //dump($trace          = debug_backtrace());
        
        //model('news1')->select();
        4/0;
        //aa();
        /*
        exit;
		echo base64_decode('VEROd01Fd3pSbkJpYlVwb1luazRlVTFFUlhoTWVrRjZURzVPTTFwblBUMD0=');
        $str = 'Test Smarty Tpl {{$smarty.const.__URL__&a=wish&b=$title|url}}<br/>
                {{$smarty.const.__ROOT__?show=$recommendArticle[loop].id|url}}<br/>
                {{index.php?c=news&a=index|url}}<br/>
                {{index.php?app=sxxx&act=xxx|url}}
              <hr/>
        		';
        $a = preg_replace_callback('/\{\{[\s\S]*?\}\}/mi', array('Router', 'tplReplace'), $str);
        echo $a;
        $this->assign('date', Date::format());
        $this->assign('title', 'test');
        $this->display();*/
    }
    
    protected function pinyin()
    {
        $data = array(
            'ai',
            'bi', 'ba', 'bei', 'ban', 'bao', 'bu', 'bai', 'biao', 'bang', 'bie',
        	'ci', 'ca', 'can', 'cao', 'cu', 'cai', 'cha', 'chu', 'chi', 'ci', 'cong', 'cuo', 'che',
            'di', 'da', 'dai', 'dan', 'dao', 'du', 'duan', 'dang', 'dui', 'dou', 'deng', 'duo',
            'fa', 'fan', 'fei', 'fen', 'fu', 'fang', 'feng',
            'gu', 'gai', 'gan', 'gen', 'ge', 'gei', 'guo', 'gao', 'gui', 'guai',
            'ha', 'hai', 'han', 'hen', 'he', 'heng', 'hao', 'huan', 'hui', 'hou', 
            'jian', 'jiao', 'jia', 'juan', 'jiu', 'ji',
            'kai', 'kan', 'ken', 'ku', 'kang', 'ke', 'kao', 'kong',
            'lu', 'lun', 'luo', 'la', 'lei', 'lie', 'li', 'lian', 'lai', 'long', 'liao',
            'mo', 'mu', 'mao', 'ma', 'mai', 'men', 'man', 'meng', 'mei', 'mang',
            'na', 'nai', 'ni', 'neng', 'nan', 'nian', 'nv', 'nei', 'nao', 'niu', 'niao', 'nong',
            'pi', 'pai', 'pan', 'pei', 'ping', 'piao', 'pian', 'pa', 'pin', 'peng', 'pang', 'pi', 'pao', 'po', 'pu',
            'qu', 'qun', 'qing', 'qian', 'qin', 'qiu', 'qi', 'que', 'qiao', 'qie', 'qiu',
            'ri', 'ru', 'run', 'rong', 'reng', 'ren', 're', 'rang', 'rou', 'ran', 'ruo', 'rui', 'ruan',
            'si', 'su', 'sa', 'sui', 'sai', 'se', 'shuo', 'shi', 'shui', 'sha', 'suan', 'shuai', 'song', 'shao', 'shan', 'shu', 'shou', 'suo', 'shen',
            'ti', 'tu', 'ta', 'tui', 'tun', 'tan', 'tou', 'tuo', 'tang', 'tai', 'tian', 'ting', 'tiao', 'ting', 'tao', 'tong',
            'wu', 'wang', 'wa', 'wen', 'wan', 'wai', 'wei', 'wo',
            'xu', 'xi', 'xin', 'xian', 'xiao', 'xun', 'xia', 'xie', 'xue', 'xing',
            'yu', 'yun', 'yang', 'yi', 'yan', 'you', 'yao', 'yue', 'yong', 'ye', 'yuan', 'yue', 'ying', 'yin',
            'zi', 'zu', 'zan', 'zai', 'zong', 'zeng', 'zuo', 'zhao', 'zhe', 'zhi', 'zhan', 'zhuan', 'zhen', 'zui', 'zhu', 'zhou', 'ze',
        );
        return $data;
    }
    
    public function moAction()
    {
        set_time_limit(0);
		$a = range(0, 9);
		$b = range('a', 'z');
		$p = $this->pinyin();

        $c = array_merge($a, $b);
        $i = 0;
   
        foreach ($b as $v)
        {
            foreach ($b as $v2)
            {
				$domain = $v.$b[array_rand($b)].$v2.$b[array_rand($b)];
                $return = curlRequest('http://pandavip.www.net.cn/check/check_ac1.cgi?domain='.$domain.'.com');//
                File::write('d:/domain.txt', $return."\n", File::APPEND_WRITEREAD);
            }
        }
    }
    
    
    
    public function domainAction()
    {
        set_time_limit(0);
		$a = range(0, 9);
		$b = range('a', 'z');
        $c = array_merge($a, $b);
        $i = 0;
        while ($i<10000)
        {
            $i++;
            $domain = $b[array_rand($b)].$b[array_rand($b)].$a[array_rand($a)];
			
            $return = curlRequest('http://pandavip.www.net.cn/check/check_ac1.cgi?domain='.$domain.'.cn');//
            File::write('d:/domain.txt', $return."\n", File::APPEND_WRITEREAD);   
            //sleep(rand(0, 3));
        }
    }
    
    
    
    
    public function pinyinAction()
    {
        set_time_limit(0);
        $file = DATA_DIR.'Pinyin/pinyin.php';
        $pinyinData = file_get_contents($file);
        $data = substr($pinyinData, 20, -3);
        $pinyinArr = unserialize(gzuncompress($data));
        $data = array();
        foreach($pinyinArr as $val) $data = array_merge($data, $val);
        
        while ($i<10000)
        {
            $i++;
            
            $p1 = $data[rand(0, 20900)];
            $p1 = $p1[array_rand($p1)];
            
            $p2 = $data[rand(0, 20900)];
            $p2 = $p2[array_rand($p2)];
            
            //$domain = $p1.$p2;
			$domain = '51'.$p1;
            
            $return = curlRequest('http://pandavip.www.net.cn/check/check_ac1.cgi?domain='.$domain.'.com');//
            File::write('d:/domain.txt', $return."\n", File::APPEND_WRITEREAD);   
            //sleep(rand(0, 3));
        }
    }
    
	
	public function ttAction()
	{
		$a = model('test1')->tableExists();
		var_dump($a);
	}
	
    
    /**
     * 姝ｅ垯鎻愬彇鏂囨湰涓殑url鏇挎崲鎴愬彲鐐瑰嚮閾炬帴
     * 
     * @param srting $msg
     * @return string
     */
    function addLink($msg)
    {
        //`.*?((http|https)://[\w#$&+,\/:;=?@.-]+)[^\w#$&+,\/:;=?@.-]*?`i
        //$content = preg_match_all("/(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se|cn))(:(\d+))?\/?/i", $msg, $matches);
        //$content = preg_match_all("/(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se|cn))(:(\d+))?\/?/i", $msg, $matches);
        $content = preg_match_all('#([(http?|ftp)\:\/\/a-zA-Z0-9]*\.[a-zA-Z0-9])+.([a-zA-Z0-9\~\!\@\#\$\%\^\&amp;\*\(\)_\-\=\+\\\/\?\.\:\;\'\,]*)?#', $msg, $matches);
        dump($matches);exit;
        return $msg;
        
        //preg_match_all('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', $msg, $matches);
        //var_dump($matches);exit;
        /*
        $in=array(
            '`((?:https?|ftp)://\S+[[:alnum:]]/?)`si',
            '`((?<!//)(www\.\S+[[:alnum:]]/?))`si'
        );
        // rel=nofollow 鏄憡璇夋悳绱㈠紩鎿庝笉瑕佸幓鎶撳彇杩欎釜閾炬帴
        $out=array(
            '<a href="$1" rel=nofollow target="_blank">$1</a>',
            '<a href="http://$1" rel=\'nofollow\' target="_blank">$1</a>'
        );
        return preg_replace($in, $out, $msg);*/
    }
}
