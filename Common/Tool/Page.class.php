<?php
/**
 * 使用方法:
 * 超强分页类，四种分页模式，默认采用类似baidu,google的分页风格。
 * 模式四种分页模式： 
 * require_once('../libs/classes/page.class.php'); 
 * $page=new page(array('total'=>1000,'perpage'=>20)); 
 * echo 'mode:1<br>'.$page->show(); 
 * echo '<hr>mode:2<br>'.$page->show(2); 
 * echo '<hr>mode:3<br>'.$page->show(3); 
 * echo '<hr>mode:4<br>'.$page->show(4); 
 * 开启AJAX： 
 * $ajaxpage=new page(array('total'=>1000,'perpage'=>20,'ajax'=>'ajax_page','page_name'=>'test')); 
 * echo 'mode:1<br>'.$ajaxpage->show(); 
 * 采用继承自定义分页显示模式： 
 * 
 * @copyright Copyright &copy; 2009, maojianlw@139.com
 * @author MaoJian
 * @since 1.0 - 2010-1-11
 */

 class Page{
		var $page_name  = 'page';  //page标签，用来控制url页。比如说xxx.php?PB_page=2中的PB_page 
		var $next_page  = '>';        //下一页
		var $pre_page   = '<';        //上一页
		var $first_page = 'First';    //第一页
		var $last_page  = 'Last';     //最后一页
		var $pre_bar    = '<<';       //上一页
		var $next_bar   = '>>';       //下一页 
		var $format_right= '';   //]
		var $format_left = '';   //[
		var $is_ajax     = false;    //是否支持ajax分页
		
		var $pagebarnum  = 10;
		var $totalpage   = 0;
		var $ajax_action_name = '';
		var $nowindex    = 1; //当前页数
		var $url = '';
		var $offset = 0;
		var $total = 0;
		var $perpage = 0;
		
		function Page($array) {
			if(is_array($array)) {
				if(!array_key_exists('total', $array)) $this->error(__FUNCTION__,'need a param of total');
				 $total   = intval($array['total']);
				 $perpage = array_key_exists('perpage', $array) ? intval($array['perpage']) : 10;
				 $nowindex = array_key_exists('nowindex', $array) ? intval($array['nowindex']) : '';
				 $url     = array_key_exists('url', $array) ? $array['url'] : '';
 			} else {
 				$total = $array;
 				$perpage = 10;
 				$nowindex = '';
 				$url    =  '';
 			}
 			
 			if(!is_int($total) || $total < 0) $this->error(__FUNCTION__, $total.'is not a positive integer');
 			if(!is_int($perpage) || $perpage <=0) $this->error(__FUNCTION__, $perpage.'is not a positive integer');
 			if(!empty($array['page_name'])) $this->set('page_name', $array['page_name']);
 			$this->_set_nowindex($nowindex);
 			$this->_set_url($url);
 			$this->totalpage = ceil($total / $perpage);
 			$this->offset   = ($this->nowindex-1) * $perpage;
 			$this->offset = ($this->offset < 0) ? 0 : $this->offset;
 			$this->total   = $total;
 			$this->perpage = $perpage;
 			if(!empty($array['ajax'])) $this->open_ajax($array['ajax']); //打开ajax模式
		} 
		
		function set($var, $name) {
			if(in_array($var, get_object_vars($this))) {
				$this->var = $name ;	
			} else {
				$this->error(__FUNCTION__, $var.'does not belong to PB_page');	
			}
		}
		
		/**开启ajax
		 ** @return $string
		 **
		**/
		function open_ajax($action) {
			$this->is_ajax = true;
			$this->ajax_action_name = $action;
		}	
		
		/**上一页
		 ** @param $string
		 ** @return $string
		 **
		**/
		function pre_page($style = '') {
			if($this->nowindex > 1) {
				return $this->_get_link($this->_get_url($this->nowindex-1), $this->pre_page, '');  // $style
			}
			
			return '<span class="'.$style.'">'.$this->pre_page.'</span>';
		}
		
		/*** 下一页
		**
		**/
		function next_page($style = '') {
			if($this->nowindex < $this->totalpage) {
				return $this->_get_link($this->_get_url($this->nowindex+1), $this->next_page, '');// $style
			}
			
			return '<span class="'.$style.'">'.$this->next_page.'</span>';
		}
		
		/*** 首页
		**
		**/
		
		function first_page($style = '') {
			if($this->first_page == 1) {
				return '<span class="'.$style.'">'.$this->first_page.'</span>';
			}
			
			return $this->_get_link($this->_get_url(1), $this->first_page, $style);
		}
		
		/** 尾页
		**
		**/
		function last_page($style = '') {
			if($this->last_page == $this->totalpage) {
				return '<span class="'.$style.'">'.$this->total.'</span>';	
			}
			
			return $this->_get_link($this->_get_url($this->totalpage), $this->last_page, $style);
		}
		/*
		*  
		*/
		function nowbar($style='', $nowindex_style = '') {
			$plus = ceil($this->pagebarnum / 2);
			if($this->pagebarnum - $plus + $this->nowindex > $this->totalpage) {
				$plus = $this->pagebarnum - $this->totalpage + $this->nowindex; 
			}
			$begin = $this->nowindex-$plus + 1;//$pagebarnum 开始计数位置
			$begin = $begin >= 1 ? $begin : 1;
			$return = '';
			for($i = $begin; $i < $begin + $this->pagebarnum; $i++) {
				 if($i <= $this->totalpage) {
				 		if($i != $this->nowindex) 
				 		  $return .= $this->_get_link($this->_get_url($i), $i, $style);
				 		 else
				 		  $return .= $this->_get_text('<span class="'.$nowindex_style.'">'.$i.'</span>');
				 } else {
				 		break;
				 }
				 $return .= " ";/* \n */
			}
			unset($begin);
			return $return;
		}
		
		function select() {
			$return .= '<select name="PB_Page_Select" onchange="window.location.href=\''.$this->url.'\'+this.options[this.selectedIndex].value">';
			for($i = 1; $i <= $this->totalpage; $i++) {
				if($this->nowindex == $i) {
					$return .= '<option value="'.$i.'" selected>'.$i.'</option>';
				} else {
					$return .= '<option value="'.$i.'">'.$i.'</option>';	
				}
			}
			unset($i);
			$return .='</select>';
			
			return	$return;
		}
		
		function show($mode = 1) {
			switch($mode) {
				case '1':
				  $this->pre_page  = '上一页';
					$this->next_page = '下一页';
					return $this->pre_page().$this->nowbar().'第'.$this->select().'页'.$this->next_page();
					break;
					
				case '2':
				  $this->pre_page  = '上一页';
					$this->next_page = '&nbsp;下一页';	
					$this->first_page = '首页';
					$this->last_page  = '尾页';
					return $this->first_page().$this->pre_page().$this->next_page().$this->last_page().'第<b>'.$this->nowindex.'</b>/<b>'.$this->totalpage.'</b>页&nbsp;共<b>'.$this->total.'</b>条记录&nbsp;每页<b>'.$this->perpage.'</b>条&nbsp;跳到'.$this->select().'页';
					break;
					
				case '3':
				  $this->pre_page = '上一页';
				  $this->next_page = '下一页';
				  $this->first_page = '首页';
				  $this->last_page  = '尾页';
				  return $this->first_page().$this->pre_page().$this->next_page.$this->last_page;	
				  break;
					
				case '4':
				  $this->pre_page = '上一页';
				  $this->next_page = '下一页';
				  return $this->pre_page('current').$this->nowbar('', 'pagesOn').$this->next_page('current');
				  break;
				  
				case '5':
				  return $this->pre_bar().$this->pre_page.$this->nowbar().$this->next_page.$this->pre_bar;
				  break; 	
			}
		}
		
		function _set_url($url = '') {
			if(!empty($url)) {
				//$this->url = $url. (stristr($url, '?') ? '&' : '?') . $this->page_name.'=';
				$this->url = $url.'&'.$this->page_name.'=';
			} else {
				if(empty($_SERVER['QUERY_STRING'])) {
					$this->url = $_SERVER['REQUEST_URI'].'?'.$this->page_name.'=';
				} else {
					if(stristr($_SERVER['QUERY_STRING'], $this->page_name.'=')) {
						$this->url = str_replace($this->page_name.'='.$this->nowindex, '', $_SERVER['REQUEST_URI']);
						$last = $this->url[strlen($this->url)-1];	
						
						 if($last=='&' || $last=='?') {
						 		$this->url .= $this->page_name.'=';
						 } else {
						 	  $this->url .= '&'.$this->page_name.'=';	
						 }
						 
					} else {
						$this->url = $_SERVER['REQUEST_URI'].'&'.$this->page_name.'=';
					}
				}
			}
			  
		}
		/** 设置当前页面
		**
		**/
		function _set_nowindex($nowindex) {
			if(empty($nowindex)) {
				if(isset($_GET[$this->page_name])) {
					$this->nowindex = intval($_GET[$this->page_name]);
				}
			} else {
					$this->nowindex = intval($nowindex);	
			}
		}
		
		/*得到当前URL
		*
		*/
		function _get_url($pageno = 1) {
			return url($this->url.$pageno);
		}
		
		/*
		**
		**/
		function _get_text($str) {
			return $this->format_left.$str.$this->format_right;
		}
		
		/*
		* 得到LINK
		*/
		function _get_link($url, $text, $style='') {
			$style = (empty($style)) ? '' : 'class="'.$style.'"';
			if($this->is_ajax) {
				 return '&nbsp;<a ' .$style. ' href="javascript:'.$this->ajax_action_name.'(\''.$url.'\')">'.$text.'</a>&nbsp;';
			} else {
				return '<a ' .$style. ' href="'.$url.'">'.$text.'</a>';	
			}
		}
		
		function error($function, $errormsg) {
			die('Error in file:<B>'.__FILE__.'</B>;function:<b>'.$function.'</b>;message:<b>'.$errormsg.'</b>');
  	}
 }
 