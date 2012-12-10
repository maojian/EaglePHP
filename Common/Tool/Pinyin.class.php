<?php
/**
 * 中文字符串转换为拼音类
 * @author maojianlw@139.com
 * @link http://www.eaglephp.com
 */

class Pinyin
{
    private static $pinyinArr = null; // 拼音代码库容器
    
    /**
     * 根据中文获取拼音
     * @param string $string 中文字符串
     * @param string $utf8 是否为utf8编码
     * @return array 返回中文拼音
     */
    public static function getPinyin($string, $utf8 = true)
    {
    	self::getPinyinCode();
    	$string = ($utf8 === true) ? iconv('utf-8', 'gbk', $string) : $string;
    	$flow = array();
    	for ($i=0;$i<strlen($string);$i++)
    	{
    		if (ord($string[$i]) >= 0x81 and ord($string[$i]) <= 0xfe) 
    		{
    			$h = ord($string[$i]);
    			if (isset($string[$i+1])) 
    			{
    				$i++;
    				$l = ord($string[$i]);
    				if (isset(self::$pinyinArr[$h][$l])) 
    				{
    					array_push($flow,self::$pinyinArr[$h][$l]);
    				}
    				else 
    				{
    					array_push($flow,$h);
    					array_push($flow,$l);
    				}
    			}
    			else 
    			{
    				array_push($flow,ord($string[$i]));
    			}
    		}
    		else
    		{
    			array_push($flow,ord($string[$i]));
    		}
    	}
    	
    	$pinyin = array();
    	$pinyin[0] = '';
    	for ($i=0;$i<sizeof($flow);$i++)
    	{
    		if (is_array($flow[$i])) 
    		{
    			if (sizeof($flow[$i]) == 1)
    			{
    				foreach ($pinyin as $key => $value)
    				{
    					$pinyin[$key] .= $flow[$i][0];
    				}
    			}
    			if (sizeof($flow[$i]) > 1)
    			{
    				$tmp1 = $pinyin;
    				foreach ($pinyin as $key => $value)
    				{
    					$pinyin[$key] .= $flow[$i][0];
    				}
    				for ($j=1;$j<sizeof($flow[$i]);$j++)
    				{
    					$tmp2 = $tmp1;
    					for ($k=0;$k<sizeof($tmp2);$k++)
    					{
    						$tmp2[$k] .= $flow[$i][$j];
    					}
    					array_splice($pinyin,sizeof($pinyin),0,$tmp2);
    				}
    			}
    		}
    		else 
    		{
    			foreach ($pinyin as $key => $value) 
    			{
    				$pinyin[$key] .= chr($flow[$i]);
    			}
    		}
    	}
    	return ($utf8 === true) ? iconv('gbk', 'utf-8', $pinyin[0]) : $pinyin[0];
    }
    
    
    /**
     * 获取拼音代码库
     */
    private static function getPinyinCode()
    {
        if(!self::$pinyinArr)
        {
            $file = DATA_DIR.'Pinyin/pinyin.php';
            if(!file_exists($file)) throw_exception(language('SYSTEM:file.not.exists', array('pinyin')));
            $pinyinData = file_get_contents($file);
            $data = substr($pinyinData, 20, -3);
            if(function_exists('gzuncompress')) self::$pinyinArr = unserialize(gzuncompress($data));
            else throw_exception(language('SYSTEM:function.not.exists', array('gzuncompress')));
        }
    }
    
}