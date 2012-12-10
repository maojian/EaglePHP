<?php
/**
 * 投票展示
 * @author maojianlw@139.com
 * @since 2.1 2012-09-07
 */
class VoteController extends CommonController{
     
    private $cur_model;   
 
    public function __construct(){
        $this->cur_model = model('vote');
    }
    
    /**
     * 查看投票结果
     */
    public function viewAction(){
        $id = (int)$this->get('id');
        $info = $this->cur_model->where("id=$id")->find();
        $data = unserialize($info['content']);
        $totalCount = $info['total_count'];
        if(is_array($data) && $totalCount)
        {
            foreach ($data as $k=>&$v)
            {
                $v['percentage'] = round(($v['count']/$totalCount)*100);
            }
        }
        $this->assign('data', $data);
        $this->assign('info', $info);
        $this->assign('title', '投票结果');
        $this->display();
    }
    
    /**
     * 添加投票
     */
    public function addAction()
    {
        $id = (int)$this->request('id');
        $link = __URL__.'view/id/'.$id;
        if($this->isPost())
        {
            $voteitem = $this->post('voteitem');
            $info = $this->cur_model->where("id=$id")->find();
            $data = unserialize($info['content']);
            $nowTime = time();
            $ip = get_client_ip();
            if($nowTime > $info['end_time'])
            {
                $message = '投票已经过期!';
            }
            else if($nowTime < $info['start_time'])
            {
                $message = '投票还没有开始!';
            }
            else if($info['is_enable'] > 0)
            {
                $message = '投票已经被关闭!';
            }
            else if(Cookie::get('vote_ip') == $ip)
            {
                $message = '您已投过票!';
            }
            else if($voteitem && is_array($data))
            {
                Cookie::set('vote_ip', $ip, false, time() * $info['interval'] * 3600);
                $voteitemArr = is_array($voteitem) ? $voteitem : array($voteitem);
                foreach ($data as $k=>&$v)
                {
                    if(in_array($k, $voteitemArr))
                    {
                        $v['count']+=1;
                    }
                }
                $content = serialize($data);
                $this->cur_model->where("id=$id")->save(array('content'=>$content, 'total_count'=>array('exp'=>'total_count+1')));
                redirect($link);
            }
        }
        else
        {
            $message = '请求方法出错';
        }
        redirect($id ? $link : __ROOT__, 3, $message);
    }
    
    
     
}