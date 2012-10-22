<?php

/**
 * word文档操作
 * @since 1.0 - 2012-1-16
 * @author maojianlw@139.com
 */

class Doc {

	/**
	 * 读取word内容
	 */
    public static function read($file){
    	try{
			$word = new COM('word.application'); 
			$word->Documents->Open(realpath($file)); 
			$content = (string) $word->ActiveDocument->Content->text;
			$content = iconv('gbk', 'utf-8', $content);
			$content = nl2br($content);
			//$content = str_replace(chr(13),'', $content);
			//$content = str_replace(chr(10),'', $content);
			$word->ActiveDocument->Close(false); 
			$word->Quit();
			return $content;
		}catch(Exception $e){
			throw_exception($e->getMessage());
		}
		return false;
    }
    
}
