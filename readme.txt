欢迎使用EaglePHP框架！

EaglePHP，是一款开源、高效、面向对象的PHP MVC开发框架，完全基于PHP5可用于开发WEB程序和服务，
借鉴国外优秀框架的设计思路，分层的设计思想使独立开发成为可能，建立模型推动代码的重用，
有助于促进快速软件开发(RAD)和创建更稳定的程序，节约了开发者的时间，并减少重复编写代码的劳动。 

框架特点：
1、代码完全采用php5面向对象编写、简洁、规范。
2、模块化的结构设计，易于扩展。
3、采用mvc模式，提高程序的可维护性。
4、支持多项目管理，由不同的单入口控制转发。
5、支持cli命令行模式。
6、为mysql、cache相关基础类提供多驱动扩展。
7、使用smarty模板引擎，分离表现层与业务层。
8、提供统一的自动加载模式和统一的命名空间。
9、提供技术人员开发调式工具及错误跟踪系统。
10、基于页面url和表单(隐藏域)action的驱动架构。
11、自动生成数据库表操作，支持二次开发。
12、支持session在memcahe、database、file中的无缝切换。
13、高安全性，内置filter组件实现过滤机制，防止sql注入及xss跨站脚本攻击。
14、支持orm，真正实现OOP开发的快捷且性能优越。
15、内置document输出模块，为指定项目生成chm或html格式的api开发手册。 


/******************* 安装步骤和配置说明   ******************/

一、安装步骤说明：

1、将项目解压至你的web根目录下。

2、Linux服务器下，以下目录需授予读写权限：

  EaglePHP/Data
  EaglePHP/Config
  EaglePHP/Public/share/upload
  
3、首次访问EaglePHP会自动跳转到CMS安装页面 ，安装成功后请删除 Public 下的 install 文件夹。

4、如正式部署至现网环境，请关闭调式模式，在后台参数设置-核心设置处选择。

5、现网域名指向文件夹为：EaglePHP/Public。Public下面的文件夹都为可公共访问的前端资源及各应用的入口文件。


二、系统相关配置说明：

1、数据库配置文件：EaglePHP/Config/Database.php

2、session会话保存方式：修改  Config/Constants.php 中的 SESSION_SAVE_TYPE 常量，共有三种方式可供选择（memcache 、 table 、file），默认为file保存。
  
3、后续如有数据库表结构更新，请登录后台执行更新ORM缓存操作，ORM为对象关系映射，其作用是自动将表结构与模型对象进行绑定关联。ORM缓存文件夹默认为EaglePHP/Data/Field。

4、数据库脚本文件是EaglePHP/Data/Install/eaglephp.sql。

5、注意：切换URL模式，需删除Data/Compile下的文件，以便重新生成新的URL路径。
  a、普通参数模式。      		（index.php?c=news&a=show&id=88）
  b、pathinfo模式。		（index.php/news/show/id/88）
  c、.html模式。		（index.php/news/show/id-88.html）


/******************* 注意事项   ******************/ 

1、后台更新内容后，需到应用中心->系统管理->缓存更新位置选择清空缓存数据。

2、凡是对数据库表结构的修改，需到应用中心->系统管理->缓存更新位置选择更新表映射ORM。

3、新增加公共类文件时，需删除或手动配置EaglePHP/Data/Config下的Autoload.php文件，指定类文件的路径，这样有利于实现自动加载类文件，否则会出现类找不到的情况。


/******************* 架构说明  ******************/

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
	Widget  部件工具类

Config    框架配置文件夹

Data    系统数据文件夹
	Config  系统生成的配置文件
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


/******************* 新建应用  ******************/

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


/******************* 联系方式  ******************/

使用框架过程中如果遇到什么疑问或者建议，请联系我。
Email: maojianlw@139.com
QQ:    408865477
QQ群:  244174426

EaglePHP官网:           http://www.eaglephp.com
EaglePHP留言反馈:   	http://www.eaglephp.com/index.php/message
EaglePHP Github:	https://github.com/maojian/EaglePHP
EaglePHP Googlecode:   	http://eaglephp.googlecode.com
							
																	
