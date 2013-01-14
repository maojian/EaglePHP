<?php
class XiamiController extends ApiCommonController{
    
    public function indexAction(){
       $song_id = (int)$this->request('song_id');
       if(empty($song_id)) $this->formatReturn(208);
       $data = $this->getBySongId($song_id);
       $data['location'] = $this->getLocation($data['location']);
       $this->formatReturn(200, $data);
       //$this->downFile($data);
    }
    
    public function getBySongId($sond_id){
       $xml = curlRequest('http://www.xiami.com/widget/xml-single/uid/0/sid/'.$sond_id);
       $xmlArr = XML::XML_unserialize($xml);
       return $xmlArr['trackList']['track'];
    }   
    
    public function downFile($data){
       $uploadDir = getUploadAddr().'xiami';
       if(!is_dir($uploadDir)){
           mk_dir($uploadDir);
       }
       $song_name = iconv('utf-8', 'gbk', $data['song_name']);
       $file = "{$uploadDir}\\{$song_name}.mp3";
       file_put_contents($file, curlRequest($data['location']));
    }
    
    public function getLocation($location){
        $loc_2 = (int)substr($location, 0, 1);
        $loc_3 = substr($location, 1);
        $loc_4 = floor(strlen($loc_3) / $loc_2);
        $loc_5 = strlen($loc_3) % $loc_2;
        $loc_6 = array();
        $loc_7 = 0;
        $loc_8 = '';
        $loc_9 = '';
        $loc_10 = '';
        while ($loc_7 < $loc_5){
            $loc_6[$loc_7] = substr($loc_3, ($loc_4+1)*$loc_7, $loc_4+1);
            $loc_7++;
        }
        $loc_7 = $loc_5;
        while($loc_7 < $loc_2){
            $loc_6[$loc_7] = substr($loc_3, $loc_4 * ($loc_7 - $loc_5) + ($loc_4 + 1) * $loc_5, $loc_4);
            $loc_7++;
        }
        $loc_7 = 0;
        while ($loc_7 < strlen($loc_6[0])){
            $loc_10 = 0;
            while ($loc_10 < count($loc_6)){
                $loc_8 .= isset($loc_6[$loc_10][$loc_7]) ? $loc_6[$loc_10][$loc_7] : null;
                $loc_10++;
            }
            $loc_7++;
        }
        $loc_9 = str_replace('^', 0, urldecode($loc_8));
        return $loc_9;
    }
   
 
}