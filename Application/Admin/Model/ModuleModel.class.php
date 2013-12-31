<?php

class ModuleModel extends Model{
   
   /**
    * 获得模块列表
    */
   public function getList(){
   		return $this->field('id,name,url,parent,level,target,width,height,number')->order('id ASC')->select();
   }
   
   /**
    * 获得系统模块
    */
   public function getModule(){
   		$modules = $this->getList();
   		return $this->buildTree($modules);
   }
   
   
   /**
    * 获得模块子节点
    * @param Array $data 模块数组
    * @param Int $id 模块ID
    * @param Int 树节点类型
    * @return Array $childs 子节点
    */
   private function getChilds($data, $id, $type){
   		if(is_array($data)){
   		    $childs = null;
   			foreach($data as $v){
   				if($v['parent'] == $id){
					if($type==1 && $v['level']==1){
						continue;
					}else{
						$childs[] = $v;
					}	
				}		
   			}
   		}
		return $childs;
   }
   
   
    /**
    * 创建树节点
    * @param Array $data 模块数组
    * @param Int $id 模块ID
    * @param Int 树节点类型
    * @return Array $childs 子节点
    */
   public function buildTree($data, $id=0, $type=0){
   		$childs = $this->getChilds($data, $id, $type);
   		if($childs == null){
   			return false;
   		}
   		
   		foreach($childs as $k=>$child){
   			if($recur_array = $this->buildTree($data, $child['id'], $type)){
   				$childs[$k]['childs'] = list_sort_by($recur_array, 'number');
   			}
   		}
   		return $childs;
   }
   
   
   /**
    * 根据角色找到相应的模块
    */
   public function findModuleByRoleID($role_id){
   		if($modules = model('role')->field('module_ids')->where("roleid=$role_id")->select()){
   			foreach($modules as $module){
   				$data[$module['moduleid']] = '';
   			}
   		}
   		return $data;
   }
   
   	/**
	 * 取得父模块id
	 */
	public function getParentModule($data){
		if(empty($data)){
			return false;
		}
		
		$modules = $this->getList();
		if(!function_exists('getParentId')) {
    		function getParentId($modules, $parent_id, &$parents){
    			if(is_array($modules))
    			foreach($modules as $k=>$v){
    				$id = $v['id'];
    				if($id == $parent_id){
    					$parent_id = $v['parent'];
    					if(!array_key_exists($id, $parents)){
    						$parents[$id] = $v;
    						if($parent_id != 0){
    							getParentId($modules, $parent_id, $parents);
    						}
    					}
    					break;
    				}
    			}
    		}
		}
		
		$all_modules = array();
		foreach($data as $v){
			getParentId($modules, $v, $all_modules);
		}
		return $all_modules;
	}
   
   
    /**
     * 获得菜单树
     */
    public function getMenuTree($isLoad = false){
    	if(isset($_SESSION[SESSION_USER_NAME]['module_tree']) && !$isLoad){
			return $_SESSION[SESSION_USER_NAME]['module_tree'];
		}
		
		$module_model = model('module');
		$role_id = (int)$_SESSION[SESSION_USER_NAME]['role_id'];
		$module_ids = '';
		if($role_id == 1){
		    $module_id_arr = $module_model->field('id')->order('number DESC')->select();
		    if($module_id_arr){
		       foreach ($module_id_arr as $val){
		          $module_ids .= $val['id'].',';
		       }
		    }
		}else{
		    $role = model('role')->field('module_ids')->where("id=$role_id")->find();
		    $module_ids = $role['module_ids'];
		}
		
		if($module_ids){
			$modules = $module_model->getParentModule(explode(',', $module_ids));
		}
		$_SESSION[SESSION_USER_NAME]['role_modules'] = $modules;
		$modules = $module_model->buildTree($modules, 0, 1);
		$modules = list_sort_by($modules,'number','asc');
	
		if(!function_exists('getChildNode')){
			function getChildNode($childs){
				if($childs == null){
					return null;
				}
				
				$tree = '';
				foreach($childs as $child){
					$url = $child['url'];
					$rel = '';
					if($url){
						$urls = explode('/', $url);
						$rel = ucfirst($urls[0]);
						$url = __ROOT__.'?c='.$urls[0].'&a='.(isset($urls[1]) ? $urls[1] : 'index');
					    if(count($urls)>2)
                        {
                            unset($urls[0], $urls[1]);
                            for($i=2; $i<=count($urls); $i++)
                            {
                                $url .= '&'.$urls[$i].'='.$urls[++$i];
                            }
                        }
						$href = (strpos($url, 'http://') !== false) ? "href=$url" : "href=".url($url);
					}else{
						$href = 'href="javascript:"';
					}
					if(isset($child['childs'])){
						$tree .= "<li><a>{$child['name']}</a><ul>";
						$tree .= getChildNode($child['childs']);
						$tree .= '</ul></li>';
					}else{
						if($child['level'] == 0){
							$target = $child['target'];
							$tree .= "<li><a $href target=\"{$target}\" rel=\"$rel\" ".(($target == 'dialog') ? " width='{$child['width']}' height='{$child['height']}' " : '').">{$child['name']}</a></li>";
						}
							
					} 
				}
				return $tree;
			}
		}
		
		$tree = null;
		if(is_array($modules)){
			foreach($modules as $module){
				$tree .= '<div class="accordionHeader"><h2><span>Folder</span>'.$module['name'].'</h2></div><div class="accordionContent"><ul class="tree treeFolder">';
				$tree .= getChildNode(isset($module['childs']) ? $module['childs'] : null);
				$tree .= '</ul></div>';
			}
		}
		$_SESSION[SESSION_USER_NAME]['module_tree'] = $tree;
		return $tree;
    }
   
}
?>