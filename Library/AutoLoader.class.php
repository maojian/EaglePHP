<?php
/**
 * 自动加载对象
 *
 * @author maojianlw@139.com
 * @since 2.1 - 2012-9-26
 */

class AutoLoader 
{

    /**
     * loader对象
     * 
     * @var object
     */
    private static $loader;
    
    
    /**
     * 自动加载的类文件
     * 
     * @var array
     */
    private static $autoload_file;

    
    /**
     * 获取单例对象
     * 
     * @return Object
     */
    public static function init()
    {
        if (self :: $loader == null)
        {
            self :: $autoload_file = DATA_DIR.'Config/Autoload.php';
            self :: $loader = new AutoLoader();
        }
        return self :: $loader;
    }

    
    /**
     * 注册自动加载函数
     * 
     * @return void
     */
    private function __construct()
    {
        spl_autoload_register(array (__CLASS__,'spl_autoload_suxx'));
    }
    
    
    /**
     * 重写自动加载类函数
     * 
     * @param string $class_name 类名
     * @return mixed
     */
    private static function spl_autoload_suxx($class_name)
    {
        $len = strlen($class_name);
        if($len!=5 && substr($class_name, -5) == 'Model')
        {
            $file = APP_DIR."Model/{$class_name}.class.php";
        }
        elseif($len!=10 && substr($class_name, -10) == 'Controller')
        {
            $file = APP_DIR."Controller/{$class_name}.class.php";
        }
        else
        {
            $file = ROOT_DIR.self::getAutoloadConfig($class_name);
        }
        $rc = false;
        if ($file && is_file($file))
        {
            require_once $file;
            $rc = $file;
        }
        return $rc;
    }


    /**
     * 获取自动加载需要的配置文件
     *
     * @param string $key
     * @return string
     */
    public static function getAutoloadConfig($key='')
    {
        static $autoloadConfigArr = null;
        if(!$autoloadConfigArr)
        {
            if(file_exists(self::$autoload_file))
            {
                $autoloadConfigArr = include_once self::$autoload_file;
            }
            else
            {
                $autoloadConfigArr = self::setAutoloadConfig();
            }
        }
        return isset($autoloadConfigArr[$key]) ? $autoloadConfigArr[$key] : '';
    }


    /**
     * 设置自动加载需要的配置文件
     *
     * @return array
     */
    public static function setAutoloadConfig()
    {
        import('Tool.Folder');
        import('Tool.File');
        $files = array_merge(self::getFile(LIB_DIR), self::getFile(COM_DIR));
        $data = array();
        foreach($files as $file)
        {
            $pathinfoArr = pathinfo($file);
            $filename = $pathinfoArr['filename'];
            if(($pos = strrpos($filename, '.')) !== false)
            {
                $filename = substr($filename, 0, $pos);
            }
            $data[$filename] = $file;
        }
        $data['SmartyBC'] = basename(COM_DIR).__DS__.'Smarty'.__DS__.'SmartyBC.class.php';
        File::write(self::$autoload_file, "<?php\r\nreturn ".var_export($data, true).';');
        return $data;
    }


    /**
     * 根据目录获取所需加载的文件
     *
     * @param string $dir
     * @return array
     */
    private static function getFile($dir = COM_DIR)
    {
        static $tmpArr = array();
        $files = Folder::read($dir);
        foreach ($files as $file)
        {
            $filePath = $dir.$file;
            if(!in_array($file, array('Smarty', 'Sdk')) && Folder::isDir($filePath))
            {
                self::getFile($filePath.__DS__);
            }
            elseif(substr($filePath, strrpos($filePath, '.')) == '.php')
            {
                $tmpArr[] = str_replace(ROOT_DIR, '', $filePath);
            }
        }
        return $tmpArr;
	}

}
