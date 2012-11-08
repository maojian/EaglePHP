<?php

/**
 * 
 * 构造表单小部件库
 * @author maojianlw@139.com
 * @since 2012-11-1
 */

class Control{
    
    
    /**
     * 
     * 构造表单控件
     * @param string $content
     * @param string $url
     * @param string $layoutH
     * @param string $method
     * @return string  
     */
    public static function buildForm($content, $url, $enctype = false, $layoutH=56, $method='post')
    {
        $uploadFormAttr = $enctype ? 'enctype="multipart/form-data" onsubmit="return iframeCallback(this);"' : 'onsubmit="return validateCallback(this);"';
        $form = '<div class="pageContent">';
        $form .= '<form method="'.$method.'" action="'.$url.'" class="pageForm required-validate" '.$uploadFormAttr.'>';
        $form .= '<div class="pageFormContent nowrap" layoutH="'.$layoutH.'">';
        $form .= $content;
        $form .= '</div><div class="formBar"><ul><li><div class="buttonActive"><div class="buttonContent"><button type="submit">提交</button></div></div></li><li><div class="button"><div class="buttonContent"><button type="button" class="close">取消</button></div></div></li></ul></div></form></div>';
        return $form;
    }
    
    
	/**
	 * 构造textbox
	 * @param string $name
	 * @param string $class 类别required,email,url,date,number,digits,creditcard
	 * @param bool $disable 是否禁用
	 * @param string $alt 说明
	 * @param intval $minlength 最小长度
	 * @param intval $maxlength 最大长度
	 * @param intval $min 最小值
	 * @param intval $max 最大值
	 * @param url $remote 验证的url
	 */
	public static function buildText($name='', $class='', $default='', $disable=false, $info='', $minlength=0, $maxlength=0, $min=0, $max=0, $remote=''){
		$text = empty($name)?'':" name=\"{$name}\"";
		$text .= $disable ? ' disabled="true"' : '';
		$text .= empty($default) ? '' : " value=\"{$default}\"";
		$text .= empty($minlength) ? '' : " minlength=\"{$minlength}\"";
		$text .= empty($maxlength) ? '' : " maxlength=\"{$maxlength}\"";
		$text .= empty($min) ? '' : " min=\"{$min}\"";
		$text .= empty($max) ? '' : " max=\"{$max}\"";
		$text .= empty($remote) ? '' : " remote=\"{$remote}\"";
		$text .= empty($class) ? '' : " class=\"{$class}\"";
		return  "<input type=\"text\"{$text} size=\"30\"/><span class=\"info\">{$info}</span>";
	}
	
	
	/**
	 * 构造下拉类表框
	 * @param array $array 数组$k=>$v $k为显示的内容，$v为提交的值
	 * @param string $name
	 * @param string $default 默认显示的项的值
	 * @param string $class 样式
	 * @return string
	 */
	public static function buildSelect($array, $name='', $default='' , $id='', $class = 'combox required'){
		$select = '<select name="'.$name.'" id="'.($id ? $id : $name).'" class="'.$class.'">';
		$select .= '<option value="">--请选择--</option>';				
		if(is_array($array))
		{
		    foreach ($array as $k=>$v)
		    {
		        $select .= "<option value=\"{$k}\" ".($default == $k ? 'selected' : '').">{$v}</option>";
		    }
		}				
		$select .= '</select>';
		return $select;
	}
	
	
	/**
	 * 单选按钮
	 * @param array $array 数组$k=>$v $k为显示的内容，$v为提交的值
	 * @param string $name
	 * @param string $default 默认勾选的项的值
	 * @return string
	 */
	public static function buildRadio($array, $name='', $default=''){
	    $radio =null;
		if (is_array($array))
		{
			$radioName = empty($name) ? '' : " name=\"{$name}\"";
			foreach ($array as $k => $v)
			{
				$radio .= "<input type=\"radio\" {$radioName} value=\"{$k}\" ".($k == $default ? ' checked="checked"' : '')."  />{$v}&nbsp;&nbsp;";
		    }
    	}
    	return $radio;
    }
    
        
    /**
     * 多选按钮
     * @param array $array 数组$k=>$v $k为显示的内容，$v为提交的值
     * @param string $name
     * @param string $default 默认勾选的项的值
     * @return string
     */
    public static function buildCheckbox($array, $name='', $default='')
    {
        $checkbox = null;
    	if (is_array($array))
    	{
    		$checkName = empty($name)?'':" name=\"{$name}\"";
    		foreach ($array as $k => $v)
    		{
    			$checkbox .= "<input type=\"checkbox\" {$radioName} value=\"{$k}\" ".($k == $default ? ' checked="checked"' : '')."  />{$v}&nbsp;&nbsp;";
    	    }
        }
        return $checkbox;
    }
	
	
	/**
	 * 日期选择
	 * @param string $name
	 * @param bool $readonly 是否只许点选
	 * @param int $yearstart 最小年份到现在的差值
	 * @param int $yearend 最大年份到现在的差值
	 * @param string $format 日期格式YYYYMMdd
	 * @return string
	 */
	public static function buildDatepick($name='', $readonly='true', $yearstart='-80', $yearend='5', $format=''){
		$date = empty($name) ? '' : " name=\"{$name}\"";
		$date .= $readonly ? ' readonly="true"' : '';
		$date .= ' yearstart="'.$yearstart.'" yearend="'.$yearend.'"';
		$date .= empty($format)? '' : " format=\"{$format}\"";
		$date = '<input type="text" class="date"'.$date.'/><a class="inputDateButton" href="javascript:;">选择</a>';
		return $date;
	}
	
	/**
	 * 
	 * 构造文件上传框
	 * @param string $name
	 * @param string $class
	 * @param string $default
	 * @param bool $disable
	 * @param string $info
	 */
	public static function buildFile($name='', $class='', $default='', $disable=false, $info='')
	{
	    $file = empty($name)?'':" name=\"{$name}\"";
		$file .= $disable ? ' disabled="true"' : '';
		$file .= empty($default) ? '' : " value=\"{$default}\"";
		$file .= empty($class) ? '' : " class=\"{$class}\"";
		return  "<input type=\"file\"{$file} size=\"30\"/><span class=\"info\">{$info}</span>";
	}
	
	
	/**
	 * 
	 * 构造编辑器
	 * @param string $name
	 * @param string $upImgUrl
	 * @param string $upLinkUrl
	 * @param string $info
	 * @param int $rows
	 * @param int $cols
	 * @param string $tools
	 */
	public static function buildEditor($name='', $upImgUrl='', $upLinkUrl='', $info='', $rows=15, $cols=100, $tools='full')
	{
	    if($upImgUrl) // 上传图片
	    {
	        $upImgUrl = 'upImgUrl = "!{editorRoot}xheditor_plugins/multiupload/multiupload.html?uploadurl='.$upImgUrl.'&params={\'PHPSESSID\':\''.Session::id().'\'}&ext=图片文件(*.jpg;*.jpeg;*.gif;*.png)"';
	    }
	    
	    if($upLinkUrl) // 链接上传文件
	    {
	        $upLinkUrl = 'upLinkUrl = "!{editorRoot}xheditor_plugins/multiupload/multiupload.html?uploadurl='.$upLinkUrl.'&params={\'PHPSESSID\':\''.Session::id().'\'}&ext=附件(*.doc;*.txt;*.zip;*.rar;*.ppt;*.xls;*.xlsx)"';    
	    }

	    $editor = '<textarea 
							class = "editor" 
							name = "'.$name.'"
							id = "'.$name.'"
							rows = "'.$rows.'" 
							cols = "'.$cols.'" 
							tools = "'.$tools.'"
							'.$upImgUrl.'
							'.$upLinkUrl.'
						></textarea><span class="info">'.$info.'</span>';
	    return $editor;
	}
	
	
	/**
	 * 
	 * 构造文本域
	 * @param string $name
	 * @param string $default
	 * @param int $cols
	 * @param int $rows
	 * @param string $class
	 */
	public static function buildTextarea($name='', $default='', $cols=80, $rows=20, $info='', $class='')
	{
	    $textarea = '<textarea name="'.$name.'" class="'.$class.'" cols="'.$cols.'" rows="'.$rows.'">'.$default.'</textarea><span class="info">'.$info.'</span>';
	    return $textarea;
	}
	
    
	/**
     * warp input
     * @param array $array
     * @return string
     */
    public static function buildWarp($array)
    {
		if(is_array($array))
		{
			foreach ($array as $k=>$v)
			{
				$arr[] = "<dl><dt>{$k}：</dt><dd>{$v}</dd></dl>";
			}
		}
		$str = implode("\r\n", $arr); //<div class="divider"></div>
		return $str;
	}
    
    
}