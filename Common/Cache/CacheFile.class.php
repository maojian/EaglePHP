<?php

/**
 * 数据缓存文件类
 * @author maojianlw@139.com
 * @since 2012-05-08
 */

class CacheFile extends Cache{
     
    private $prefix = '~@';
     
     /**
      * 初始化相关参数
      * @param array $options
      */
     public function __construct($options){
          if($options) $this->options = $options;
          $this->options['dir'] = DATA_DIR.getCfgVar('cfg_cache_dir').(isset($this->options['dir']) && !empty($this->options['dir']) ? __DS__.$this->options['dir'] : '');
          if(substr($this->options['dir'], -1) != __DS__) $this->options['dir'] .= __DS__;
          $this->options['expire'] = $this->options['expire'] ? $this->options['expire'] : getCfgVar('cfg_cache_time'); 
          $this->connected = (mk_dir($this->options['dir']) && is_writeable($this->options['dir']));
     }
     
     /**
      * 获取缓存文件名称
      * @param string $name
      */
     protected function getFileName($name){
          return $this->options['dir'].$this->prefix.md5($name).'.php';
     }
     
     
     /**
      * 读取缓存数据
      * @param string $name
      */
     public function get($name){
          $filename = $this->getFileName($name);
          if(!$this->connected || !is_file($filename)){
              return false;
          }
          $data = File::read($filename);
          if($data !== false){
              $expire = (int)substr($data, 8, 12);
              // 检验缓存是否过期，若过期则进行删除，0为永久有效期。
              if($expire !=0 && time() > (filemtime($filename)+$expire)){
                  $this->rm($name); 
                  return false;
              }else{
                  $data = substr($data, 20, -3);
                  if(function_exists('gzuncompress')){
                      $data = gzuncompress($data);
                  }
                  $data = unserialize($data);
                  return $data;
              }
          }else{
              return false;
          }
     }
     
     
     /**
      * 写入缓存数据
      * @param string $name
      * @param string $value
      * @param int $expire
      */
     public function set($name, $value, $expire=null){
          $filename = $this->getFileName($name); 
          $data = serialize($value);
          if(is_null($expire)) $expire = $this->options['expire'];
          if(function_exists('gzcompress')){
              $data = gzcompress($data, 3);
          }
          $data = "<?php\n//".sprintf("%012d", $expire).$data."\n?>";
          $result = File::write($filename, $data);
          if($result){
              clearstatcache();
              return true;
          }else{
              return false;
          }
     }
     
     
     /**
      * 删除缓存数据
      * @param string $name
      */
     public function rm($name){
          $filename = $this->getFileName($name);
          if(is_file($filename)){
               return unlink($filename);
          }else{
               return false;
          }
     }
     
 
}