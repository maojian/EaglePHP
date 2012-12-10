<?php

/**
 * 诸葛武侯马前课
 * @author maojianlw@139.com
 * @since 2011-12-31
 */

class MqkController extends Controller{
	
	private $curModel = null;
	
    public function __construct() {
    	$this->curModel = model('mqk');
    }
    
    public function indexAction(){
    	$this->display();
    }
    
    /**
     * 未來預知術，蜀漢諸葛武候著，邵康節演。
     * 占法： 凡占此卦，須於清晨洗凈後，選銅錢八枚或銅元亦可，取其中一枚以紅線系之，焚香凈手，虔誠默告一通，隨將錢含掌中，兩手高舉向空，速搖數下，搖畢，
     * 將錢按照《占卦式》八卦圖上從一（乾）、二（兌）、三（離）、四（震）、五（巽）、六（坎）、七（艮）、八（坤）的次序依次排列，此時看系紅線者排在某卦上，即為某卦。
     * 將錢混合收回，重復上述動作連搖，依置如前，再看系紅線者在某卦上，亦即為某卦；
     * 假如第一次搖得系紅線者在“艮”上（即七），第二次搖得系紅線者在“乾”上（即一），
     * 則知占得者“艮乾”是也。但“艮乾” 一卦共有六爻，將銅錢去二個留六個（系紅線者留之），
     * 六枚銅錢混合搖之，以《占爻式》按照一、二、三、四、五、六自下而上排起，系紅線者在幾爻，
     * 便是卦中之何數，如系紅線者在“五”數上，即為“艮乾◎◎◎◎◎”，在“二”數上，即為“艮乾◎◎”是也。即可查找相應的卦之解意，便知所問之事吉兇。
     */
    public function resultAction(){
    	$bagua = array(1=>'乾',2=>'兑',3=>'离',4=>'震',5=>'巽',6=>'坎',7=>'艮',8=>'坤');
    	$up = $bagua[rand(1,8)];
    	$down = $bagua[rand(1,8)];
  		$yao = rand(1,6);
    	$result = $this->curModel->where("keyword='{$up}{$down}' AND yao=$yao")->find();
    	$result['yao'] = str_repeat('◎',$result['yao']);
    	$this->assign('result',$result);
    	$this->display();
    }
    
    /*
    public function exportAction(){
    	$data = file('mqk.txt');
    	$matchs = array();
    	foreach($data as $key=>$str){
    		preg_match("/※	(.*?)  (.*?)  (.*?)演：(.*?)(\(|（)解签：(.*?)(）|\))/",iconv('gbk','utf-8',$str),$matchs);
    		if($matchs[1] == '')
    			echo ( $key+1).'<br/>';
    		else
    			$this->curModel->add(array('keyword'=>trim($matchs[1]),'yao'=>strlen($matchs[2])/3,'content'=>$matchs[3],'yan'=>$matchs[4],'jieqian'=>$matchs[6]));
    	}
    }
    */
   
}
?>