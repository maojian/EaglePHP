<?php
class VoteModel extends Model
{

	public function getJs($id)
	{
	    $dir = getUploadAddr().'vote/';
	    mk_dir($dir);
	    $fileName = "vote_{$id}.js";
	    $file = $dir.$fileName;
	    File::write($file, 'document.write("'.addslashes($this->getHtml($id)).'");', File::WRITEREAD);
	    $url = '<script language="javascript" src="'.__UPLOAD__.'vote/'.$fileName.'"></script>';
	    return $url;
	}
	
	
	public function getHtml($id)
	{
	    $viewLink = __PROJECT__."index.php/vote/view/id/$id";
	    $actionLink = __PROJECT__."index.php/vote/add/id/$id";
	    $info = $this->where("id=$id")->find();
	    $str = '';
	    
	    if($info)
	    {
	        $data = unserialize($info['content']);
	        $attr = ($info['is_more'] == 1) ? 'type=\'checkbox\' name=\'voteitem[]\'' : 'type=\'radio\' name=\'voteitem\'';
	        if(is_array($data))
	        foreach ($data as $k=>$v)
	        {
	            $str .= "<tr><td height=30 bgcolor=#FFFFFF style='color:#666666'><input {$attr} value='{$k}' />{$v['name']}</td></tr>"; 
	        }
	    }
	    
	    $html  = <<<EOT
	    			<form name='voteForm' method='post' action='{$actionLink}' target='_blank'>
                    <table width='100%' border='0' cellspacing='1' cellpadding='1' id='voteItem'>
                    <input type='hidden' name='id' value='{$id}' />
                    <input type='hidden' name='is_more' value='{$info['is_more']}' />
                    <tr align='center'><td height='30' id='votetitle' style='border-bottom:1px dashed #999999;color:#3F7652' ><strong>{$info['name']}</strong></td></tr>
                    {$str}
                    <tr><td height='30'>
                    <input type='submit' style='width:40;background-color:#EDEDE2;border:1px soild #818279' name='vbt1' value='投票' />
                    <input type='button' style='width:80;background-color:#EDEDE2;border:1px soild #818279' name='vbt2' value='查看结果' onClick="window.open('{$viewLink}');" /></td></tr>
                    </table>
                    </form>
EOT;
        $html = str_replace(array("\r", "\n"), array('', ''), trim($html));
        return $html;
	}
	
}