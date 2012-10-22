

欢迎使用EaglePHP框架！

Author：maojianlw@139.com

EaglePHP官网：www.eaglephp.com

/******************************************* 安装步骤和配置说明 ************************************************/

一、安装步骤说明：

1、将项目解压至你的web根目录下。

2、Linux服务器下，以下目录需授予读写权限：

  EaglePHP/Data
  EaglePHP/Config
  EaglePHP/Public/share/upload
  
3、访问安装系统页面： http://127.0.0.1/EaglePHP/index.php ，安装成功后请删除 Pub 下的 install 文件夹。

4、如正式部署至现网环境，请关闭调式模式，在后台参数设置-核心设置处选择。

5、现网域名指向文件夹为：EaglePHP/Public。Public下面的文件夹都为可公共访问的前端资源及各应用的入口文件。


二、系统相关配置说明：

1、数据库配置文件：EaglePHP/Config/DbConfig.php

2、session会话保存方式：修改  Library/Main.inc.php 中的 SESSION_SAVE_TYPE 常量，共有三种方式可供选择（memcache 、 table 、file），默认为file保存。
  
3、后续如有数据库表结构更新，请登录后台执行更新ORM缓存操作，ORM为对象关系映射，其作用是自动将表结构与模型对象进行绑定关联。ORM缓存文件夹默认为EaglePHP/Data/Field。

4、数据库脚本文件是EaglePHP/Data/Install/eaglephp.sql。



/******************************************* 注意事项  ************************************************/ 

1、后台更新内容后，需到应用中心->系统管理->缓存更新位置选择清空缓存数据。

2、凡是对数据库表结构的修改，需到应用中心->系统管理->缓存更新位置选择更新表映射ORM。

3、新增加公共类文件时，需手动配置Config下的AutoloadConfig.php文件，指定类文件的路径，这样有利于实现自动加载类文件，否则会出现类找不到的情况。
       另外新增类文件，也可以把Config下的AutoloadConfig.php文件删除，框架会自动重生生成AutoloadConfig.php文件。



/******************************************* 架构说明  ************************************************/

Application	各种应用、项目目录
	Admin	CMS管理系统
	Api	             对外接口应用
	Doc     生成开发帮助文档应用
	Install 网站安装目录
	Home    网站前台目录
	
Common	系统公共包
	Cache   缓存驱动器
	Db	             数据库驱动器
	Mail    邮件组件
	Sdk     外部开发工具包
	Session session驱动器
	Smarty	Smarty模板类库
	Tool	公共工具类目录

Config    框架配置文件夹

Data    系统数据文件夹
	Dict    数据词典
	I18n    国际化语言包
	Mark    图片水印包
	Install 系统安装包
	Backup  数据备份包
	Cache   Smarty缓存包
	Compile Smarty编译包
	Field   ORM表映射包
	File    数据缓存文件包
	Log     各应用日志目录
	Session 会话存储目录

Library	系统核心类库，此库下面的文件不需要手动import导入，全部为自动加载。

Public	公共访问目录，存放前端相关资源文件、入口文件。


/******************************************* 新建应用说明 ************************************************/

在这里以创建Blog应用为例：

1、在Application目录下创建Blog的文件夹。

2、在Application/Blog下创建Config、Controller、Model、View等文件夹。

3、在Application/Blog/Controller下新建一个 IndexController.class.php 控制器文件。

4、写上一段测试代码：

class IndexController extends Controller{
       public function indexAction(){
            echo 'hellow world';
       }
}

5、在Public目录下创建Blog文件夹。

6、在Public/Blog/下创建index.php，加入如下代码：
  define('APP_NAME', 'blog'); // 应用名称
  include '../../Library/Loader.php';
  MagicFactory::getInstance('Application')->run();
	
7、Blog访问地址：http://127.0.0.1/EaglePHP/Public/Blog/


/******************************************* 最后 ************************************************/

使用框架过程中如果遇到什么疑问或者建议，请联系我。
Email：maojianlw@139.com
QQ:   408865477
QQ群：     244174426

EaglePHP官网：           http://www.eaglephp.com
EaglePHP源码下载：   http://eaglephp.googlecode.com
EaglePHP留言反馈：   http://www.eaglephp.com/index.php/message
							
																								
																MJ	2012年10月8日 8:00
																	
