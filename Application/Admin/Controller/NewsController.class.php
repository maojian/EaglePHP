<?php

/**
 * 资讯管理
 * 
 * @author maojianlw@139.com
 * @since 1.0 - 2011-7-8
 */

class NewsController extends CommonController {
	
	private $news_model, $types, $uid, $channelIdArr;
	const __IMG_REGEXP__ = '/<img.*?\"([^\"]*(jpg|bmp|jpeg|gif|png)).*?>/i';
	
	public function __construct(){
		$this->news_model = model('news');
		$this->uid = (int)$_SESSION[SESSION_USER_NAME]['uid'];
		$userInfo = model('manager')->field('channel_ids')->where("uid={$this->uid}")->find();
		$this->types = model('helper')->getNewsTypeList($userInfo['channel_ids'], $this->channelIdArr);
	}

	
	protected function getTypeIdSql()
	{
	    if(is_null($this->channelIdArr)) return '';
	    return 'type IN('.implode(',', $this->channelIdArr).')';
	}
		
	
	/**
	 * 列表页
	 */
	public function indexAction(){
	    $sql = $this->getTypeIdSql();
		$page = $this->page($this->news_model->where($sql)->where(true)->count());
		$news = $this->news_model->where($sql)->where(true)->order($page['orderFieldStr'])->limit("{$page['limit']},{$page['numPerPage']}")->select();
		if(is_array($news)){
			$manager_model = model('manager');
			foreach($news as &$n){
				$n['type'] = str_replace('&nbsp;', '', $this->types[$n['type']]);
				$manager_info = $manager_model->field('username')->where("uid={$n['create_uid']}")->find();
				$n['create_username'] = $manager_info['username'];
			}
		}
		$this->assign('types', $this->types);	
		$this->assign('news', $news);
		$this->assign('page', $page);
		$this->display();
	}
	
	
	/**
	 * 新闻内容检查
	 */
	private function checkText(){
	    $cfg_title_maxlen = getCfgVar('cfg_title_maxlen');
	    $cfg_check_title = getCfgVar('cfg_check_title');
	    $cfg_arc_autokeyword = getCfgVar('cfg_arc_autokeyword');
	    
	    $host = HttpRequest::getHttpHost();
	    $title = $this->post('title');
	    $remote = $this->post('remote');
	    $type = $this->post('type');
	    $autolitpic = $this->post('autolitpic');
	    $dellink = $this->post('dellink');
	    $needwatermark = $this->post('needwatermark');
		$content = $this->post('content', self::_NO_CHANGE_VAL_, false);
	    //$content = html_entity_decode($content);
	    $keywords = $this->post('keywords');
	    $description = $this->post('description');
	    $vote_id = (int)$this->post('orgLookup_id');
	    
	    if(!is_null($this->channelIdArr) && !in_array($type, $this->channelIdArr)){
	        $this->ajaxReturn(300, language('NEWS:type.error'));
	    }
		
	    $matchs = array();
	    
	    // 自动提取关键字
	    if($cfg_arc_autokeyword == 1 && empty($keywords))
	    {
	        try 
	        {
	            $sw = new SplitWord();
	        }
	        catch (Exception $e)
	        {
	            $this->ajaxReturn(300, $e->getMessage());
	        }
	        $sw->SetSource($title);
	        $sw->StartAnalysis();
	        $title_indexs = $sw->GetFinallyIndex();
	        
	        $sw->SetSource(strip_tags($content));
	        $sw->StartAnalysis();
	        $content_indexs = $sw->GetFinallyIndex();
	        
	        if(is_array($title_indexs) && is_array($content_indexs)){
	             $all_indexs = array_merge($title_indexs, $content_indexs);
	             foreach ($all_indexs as $k=>$v){
	                 if(strlen($keywords.$k) > 60) break;
	                 elseif(strlen($k) <= 3) continue;
	                 else $keywords .= $k.',';
	             }
	             $_POST['keywords'] = rtrim($keywords, ',');
	        }
	    }
	    
	    // 自动提取摘要
	    if(($desc_len = getCfgVar('cfg_auot_description')) > 0 && empty($description)){
	        $_POST['description'] = trim(mb_substr(strip_tags($content), 0, $desc_len, 'utf-8'));
	    }
	    
	    // 删除非站内链接
	    if($dellink == 1){
	        preg_match_all("#<a([^>]*)>(.*)<\/a>#iU", $content, $matchs);
	        if($matchs[0]){
	            foreach ($matchs[0] as $k=>$val){
	                $a_text = $matchs[1][$k];
	                if((strpos($a_text, 'http://')!==false || strpos($a_text, 'https://')!==false) && strpos($a_text, $host) === false){
	                    $search_arr[] = $val;
	                    $replace_arr[] = $matchs[2][$k]; 
	                }
	            }
	            $content = str_replace($search_arr, $replace_arr, $content);
	        }
	    }
	    
	    // 检查标题长度
	    if(mb_strlen($title, 'utf-8') > $cfg_title_maxlen){
	       $this->ajaxReturn(300, language('NEWS:max.length.limit', array($cfg_title_maxlen)));
	    }
	    
	    // 检查标题是否重复
	    if($cfg_check_title == 1){
	       $sql = "title='{$title}'";
	       $id = $this->post('id');
	       $sql .= $id ? " AND id!=$id " : '';
	       if($this->news_model->field('id')->where($sql)->find()){
	           $this->ajaxReturn(300, language('NEWS:title.exists'));
	       }
	    }
	    
	    $upload = getUploadAddr();
	    $date = date('Ymd');
	    $dir_arr = $this->getUploadDir();
	    $upload_dir = $dir_arr['uploadDir'];
	    $img_dir = $dir_arr['imgDir'];
	    
	    $regexp = self::__IMG_REGEXP__;
	    //$regexp = '/href="(.*?)"/i';
	    
	    // 远程图片本地化
	    if($remote == 1){
	       preg_match_all($regexp, $content, $matchs);
	       $one_img = null;
	       $img_arr = $matchs[1];
	       //$this->ajaxReturn(300, var_export($img_arr, true));
	       
	       if(count($img_arr) > 0){
     	       foreach ($img_arr as $k=>$img){
     	          $path_info_arr = parse_url($img);
     	          if(in_array($path_info_arr['host'], array($host, ''))){
     	              continue;
     	          }
     	          $path_arr = pathinfo($path_info_arr['path']);
 
     	          $file_name = time().rand(1000, 9999).'.'.$path_arr['extension'];
     	          //$file_name = $path_arr['filename'].'.'.$path_arr['extension'];
     	          
     	          $upload_file = __UPLOAD__.$img_dir.$file_name;
     	          if(file_put_contents($upload_dir.$file_name, curlRequest($img))){
     	             $content = str_replace($img, $upload_file, $content);
     	          }
     	          
     	          if($k == 0){
     	             $one_img = $upload_file;
     	          }
     	       }
	       }
	    }
	    
	    // 图片是否添加水印
	    if($needwatermark == 1){
 	       preg_match_all($regexp, $content, $matchs);
 	       $img_arr = $matchs[1];
	       if(count($img_arr) > 0){
     	       foreach ($img_arr as $k=>$img){
     	          $path_info_arr = parse_url($img);
     	          if(!isset($path_info_arr['host'])){
     	              $src_img = realpath(getUploadAddr().str_replace(__UPLOAD__, '', $img));
     	              Image::watermark($src_img);
     	          }
     	       }
	       }
	    }
	    
	    $is_remote_file = true;
	    // 提取第一张图片为缩略图 
	    if($autolitpic == 1){
	        if(empty($one_img)){
	            preg_match($regexp, $content, $matchs);
      	        $img = isset($matchs[1]) ? $matchs[1] : '';
				if(!empty($img)){
					$path_info_arr = parse_url($img);
					
					// 提取用户上传的照片
					if(isset($path_info_arr['host']) && in_array($path_info_arr['host'], array($host, '')) || !isset($path_info_arr['host'])){
						$one_img = $path_info_arr['path'];
						$is_remote_file = false;
					}else{
						$path_arr = pathinfo($path_info_arr['path']);
						$file_name = time().rand(1000, 9999).'.'.$path_arr['extension'];
						file_put_contents($upload_dir.$file_name, curlRequest($img));
						$one_img = __UPLOAD__.$img_dir.$file_name;
						$is_remote_file = true;
					}
				}
	        }
	        
	        // 此处对提取的第一张图片进行缩略图处理
	        if(!empty($one_img)){
	            $path_info = pathinfo($one_img);
	            $org_img = realpath(getUploadAddr().str_replace(__UPLOAD__, '', $one_img));
	            $file_name = 'thumb_'.$path_info['basename'];
               
				$r = Image::thumb($org_img, $upload_dir.$file_name, '', getCfgVar('cfg_img_width'), getCfgVar('cfg_img_height')); 
	            if($r !== false){
	               $_POST['img'] = __UPLOAD__.$img_dir.$file_name;
	               // 如果不是远程图片本地化，就删除提取的第一张图片
	               if($remote == 0 && $is_remote_file){unlink($org_img);}
	            }
	        }
	    }
	    
	    // 插入投票
	    if($vote_id > 0)
	    {
            $scriptJs = model('vote')->getJs($vote_id);
            $_POST['content'] .= "<!--插入投票start-->\r\n{$scriptJs}\r\n<!--插入投票end-->\r\n";
	    }
	    $_POST['content'] = $content;
	}
	
	
	/**
	 * 采集单个网页内容
	 * @param string $url
	 */
	public function pickAction(){
	    $url = $this->post('url');
	    if(empty($url)) $this->ajaxReturn(300, language('NEWS:url.empty'));
	    $url_info = parse_url($url);
	    $host = isset($url_info['host']) ? $url_info['host'] : '';
	    if(empty($host)) $this->ajaxReturn(300, language('NEWS:url.error'));
	    $content = curlRequest($url);
	    if($content === false) $this->ajaxReturn(300, language('NEWS:url.not.open'));
	    
	    $sw = $ew = '';
	    // 提取采集规则进行内容匹配
	    $pick_info = model('pick')->field('lang,rule')->where("url LIKE '%{$host}%'")->find();
	    if($pick_info){
    	    $rule_arr = explode('{@body}', $pick_info['rule']);
    	    $sw = $rule_arr[0];
    	    $ew = $rule_arr[1];
    	    if(($lang = $pick_info['lang']) == 'gb2312'){
    	         $content = iconv('GBK//IGNORE', 'utf-8', $content);
    	    }
	    }
	    
	    // 标题
	    $matches = array();
	    preg_match_all('/<title>(.*)<\/title>/isU', $content, $matches);
	    $data['title'] = isset($matches[1][0]) ? trim($matches[1][0]) : '';
	    
	    // 关键字
	    preg_match_all('/<meta[\s]+name=[\'"]keywords[\'"] content=[\'"](.*)[\'"]/isU', $content, $matches);
	    $data['keywords'] = isset($matches[1][0]) ? trim($matches[1][0]) : '';
	    
	    // 描述
	    preg_match_all('/<meta[\s]+name=[\'"]description[\'"] content=[\'"](.*)[\'"]/isU', $content, $matches);
	    $data['description'] = isset($matches[1][0]) ? trim($matches[1][0]) : '';
	    
	    if($sw != '' && $ew!=''){
	        $start = strpos($content, $sw);
	        if($start !== false){
    	        $end = strpos($content, $ew, $start);
    	        if($end !== false && $end > $start){
    	            $content = substr($content, $start+strlen($sw), $end-$start-strlen($sw));
    	        }
	        }
	    }
	    
	    $data['content'] = $content;
	    //dump($data);
	    //exit;
	    $this->ajaxReturn(200, 'ok', '', '', '', $data);
	}
	

	/**
	 * 添加文章
	 */
	public function addAction(){
	    if(count($_POST) > 0){
		    $this->checkText();
			$_POST['create_time'] = date('Y-m-d H:i:s');
			$_POST['create_uid'] = $this->uid;
			$_POST['clicknum'] = 0;
			if($news_id = $this->news_model->add()){
			    //model('helper')->weblogUpdates($this->news_model->getHref($news_id));
			    
			    $this->news_model->makeHtml($this->news_model->where("id=$news_id")->find());
				$this->ajaxReturn(200, language('PUBLIC:add.success'), '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, language('PUBLIC:add.failure'));
			}
		}else{
		    $mark_info = fileRW('_mark/watermark');
		    $this->assign('needwatermark', $mark_info['upload']);
			$this->assign('username', $_SESSION[SESSION_USER_NAME]['username']);
			$this->assign('types', $this->types);
			$this->assign('PHPSESSID', session_id());
			$this->assign('uploadUrl', url(__URL__.'&a=upload&immediate=1&target=image', true));
			$this->display();
		}
	}
	
	/**
	 * 修改用户
	 */
	public function updateAction(){
		if($this->isPost()){
		    $this->checkText();
			$_POST['update_time'] = date('Y-m-d H:i:s');
			if($this->news_model->save()){
			    $news_id = $this->post('id');
			    //model('helper')->weblogUpdates($this->news_model->getHref($news_id));
			    $this->news_model->makeHtml($this->news_model->where("id=$news_id")->find());
				$this->ajaxReturn(200, language('PUBLIC:update.success'), '', 'closeCurrent');
			}else{
				$this->ajaxReturn(300, language('PUBLIC:update.failure'), '');
			}
		}else{
			$news_id = (int)$this->get('id');
			$typeIdSql = $this->getTypeIdSql();
		    $sql = $typeIdSql ? " AND $typeIdSql" : '';
			$news_info = $this->news_model->where("id=$news_id {$sql}")->find();
			$manager_info = model('manager')->field('username')->where("uid={$news_info['create_uid']}")->find();
			$news_info['create_username'] = $manager_info['username'];
			$this->assign('news_info', $news_info);
			$this->assign('types', $this->types);
			$this->assign('PHPSESSID', session_id());
			$this->assign('uploadUrl', url(__URL__.'&a=upload&immediate=1&target=image', true));
			$this->display();
		}
	}
	
	
	/**
	 * 生成html静态页
	 * 
	 * @return void
	 */
    public function makeAction()
    {
        if($this->isPost())
        {
            ini_set('max_execution_time', 0);
            $type = (int)$this->post('type');
            $start_id = (int)$this->post('start_id');
            $end_id = (int)$this->post('end_id');
            $this->ajaxReturn(200, language('NEWS:update.file.count', array(model('news')->makeList($type, $start_id, $end_id))));
        }
        $this->assign('types', $this->types);
		$this->display();
    }
	
	
	/**
	 * 删除用户
	 */
	public function deleteAction(){
		$ids = $this->request('ids');
		$cfg_upload_switch = getCfgVar('cfg_upload_switch');
		$typeIdSql = $this->getTypeIdSql();
		$sql = "id IN($ids)".($typeIdSql ? " AND $typeIdSql" : '');
		
		// 删除文章时同时删除图片文件
		if($cfg_upload_switch > 0){
    		
		    function delImg($file){
    		    static $i = 0;
    		    $img_file = realpath(getUploadAddr().str_replace(__UPLOAD__, '', $file));
         	    if(is_file($img_file)) if(unlink($img_file)) $i++;
         	    return $i;
    		}
    		
    		$list = $this->news_model->field('id,content,img,create_time')->where($sql)->select();
    		$i = 0;
    	    foreach ($list as $val){
    		    $content = html_entity_decode($val['content']);
     	        preg_match_all(self::__IMG_REGEXP__, $content, $matchs);
     	        $img_arr = $matchs[1];
     	        if(empty($img_arr)) continue;
     	        foreach ($img_arr as $img) delImg($img); // 删除文章内容图片
     	        $i = delImg($val['img']); // 删除文章缩略图
     	        
     	        // 删除静态的HTML文件
     	        $this->news_model->delHtml($val);
    		    
    	    }
    		$msg = language('NEWS:delete.img.file', array($i));
		}
		
		if(!empty($ids) && $this->news_model->where($sql)->delete()){
		    // 同时删除该文章下的评论
		    model('comment')->where("news_id IN($ids)")->delete();
			$this->ajaxReturn(200, language('PUBLIC:delete.success').$msg);
		}else{
			$this->ajaxReturn(300, language('PUBLIC:delete.failure'));
		}
	}
	
	
	/**
	 * 导出至Excel文件
	 */
	public function exportAction(){
		$data[0] = array('编号', '标题', '内容', '类型', '创建时间', '创建者', '修改时间', '排序值', '点击数','评论数','图片路径','关键字','作者','来源','简介');
		$news = $this->news_model->where($this->getTypeIdSql())->where(true)->limit(10000)->order('id DESC')->select();
		if(is_array($news)){
			$manager_model = model('manager');
			$news_type_model = model('news_type');
			foreach($news as &$n){
				$typeInfo = $news_type_model->field('title')->where("id={$n['type']}")->find();
				$n['type'] = $typeInfo['title'];
				$manager_info = $manager_model->field('username')->where("uid={$n['create_uid']}")->find();
				$n['create_uid'] = $manager_info['username'];
			}
			$data = array_merge($data, $news);
		}
		$xls = new Excel('UTF-8', false, '文章列表');
		$xls->addArray($data);
		$xls->generateXML('news_'.date('YmdHis'));
	}
	
	
	/**
	 * 上传文件
	 */
	public function uploadAction(){
		if($this->isPost() && count($this->file()) > 0){
			$immediate = (int)$this->request('immediate');
			$target = $this->request('target');
			$dir_arr = $this->getUploadDir($target);
			$fileName = $this->upload($dir_arr['uploadDir'], '*');
			$url = (($immediate) ? '!' : '' ).__UPLOAD__.$dir_arr['imgDir'].$fileName;
			echo "{'err':'','msg':'{$url}'}";
		}
		
	}
	
	
	private function getUploadDir($target='image'){
	        $imgDir = "news/{$target}/".date('Ymd').'/';
			$uploadDir = getUploadAddr().$imgDir;
			if(!is_dir($uploadDir)){
				mk_dir($uploadDir);
			}
			return array('uploadDir'=>$uploadDir, 'imgDir'=>$imgDir);
	}
    
	
    
}
?>