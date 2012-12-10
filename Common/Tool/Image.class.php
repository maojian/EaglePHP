<?php
/**
 * 图像操作类
 * @copyright Copyright &copy; 2011, MAO JIAN
 * @since 1.0 - 2011-8-5
 * @author maojianlw@139.com
 */

 
class Image
{

    /**
     * 取得图像信息
     * @static
     * @access public
     * @param string $image 图像文件名
     * @return mixed
     */
    public static function getImageInfo($img) 
    {
        $imageInfo = getimagesize($img);
        if( $imageInfo!== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
            $imageSize = filesize($img);
            $info = array(
                "width"=>$imageInfo[0],
                "height"=>$imageInfo[1],
                "type"=>$imageType,
                "size"=>$imageSize,
                "mime"=>$imageInfo['mime']
            );
            return $info;
        }else {
            return false;
        }
    }

    /**
     
     * 显示服务器图像文件
     * 支持URL方式
     * @static
     * @access public
     * @param string $imgFile 图像文件名
     * @param string $text 文字字符串
     * @param string $width 图像宽度
     * @param string $height 图像高度
     * @return void
     */
    public static function showImg($imgFile,$text='',$width=80,$height=30) 
    {
        //获取图像文件信息
        $info = Image::getImageInfo($imgFile);
        if($info !== false) {
            $createFun  =   str_replace('/','createfrom',$info['mime']);
            $im = $createFun($imgFile);
            if($im) {
                $ImageFun= str_replace('/','',$info['mime']);
                if(!empty($text)) {
                    $tc  = imagecolorallocate($im, 0, 0, 0);
                    imagestring($im, 3, 5, 5, $text, $tc);
                }
                if($info['type']=='png' || $info['type']=='gif') {
                imagealphablending($im, false);//取消默认的混色模式
                imagesavealpha($im,true);//设定保存完整的 alpha 通道信息
                }
                header("Content-type: ".$info['mime']);
                $ImageFun($im);
                imagedestroy($im);
                return ;
            }
        }
        //获取或者创建图像文件失败则生成空白PNG图片
        $im  = imagecreatetruecolor($width, $height);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
        imagestring($im, 4, 5, 5, "NO PIC", $tc);
        Image::output($im);
        return ;
    }
    

    
    /**
     * 图片裁剪
     */
    public static function crop($image, $thumbName, $type ='', $maxWidth=100, $maxHeight=100, $x=0, $y=0)
    {
		
		$info  = Image::getImageInfo($image);
        $width  = $info['width'];
        $height = $info['height'];
        $type = ($type) ? $type : $info['type'];
        $type = strtolower($type);
        $type = ($type == 'jpg') ? 'jpeg' : $type;
        
        $imageFun = "imagecreatefrom{$type}";
		$oriImg = $imageFun($image);
		$thumbImg = imagecreatetruecolor($maxWidth, $maxHeight);
		imagecopyresampled($thumbImg, $oriImg, 0, 0, $x, $y, $maxWidth, $maxHeight, $maxWidth, $maxHeight);
       	
       	if($type == 'gif' || $type == 'png'){
       		$backColor = imagecolorallocate($thumbImg, 0, 255, 0);
       		imagecolortransparent($thumbImg, $backColor); // 设置为透明色
       	}else if($type == 'jpeg'){
       		imageinterlace($thumbImg, 1);
       	}
       	
       	$imageFun = "image{$type}";
       	$return = $imageFun($thumbImg, $thumbName);
       	
       	imagedestroy($oriImg);
       	imagedestroy($thumbImg);
       	
       	return $return;

    }
    

    /**
     * 生成缩略图
     * @static
     * @access public
     * @param string $image  原图
     * @param string $type 图像格式
     * @param string $thumbname 缩略图文件名
     * @param string $maxWidth  宽度
     * @param string $maxHeight  高度
     * @param string $position 缩略图保存目录
     * @param boolean $interlace 启用隔行扫描
     * @return void
     */
    public static function thumb($image,$thumbname,$type='',$maxWidth=100,$maxHeight=100, $x=0, $y=0, $interlace=true)
    {
        // 获取原图信息
        $info  = Image::getImageInfo($image);
         if($info !== false) {
            $srcWidth  = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type)?$info['type']:$type;
			$type = strtolower($type);
            $interlace  =  $interlace? 1:0;
            unset($info);
            
            if($x == 0 && $y == 0){
            	$scale = min($maxWidth/$srcWidth, $maxHeight/$srcHeight); // 计算缩放比例
	            if($scale>=1) {
	                // 超过原图大小不再缩略
	                $width   =  $srcWidth;
	                $height  =  $srcHeight;
	            }else{
	                // 缩略图尺寸
	                $width  = (int)($srcWidth*$scale);
	                $height = (int)($srcHeight*$scale);
	            }
            }else{
            	$width   =  $srcWidth;
	            $height  =  $srcHeight;
            	$srcWidth = $maxWidth;
            	$srcHeight = $maxHeight;
            }
            


            // 载入原图
            $createFun = 'ImageCreateFrom'.($type=='jpg'?'jpeg':$type);
            $srcImg     = $createFun($image);

            //创建缩略图
            if($type!='gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);
            
            if('gif'==$type || 'png'==$type) {
                imagealphablending($thumbImg, false);//取消默认的混色模式
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                $background_color  =  imagecolorallocate($thumbImg,  0,255,0);  //  指派一个绿色
                imagecolortransparent($thumbImg,$background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }
                
            // 复制图片
            if(function_exists('ImageCopyResampled'))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth,$srcHeight);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height,  $srcWidth,$srcHeight);
            
                /*
            if('gif'==$type || 'png'==$type) {
                //imagealphablending($thumbImg, false);//取消默认的混色模式
                //imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
                $background_color  =  imagecolorallocate($thumbImg,  0,255,0);  //  指派一个绿色
				imagecolortransparent($thumbImg,$background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }
			*/
                
            // 对jpeg图形设置隔行扫描
            if('jpg'==$type || 'jpeg'==$type) 	imageinterlace($thumbImg,$interlace);

            //$gray=ImageColorAllocate($thumbImg,255,0,0);
            //ImageString($thumbImg,2,5,5,"ThinkPHP",$gray);
            // 生成图片
            $imageFun = 'image'.($type=='jpg'?'jpeg':$type);
            
            $parentDir = dirname($thumbname);
            if(!is_dir($parentDir)){
            	mk_dir($parentDir);
            }
            $imageFun($thumbImg,$thumbname);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
         }
         return false;
    }

    /**
     * 根据给定的字符串生成图像
     * @static
     * @access public
     * @param string $string  字符串
     * @param string $size  图像大小 width,height 或者 array(width,height)
     * @param string $font  字体信息 fontface,fontsize 或者 array(fontface,fontsize)
     * @param string $type 图像格式 默认PNG
     * @param integer $disturb 是否干扰 1 点干扰 2 线干扰 3 复合干扰 0 无干扰
	 * @param bool $border  是否加边框 array(color)
     * @return string
     */
	public static function buildString($string,$rgb=array(),$filename='',$type='png',$disturb=1,$border=true) 
	{
		if(is_string($size))		$size	=	explode(',',$size);
		$width	=	$size[0];
		$height	=	$size[1];
		if(is_string($font))		$font	=	explode(',',$font);
		$fontface	=	$font[0];
		$fontsize	 	=	$font[1];
		$length		=	strlen($string);
        $width = ($length*9+10)>$width?$length*9+10:$width;
		$height	=	22;
        if ( $type!='gif' && function_exists('imagecreatetruecolor')) {
            $im = @imagecreatetruecolor($width,$height);
        }else {
            $im = @imagecreate($width,$height);
        }
		if(empty($rgb)) {
			$color = imagecolorallocate($im, 102, 104, 104);
		}else{
			$color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
		}
        $backColor = imagecolorallocate($im, 255,255,255);    //背景色（随机）
		$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $pointColor = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));                 //点颜色

        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
        @imagestring($im, 5, 5, 3, $string, $color);
		if(!empty($disturb)) {
			// 添加干扰
			if($disturb=1 || $disturb=3) {
				for($i=0;$i<25;$i++){
					imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pointColor);
				}
			}elseif($disturb=2 || $disturb=3){
				for($i=0;$i<10;$i++){
					imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$pointColor);
				}
			}
		}
        Image::output($im,$type,$filename);
	}

    /**
     * 生成图像验证码
     * @static
     * @access public
     * @param string $length  位数
     * @param string $mode  类型
     * @param string $type 图像格式
     * @param string $width  宽度
     * @param string $height  高度
     * @return string
     */
    public static function buildImageVerify($length=4,$mode=1,$type='png',$width=48,$height=22, $rand=0, $verifyName='verify')
    {
        $randval = String::rand_string($length,$mode);
        Session::set($verifyName, md5($randval));
        $width = ($length*10+10)>$width?$length*10+10:$width;
        if ( $type!='gif' && function_exists('imagecreatetruecolor')) {
            $im = @imagecreatetruecolor($width,$height);
        }else {
            $im = @imagecreate($width,$height);
        }
        $r = Array(225,255,255,223);
        $g = Array(225,236,237,255);
        $b = Array(225,236,166,125);
        $key = mt_rand(0,3);

        $backColor = imagecolorallocate($im, $r[$key],$g[$key],$b[$key]);    //背景色（随机）
		$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        $pointColor = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));                 //点颜色

        @imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        @imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
        $stringColor = imagecolorallocate($im,mt_rand(0,200),mt_rand(0,120),mt_rand(0,120));
		// 干扰
		for($i=0;$i<10;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
		}
		for($i=0;$i<25;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pointColor);
		}
		for($i=0;$i<$length;$i++) {
			imagestring($im,5,$i*10+5,($rand===0 ? mt_rand(1,8) : $rand),$randval{$i}, $stringColor);
		}
//        @imagestring($im, 5, 5, 3, $randval, $stringColor);
        Image::output($im,$type);
    }

	// 中文验证码
	public static function GBVerify($length=4,$type='png',$width=180,$height=50,$fontface='simhei.ttf',$verifyName='verify') 
	{
		$code	=	rand_string($length,4);
        $width = ($length*45)>$width?$length*45:$width;
		Session::set($verifyName, md5($code));
		$im=imagecreatetruecolor($width,$height);
		$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
		$bkcolor=imagecolorallocate($im,250,250,250);
		imagefill($im,0,0,$bkcolor);
        @imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
		// 干扰
		for($i=0;$i<15;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
		}
		for($i=0;$i<255;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$fontcolor);
		}
		if(!is_file($fontface)) {
			$fontface = dirname(__FILE__)."/".$fontface;
		}
		for($i=0;$i<$length;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,120),mt_rand(0,120),mt_rand(0,120)); //这样保证随机出来的颜色较深。
			$codex= msubstr($code,$i,1);
			imagettftext($im,mt_rand(16,20),mt_rand(-60,60),40*$i+20,mt_rand(30,35),$fontcolor,$fontface,$codex);
		}
		Image::output($im,$type);
	}

    /**
     * 把图像转换成字符显示
     * @static
     * @access public
     * @param string $image  要显示的图像
     * @param string $type  图像类型，默认自动获取
     * @return string
     */
    public static function showASCIIImg($image,$string='',$type='')
    {
        $info  = Image::getImageInfo($image);
        if($info !== false) {
            $type = empty($type)?$info['type']:$type;
            unset($info);
            // 载入原图
            $createFun = 'ImageCreateFrom'.($type=='jpg'?'jpeg':$type);
            $im     = $createFun($image);
            $dx = imagesx($im);
            $dy = imagesy($im);
			$i	=	0;
            $out   =  '<span style="padding:0px;margin:0;line-height:100%;font-size:1px;">';
			set_time_limit(0);
            for($y = 0; $y < $dy; $y++) {
              for($x=0; $x < $dx; $x++) {
                  $col = imagecolorat($im, $x, $y);
                  $rgb = imagecolorsforindex($im,$col);
				  $str	 =	 empty($string)?'*':$string[$i++];
                  $out .= sprintf('<span style="margin:0px;color:#%02x%02x%02x">'.$str.'</span>',$rgb['red'],$rgb['green'],$rgb['blue']);
             }
             $out .= "<br>\n";
            }
            $out .=  '</span>';
            imagedestroy($im);
            return $out;
        }
        return false;
    }

    /**
     * 生成高级图像验证码
     * @static
     * @access public
     * @param string $type 图像格式
     * @param string $width  宽度
     * @param string $height  高度
     * @return string
     */
    public static function showAdvVerify($type='png',$width=180,$height=40)
    {
		$rand = range('a','z');
		shuffle($rand);
		$verifyCode	=	array_slice($rand,0,10);
        $letter = implode(" ",$verifyCode);
        Session::set('verifyCode', $verifyCode);
        $im = imagecreate($width,$height);
        $r = array(225,255,255,223);
        $g = array(225,236,237,255);
        $b = array(225,236,166,125);
        $key = mt_rand(0,3);
        $backColor = imagecolorallocate($im, $r[$key],$g[$key],$b[$key]);
		$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
        $numberColor = imagecolorallocate($im, 255,rand(0,100), rand(0,100));
        $stringColor = imagecolorallocate($im, rand(0,100), rand(0,100), 255);
		// 添加干扰
		/*
		for($i=0;$i<10;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagearc($im,mt_rand(-10,$width),mt_rand(-10,$height),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
		}
		for($i=0;$i<255;$i++){
			$fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$fontcolor);
		}*/
        imagestring($im, 5, 5, 1, "0 1 2 3 4 5 6 7 8 9", $numberColor);
        imagestring($im, 5, 5, 20, $letter, $stringColor);
        Image::output($im,$type);
    }

    /**
     
     * 生成UPC-A条形码
     * @static
     * @param string $type 图像格式
     * @param string $type 图像格式
     * @param string $lw  单元宽度
     * @param string $hi   条码高度
     * @return string
     */
    public static function UPCA($code,$type='png',$lw=2,$hi=100) 
    {
        static $Lencode = array('0001101','0011001','0010011','0111101','0100011',
                         '0110001','0101111','0111011','0110111','0001011');
        static $Rencode = array('1110010','1100110','1101100','1000010','1011100',
                         '1001110','1010000','1000100','1001000','1110100');
        $ends = '101';
        $center = '01010';
        /* UPC-A Must be 11 digits, we compute the checksum. */
        if ( strlen($code) != 11 ) { die("UPC-A Must be 11 digits."); }
        /* Compute the EAN-13 Checksum digit */
        $ncode = '0'.$code;
        $even = 0; $odd = 0;
        for ($x=0;$x<12;$x++) {
          if ($x % 2) { $odd += $ncode[$x]; } else { $even += $ncode[$x]; }
        }
        $code.=(10 - (($odd * 3 + $even) % 10)) % 10;
        /* Create the bar encoding using a binary string */
        $bars=$ends;
        $bars.=$Lencode[$code[0]];
        for($x=1;$x<6;$x++) {
          $bars.=$Lencode[$code[$x]];
        }
        $bars.=$center;
        for($x=6;$x<12;$x++) {
          $bars.=$Rencode[$code[$x]];
        }
        $bars.=$ends;
        /* Generate the Barcode Image */
        if ( $type!='gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($lw*95+30,$hi+30);
        }else {
            $im = imagecreate($lw*95+30,$hi+30);
        }
        $fg = ImageColorAllocate($im, 0, 0, 0);
        $bg = ImageColorAllocate($im, 255, 255, 255);
        ImageFilledRectangle($im, 0, 0, $lw*95+30, $hi+30, $bg);
        $shift=10;
        for ($x=0;$x<strlen($bars);$x++) {
          if (($x<10) || ($x>=45 && $x<50) || ($x >=85)) { $sh=10; } else { $sh=0; }
          if ($bars[$x] == '1') { $color = $fg; } else { $color = $bg; }
          ImageFilledRectangle($im, ($x*$lw)+15,5,($x+1)*$lw+14,$hi+5+$sh,$color);
        }
        /* Add the Human Readable Label */
        ImageString($im,4,5,$hi-5,$code[0],$fg);
        for ($x=0;$x<5;$x++) {
          ImageString($im,5,$lw*(13+$x*6)+15,$hi+5,$code[$x+1],$fg);
          ImageString($im,5,$lw*(53+$x*6)+15,$hi+5,$code[$x+6],$fg);
        }
        ImageString($im,4,$lw*95+17,$hi-5,$code[11],$fg);
        /* Output the Header and Content. */
        Image::output($im,$type);
    }

	// 生成手机号码
	static public function buildPhone() 
	{
	}
	
	// 生成邮箱图片
	static public function buildEmail($email,$rgb=array(),$filename='',$type='png') 
	{
		$mail		=	explode('@',$email);
		$user		=	trim($mail[0]);
		$mail		=	strtolower(trim($mail[1]));
		$path		=	dirname(__FILE__).'/Mail/';
		if(is_file($path.$mail.'.png')) {
			$im	= imagecreatefrompng($path.$mail.'.png');
			$user_width = imagettfbbox(9, 0, dirname(__FILE__)."/Mail/tahoma.ttf", $user);
			$x_value = (200 - ($user_width[2] + 113));
			if(empty($rgb)) {
				$color = imagecolorallocate($im, 102, 104, 104);
			}else{
				$color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
			}
			imagettftext($im, 9, 0, $x_value, 16, $color, dirname(__FILE__)."/Mail/tahoma.ttf", $user);
		}else{
			$user_width = imagettfbbox(9, 0, dirname(__FILE__)."/Mail/tahoma.ttf", $email);
			$width	=	$user_width[2]+15;
			$height	=	20;
			$im	=	imagecreate($width,20);
			$backColor = imagecolorallocate($im, 255,255,255);    //背景色（随机）
			$borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
			$pointColor = imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));                 //点颜色
			imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
			imagerectangle($im, 0, 0, $width-1, $height-1, $borderColor);
			if(empty($rgb)) {
				$color = imagecolorallocate($im, 102, 104, 104);
			}else{
				$color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
			}
			imagettftext($im, 9, 0, 5, 16, $color, dirname(__FILE__)."/Mail/tahoma.ttf", $email);
			for($i=0;$i<25;$i++){
				imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$pointColor);
			}
		}
		Image::output($im,$type,$filename);
	}

    public static function output($im,$type='png',$filename='')
    {
        header("Content-type: image/".$type);
        $ImageFun='image'.$type;
		if(empty($filename)) {
	        $ImageFun($im);
		}else{
	        $ImageFun($im,$filename);
		}
        imagedestroy($im);
    }
    
    /**
     * 图片自动加水印方法
     */
    public static function watermark($src_file, $preview=0)
    {
         $img_info = Image::getImageInfo($src_file);
         $image_width = $img_info['width'];
         $image_height = $img_info['height'];
         $image_size = $img_info['size'];
         $mime = $img_info['mime'];
         $animatedgif = 0;
         
         switch($mime)
         {
            case 'image/jpeg':
                $imagecreatefromfunc = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
                $imagefunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
                break;
            case 'image/gif':
                $imagecreatefromfunc = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
                $imagefunc = function_exists('imagegif') ? 'imagegif' : '';
                $fp = fopen($src_file, 'rb');
                $targetfilecontent = fread($fp, $image_size);
                fclose($fp);
                $animatedgif = strpos($targetfilecontent, 'NETSCAPE2.0') === false ? 0 : 1;
                break;
            case 'image/png':
                $imagecreatefromfunc = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
                $imagefunc = function_exists('imagepng') ? 'imagepng' : '';
                break;
         }//为空则匹配类型的函数不存在
         
         $mark_arr = fileRW('Mark/watermark');
         $mark_type = $mark_arr['type'];
         $mark_pos = $mark_arr['position'];
         if(empty($mark_pos)) $mark_pos = rand(1, 9);
         $shadowx = 0;
         $shadowy = 0;
         $shadow_color = '0,0,0';
  
         if($mark_type < 2)
         {
             $watermark_file = $mark_type == 1 ? DATA_DIR.'Mark/mark.png' : DATA_DIR.'Mark/mark.gif';
             if(!file_exists($watermark_file)){return false;}
             $watermarkinfo = @getimagesize($watermark_file);
             $watermark_logo = $mark_type == 1 ? @imagecreatefrompng($watermark_file) : @imagecreatefromgif($watermark_file);
             if(!$watermark_logo){return false;}
             list($logo_width, $logo_height) = $watermarkinfo;
             if($image_width < $logo_width || $image_height < $logo_height){return false;}
         }
         else
         {
             $font_path = DATA_DIR.'Mark/simhei.ttf';
             if(!file_exists($font_path)){return false;}
             $box = @imagettfbbox($mark_arr['fontsize'], 0, $font_path, $mark_arr['text']);
             $logo_width = max($box[2], $box[4]) - min($box[0], $box[6]);
             $logo_height = max($box[1], $box[3]) - min($box[5], $box[7]);
             $ax = min($box[0], $box[6]) * -1;
             $ay = min($box[5], $box[7]) * -1;
         }
         $wmwidth = $image_width - $logo_width;
         $wmheight = $image_height - $logo_height;
         if(($mark_type < 2 && is_readable($watermark_file) || $mark_type == 2) && $wmwidth > 10 && $wmheight > 10 && !$animatedgif)
         {
             switch($mark_pos)
             {
                 case 1:
                     $x = +5;
                     $y = +5;
                     break;
                 case 2:
                     $x = ($image_width - $logo_width) / 2;
                     $y = +5;
                     break;
                 case 3:
                     $x = $image_width - $logo_width - 5;
                     $y = +5;
                     break;
                 case 4:
                     $x = +5;
                     $y = ($image_height - $logo_height) / 2;
                     break;
                 case 5:
                     $x = ($image_width - $logo_width) / 2;
                     $y = ($image_height - $logo_height) / 2;
                     break;
                 case 6:
                     $x = $image_width - $logo_width - 5;
                     $y = ($image_height - $logo_height) / 2;
                     break;
                 case 7:
                     $x = +5;
                     $y = $image_height - $logo_height - 5;
                     break;
                 case 8:
                     $x = ($image_width - $logo_width) / 2;
                     $y = $image_height - $logo_height - 5;
                     break;
                 case 9:
                     $x = $image_width - $logo_width - 5;
                     $y = $image_height - $logo_height -5;
                     break;
             }
             $dst_photo = @imagecreatetruecolor($image_width, $image_height);
             $target_photo = $imagecreatefromfunc($src_file);
             imagecopy($dst_photo, $target_photo, 0, 0, 0, 0, $image_width, $image_height);
             if($mark_type == 1)
             {
                 imagecopy($dst_photo, $watermark_logo, $x, $y, 0, 0, $logo_width, $logo_height);
             }
             elseif($mark_type == 2)
             {
                 if(($shadowx || $shadowy) && $shadow_color)
                 {
                     $shadowcolorrgb = explode(',', $shadow_color);
                     $shadowcolor = imagecolorallocate($dst_photo, $shadowcolorrgb[0], $shadowcolorrgb[1], $shadowcolorrgb[2]);
                     imagettftext($dst_photo, $image_size, 0,
                     $x + $ax + $shadowx, $y + $ay + $shadowy, $shadowcolor,
                     $font_path, $mark_arr['text']);
                 }
                 $colorrgb = explode(',', $mark_arr['color']);
                 $color = imagecolorallocate($dst_photo, $colorrgb[0], $colorrgb[1], $colorrgb[2]);
                 imagettftext($dst_photo, $mark_arr['fontsize'], 0,
                 $x + $ax, $y + $ay, $color, $font_path, $mark_arr['text']);
             }
             else
             {
                 imagealphablending($watermark_logo, true);
                 imagecopymerge($dst_photo, $watermark_logo, $x, $y, 0, 0, $logo_width, $logo_height, $mark_arr['trans']);
             }
             $targetfile = !$preview ? $src_file : './watermark_tmp.jpg';
             if($mime == 'image/jpeg')
             {
                 $imagefunc($dst_photo, $targetfile, 85);
             }
             else
             {
                 $imagefunc($dst_photo, $targetfile);
             }
         }
    }

}
