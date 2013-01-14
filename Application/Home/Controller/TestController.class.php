<?php

/**
 * 测试文件
 * @author maojianlw@139.com
 * @since 2012-1-2
 */

class TestController extends Controller
{
    
    public function a()
    {
        dump(func_get_args());
    }
    
    
    public function __construct()
    {
        exit;
        $_POST['a'] = "<script>alert(123);</script> eval('some code')  j a v a s c r i p t a l e r t <a href='http://baidu.com' target='_blank'>aaa</a>";
        dump($this->post('a'));
        exit;
        //$arr = array('a'=>$a, 'b'=>$a, 'c'=>array('test'=>$a));
        $a = '$newstext=preg_replace(preg_replace("/(<img[^>]+src\s*=\s*”?([^>"\s]+)”?[^>]*>)/im", ‘<a href=”$2″>$1</a>", $newstext); ';  
        $a = Security::xssClean($a);
        $a = addslashes($a);
        //var_dump($a);
        dump($a);
        exit;
        $str = "eval('some code')";
        $str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
        var_dump($str);
        //echo Date::format();
        echo Date::format();
        //show_404();
        //header('HTTP/1.1 404 Not found');
        exit;
        var_dump(HttpResponse::getApaceHeader());
        HttpResponse::sendHeader(404);
        var_dump(removeInvisibleCharacters('Java\0script.\x00-\u6e38\u5ba2'.Date::format()));
        exit;
		$a = model('manager')->field('m.username,r.name')->table(array('manager'=>'m'))->join('left JOIN role as r ON m.role_id=r.id')->select();
		echo model('manager')->getLastSql();
		dump($a);
		exit;
        dump(Date::getDayRangeInBetweenDate('2008-01-01', '2008-2-01', true));
        echo date('Y-m-d H:i:s');
        exit;
        $m = model('catalog', 'sqlite');
        $sql = 'CREATE TABLE catalog(
                    id integer primary key,
                    pid integer,
                    name varchar(10) UNIQUE
                );';
        //var_dump($m->execute($sql));
        //$sql = 'drop table catalog';
        //var_dump($m->execute("insert into catalog (pid,name) values ('002','eaglephp')"));
        //exit;
        //exit;
        /*
        $data = array('id'=>3,'pid'=>'003', 'name'=>'maojian000');
        var_dump($m->save($data));
        var_dump($m->getDb()->startTrans());
        var_dump($m->getDb()->commit());
        var_dump($m->getDb()->rollback());
        var_dump($m->getLastSql());
        
        $a = $m->getDb()->fields('catalog');
        */
        $a = $m->limit('1,3')->select();
        var_dump($m->getLastSql());
        dump($a);
        
        exit;
        $a = socketRequest('http://m.weather.com.cn/data/101280101.html', $post_string, 'GET');
        var_dump($a);
        exit;
        /********************中文转拼文测试***********************/
        import('Util.Pinyin');
        try{
            $a = Pinyin::getPinyin('暑假去哪玩？');
            echo $a;
        }catch (Exception $e){
            echo $e->getMessage();
        }
        exit;
        var_dump(Session::module());
        $redis = Cache::getInstance('redis');
        $redis->hSet('hashtest', 'a', 1);
        $redis->hSet('hashtest', 'b', '荷叶bbbbb');
        $redis->hDel('hashtest', 'a');
        var_dump($redis->sort('settest', array('sort'=>'desc', 'limit'=>array(0,10))));
        var_dump($redis->sGetMembers('settest'));
        exit;
        var_dump($redis->mset(array('a'=>1233, 'b'=>'eaglephp', c=> 'apple')));
        var_dump($redis->get('c'));
        if(!$redis->get('test')){
            echo 'set';
            $redis->set('test', 'abcdefg');
        }else{
            echo $redis->get('test');
            //$redis->rm('test');
        }
        exit;   
        $memcache = Cache::getInstance('memcache', array('expire'=>10));
        if(!$memcache->get('maojian')){
            echo 'set';
            $memcache->set('maojian', 123456);
        }else{
            echo $memcache->get('maojian');
            //$memcache->rm('maojian');
        }
        exit;
        // apc 调用实例
        $apc = Cache::getInstance('apc', array('expire'=>10));
        if(!$apc->exists('maojian')){
            echo 'set';
            $apc->set('maojian', 123456);
        }else{
            echo $apc->get('maojian');
            $apc->rm('maojian');
        }
        exit;
    }
    
    public function indexAction(){
        
        $a = model('ceshi', 'oracle');
        //$a->select();
        //$a->getDb()->fields('ceshi');
        //$a->getDb()->tables();
        //exit;
        
        $_POST['id'] = 7;
        $_POST['username'] = 'maojian';
        $_POST['password'] = '000999';
        $_POST['createtime'] = '09-APR-12';
        echo $a->add($_POST);
        //echo $a->save($_POST);
        //echo $a->where('id=7')->delete();
        //$a->execute("INSERT INTO CESHI (id, username, password, createtime) VALUES ('5', 'admin', '123456', '09-APR-12')");
        //dump($a->limit('2,3')->order('id DESC')->select());
        echo $a->getLastSql();
    }
    
    public function mongoAction(){
    	$a = model('test', 'mongo');
    	//$v = $a->field('*')->limit()->select();
    	//$v = $a->getDb()->tables();
    	//$v = $a->getDb()->fields('test');
    	//echo $a->getLastSql();
    	dump($v);
    	//exit;
    	$_SERVER['REQUEST_METHOD'] = 'POST';
    	
    	/*
    	$_POST['sid'] = '3443567';
    	$_POST['expiry'] = '123455';
    	$_POST['data'] = '萨达伽师瓜大范甘迪过发大股东非个过yt3';
    	$_POST['text'] = 'aaa';
    	*/
    	//$v = $a->add($_POST);
    	//$v = $a->save($_POST);
    	//$v = $a->delete();
    	//$v = $a->order('sid desc')->limit('2')->select(); 
    	
    	$_POST['id'] = 1;
    	$_POST['name'] = '说到底山东省43434343闪电式';
    	$_POST['content'] = '犹太人天通苑始登商山道';
    	$_POST['text'] = '体验体验特让他';
    	$_POST['session__sid'] = '4';
    	
    	//$v = $a->add(); // auto increment
    	//echo $a->id.'<br/>';
    	//$v = $a->delete();
    	//$v = $a->save();

    	//$v = $a->join('session ON test.id=session.sid')->where(true)->select();
    	//$v = $a->group('usetime')->having('usetime="2012-02-03"')->count('id');
    	//$v = $a->select();
    	//$v = $a->replace();
    	//$v = $a->getbyId(1);
    	echo $a->getLastSql();
    	dump($v);
    	exit;
    }
    

}
