<?php

/**
 * 测试文件
 * @author maojianlw@139.com
 * @since 2012-1-2
 */


class TestController extends Controller
{
    
    public function indexAction()
    {
        $str = 'Test Smarty Tpl {{$smarty.const.__URL__&a=wish&b=$title|url}}<br/>
        		gsdg{{$smarty.const.__URL__&a=wish&b=$title&id=$list[loop].id|url}}dsdffd<br/>
        		maojian {{$smarty.const.__URL__&name=$foo[bar]&pwd=$a->pwd|url}}<br/>
        		adssfsdf{{$smarty.const.__URL__|url}}<br/>
        		{{$smarty.const.__ROOT__?c=news&a=index&content=$keywords_arr[loop]|url}}<br/>
        		{{section name=loop loop=$adv_list}}
                <li><a href="{{$adv_list[loop].url}}" target="_blank"><img src="{{$smarty.const.__UPLOAD__}}{{$adv_list[loop].img}}" alt="{{$adv_list[loop].title}}"/></a></li>
                {{/section}}<br/>
                {{/admin/index.php?c=DebugLog&a=report&flag=data|url}}<br/>
        		{{$smarty.section.loop.index}}{{$smarty.const.__ROOT__?c=news&a=index&content=$keywords_arr[loop]|url}}{{$keywords_arr[loop]}}<br/>
        		adssfsdf{{$smarty.const.__ROOT__?c=a&b=$list[loop].b|url}}<br/><hr/>
        		';
        $a = preg_replace_callback('/\{\{[\s\S]*?\}\}/mi', array('Router', 'tplReplace'), $str);
        echo $a;
        $this->assign('date', Date::format());
        $this->assign('title', 'test');
        $this->display();
    }

}
