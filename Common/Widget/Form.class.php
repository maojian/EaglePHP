<?php
/**
 * 表单组件
 * 
 * @author maojianlw@139.com
 * @since 2012-11-1
 */

class Form{
    
    private $url;
    
    private $content = array();
    
    private $method;
    
    private $enctype;
    
    private $layoutH;
    
    /**
     * 
     * 初始化表单控件
     * @param string $url
     * @param string $enctype 是否是上传文件表单
     * @param string $method
     * @param string $layoutH
     */
    public function __construct($url = __URL__, $enctype = false, $method = 'post', $layoutH = 56)
    {
        $this->url = $url;
        $this->method = $method;
        $this->layoutH = $layoutH;
        $this->enctype = $enctype;
    }
    
    public function text($label, $name='', $class='', $default='', $disable=false, $info='', $minlength=0, $maxlength=0, $min=0, $max=0, $remote='')
    {
        $this->content[$label] = Control::buildText($name, $class, $default, $disable, $info, $minlength, $maxlength, $min, $max, $remote);
    }
    
    public function select($label, $array, $name='', $default='' , $id='', $class = 'combox required')
    {
        $this->content[$label] = Control::buildSelect($array, $name, $default, $id, $class);
    }
    
    public function radio($label, $array, $name='', $default = '')
    {
        $this->content[$label] = Control::buildRadio($array, $name, $default);
    }
    
    public function checkbox($label, $array, $name='', $default = '')
    {
        $this->content[$label] = Control::buildCheckbox($array, $name, $default);
    }
   
    public function datepick($label, $name='', $readonly='true', $yearstart='-80', $yearend='5', $format='')
    {
        $this->content[$label] = Control::buildDatepick($name, $readonly, $yearstart, $yearend, $format);
    }
    
    public function file($label, $name='', $class='', $default='', $disable=false, $info='')
    {
        $this->content[$label] = Control::buildFile($name, $class, $default, $disable, $info);
    }
    
    public function editor($label, $name='', $upImgUrl='', $upLinkUrl='', $info='', $rows=15, $cols=100, $tools='full')
    {
        $this->content[$label] = Control::buildEditor($name, $upImgUrl, $upLinkUrl, $info, $rows, $cols, $tools);
    }
    
    public function textarea($label, $name='', $default='', $cols=80, $rows=20, $info='', $class='')
    {
        $this->content[$label] = Control::buildTextarea($name, $default, $cols, $rows, $info, $class);
    }
    
    public function build()
    {
        return Control::buildForm(Control::buildWarp($this->content), $this->url, $this->enctype, $this->layoutH, $this->method);
    }
    
}


/*
 * 	    
$form = new Form(__URL__.'add', true); //新建一个表单 提交地址是__URL__.'add' 方式默认为post
$form->text('真实姓名','realname', 'mmmm', 'required');  //添加 一个text输入框
$form->text('用户名','username','required'); //添加 一个text输入框
$form->text('密码','password',''); //添加 一个text输入框我这里密码是明文的
$form->select('职位', array(100=>'经理',101=>'保洁',102=>'保安'),'gid'); //添加一个下拉
$form->text('电话','phone','required phone',false,'请输入电话','',7,20,0,0);//添加 一个text输入框
$form->radio('性别', array('男'=>'男','女'=>'女','未知'=>'未知'),'sex','未知');//添加一个单选
$form->checkbox('多选', array('男'=>'男','女'=>'女','未知'=>'未知'),'sex2','未知');//添加一个单选
$form->datepick('生日','birth',true);//添加一个日期选择
$form->text('住址','home');//添加 一个text输入框
$form->text('邮箱','email' ,'required email', 'aaa', '', 'sdsdsdsd');//添加 一个text输入框
$form->file('文件', 'file', 'required', 'file.txt', false, '文件格式：txt，jpg');
$form->textarea('摘要', 'description', '这里是摘要', 50, 10, '请注意');
$form->editor('内容', 'content', 'uplodaImg.php', 'uploadAttch.php', '分页以#号分隔');
echo $form->build(); //生成表单并展示
exit;
*/
