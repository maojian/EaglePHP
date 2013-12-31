<?php
/**
 * 日期时间操作类
 * @copyright EaglePHP Team
 * @link www.eaglephp.com
 * @author maojianlw@139.com
 * @since 2012-10-11
 */

class Date
{    
    
    /**
     * 获取或者设置时区
     * 
     * @param int $timezone 时区
     * @return string | bool
     */
    public static function timeZone($timezone='')
    {
        if($timezone)
        {
            return function_exists('date_default_timezone_set') ? date_default_timezone_set($timezone) : putenv("TZ={$timezone}"); 
        }
        else
        {
            return function_exists('date_default_timezone_get') ? date_default_timezone_get() : date('e');
        }
    }
    
    /**
     * 获得时间戳
     * 
     * @param int $dateTime 默认为空，则以当前时间戳返回
     * @return int
     */
    public static function getTimeStamp($dateTime = null)
    {
        return $dateTime ? is_numeric($dateTime) ? $dateTime : strtotime($dateTime) : time();
    }
    
    /**
     * 格式化输出
     * 
     * @param string $format 目标格式，默认为空则以Y-m-d H:i:s格式输出
     * @param int $dateTime Unix时间戳,默认为空则获取当前时间戳
     * @return string
     */
    public static function format($format = null, $dateTime = null)
    {
        return date($format ? $format : 'Y-m-d H:i:s', self::getTimeStamp($dateTime));
    }
    
    /**
     * 获取星期
     * 
     * @param int $week 星期，默认为当前时间获取
     * @return string
     */
    public static function getWeek($week = null)
    {
        $week = $week ? $week : self::format('w');
        $weekArr = array('星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六');
        return $weekArr[$week];
    }
    
    /**
     * 判断是否为闰年
     * 
     * @param int $year 年份，默认为当前年份
     * @return bool
     */
    public static function isLeapYear($year = null)
    {
        $year = $year ? $year : self::format('Y');
        return ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0);
    }
    
    /**
     * 获取一年中有多少天
     * @param int $year 年份，默认为当前年份
     */
    public static function getDaysInYear($year = null)
    {
        $year = $year ? $year : self::format('Y');
        return self::isLeapYear($year) ? 366 : 365;
    }
    
    /**
     * 获取一天中的时段
     * 
	 * @param int $hour 小时，默认为当前小时
     * @return string
     */
    public static function getPeriodOfTime($hour = null)
    {
        $hour = $hour ? $hour : self::format('G');
        $period = null;
        if($hour >=0 && $hour <6)
        {
            $period = '凌晨';
        }
        elseif($hour >=6 && $hour <8)
        {
            $period = '早上';
        }
        elseif($hour >=8 && $hour <11)
        {
            $period = '上午';
        }
        elseif($hour >=11 && $hour <13)
        {
            $period = '中午';
        }
        elseif($hour >=13 && $hour <15)
        {
            $period = '响午';
        }
        elseif($hour >=15 && $hour <18)
        {
            $period = '下午';
        }
        elseif($hour >=18 && $hour <20)
        {
            $period = '傍晚';
        }
        elseif($hour >=20 && $hour <22)
        {
            $period = '晚上';
        }
        elseif($hour >=22 && $hour <=23)
        {
            $period = '深夜';
        }
        return $period;
    }
    
    
    /**
     * 日期数字转中文，适用于日、月、周
     * @param int $day 日期数字，默认为当前日期
     * @return string
     */
    public static function numberToChinese($number)
    {
        $chineseArr = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
        $chineseStr = null;
        if($number < 10)
        {
            $chineseStr = $chineseArr[$number-1];
        } 
        elseif($number < 20)
        {
            $chineseStr = '十'.$chineseArr[$number-11];
        }
        elseif($number < 30)
        {
            $chineseStr = '二十'.$chineseArr[$number-21];
        }
        else
        {
            $chineseStr = '三十'.$chineseArr[$number-31];
        }
        return $chineseStr;
    }
    
    
    /**
     * 年份数字转中文
     * 
     * @param int $year 年份数字，默认为当前年份
     * @return string
     */
    public static function yearToChinese($year = null, $flag = false)
    {
        $year = $year ? intval($year) : self::format('Y');
        $data = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chineseStr = null;
        for($i=0; $i<4; $i++)
        {
            $chineseStr .= $data[substr($year,$i,1)];
        }
        return $flag ? '公元'.$chineseStr : $chineseStr;
    }
   
    
    /**
     * 获取日期所属的星座、干支、生肖
     * 
     * @param string $type 获取信息类型（SX：生肖、GZ：干支、XZ：星座）
     * @return string
     */
    public static function dateInfo($type, $date = null)
    {
        $year = self::format('Y', $date);
        $month = self::format('m', $date);
        $day = self::format('d', $date);
        $result = null;
        switch($type)
        {
            case 'SX':
                $data = array('鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪');
                $result = $data[($year-4)%12];
                break;
            case 'GZ':
                $data = array(
                    array('甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'),
                    array('子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥')
                );
                $num = $year - 1900 + 36;
                $result = $data[0][$num%10].$data[1][$num%12];
                break;
            case 'XZ':
                $data = array('摩羯', '宝瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '处女', '天秤', '天蝎', '射手');
                $zone = array(1222, 122, 222, 321, 421, 522, 622, 722, 822, 922, 1022, 1122, 1222);
                if((100 * $month + $day) >= $zone[0] || (100 * $month + $day) < $zone[1])
                {
                    $i=0;
                }
                else
                {
                    for($i=1; $i<12; $i++)
                    {
                        if((100* $month + $day) >= $zone[$i] && (100 * $month + $day) < $zone[$i+1]) break;
                    }
                }
                $result = $data[$i].'座';
                break;
        }
        return $result;
    }
    
    
    /**
     * 获取两个日期的差
     * 
     * @param string $interval 日期差的间隔类型，（Y：年、M：月、W：星期、D：日期、H：时、N：分、S：秒）
     * @param int $startDateTime 开始日期
     * @param int $endDateTime 结束日期
     * @return int
     */
    public static function dateDiff($interval, $startDateTime, $endDateTime)
    {
        $diff = self::getTimeStamp($endDateTime) - self::getTimeStamp($startDateTime);
        $result = 0;
        switch ($interval)
        {
            case 'Y'://年
                $result = bcdiv($diff, 60 * 60 * 24 * 365);
                break;
            case 'M'://月
                $result = bcdiv($diff, 60 * 60 * 24 * 30);
                break;
            case 'W'://星期
                $result = bcdiv($diff, 60 * 60 * 24 * 7);
                break;
            case 'D'://日
                $result = bcdiv($diff, 60 * 60 * 24);
                break;
            case 'H'://时
                $result = bcdiv($diff, 60 * 60);
                break;
            case 'N'://分
                $result = bcdiv($diff, 60);
                break;
            case 'S'://秒
            default:
                $result = $diff;
                break;
        }
        return $result;
    }
    
    
    /**
     * 返回指定日期在一段时间间隔时间后的日期
     * 
     * @param string $interval 时间间隔类型，（Y：年、Q：季度、M：月、W：星期、D：日期、H：时、N：分、S：秒）
     * @param int $value 时间间隔数值，数值为正数获取未来的时间，数值为负数获取过去的时间
     * @param string $dateTime 日期
     * @param string $format 返回的日期转换格式
     * @return string 返回追加后的日期
     */
    public static function dateAdd($interval, $value, $dateTime = null, $format = null)
    {
        $dateTime = $dateTime ? $dateTime : self::format();
        $date = getdate(self::getTimeStamp($dateTime));
        switch ($interval)
        {
            case 'Y'://年
                $date['year'] += $value;
                break;
            case 'Q'://季度
                $date['mon'] += ($value * 3);
                break;
            case 'M'://月
                $date['mon'] += $value;
                break;
            case 'W'://星期
                $date['mday'] += ($value * 7);
                break;
            case 'D'://日
                $date['mday'] += $value;
                break;
            case 'H'://时
                $date['hours'] += $value;
                break;
            case 'N'://分
                $date['minutes'] += $value;
                break;
            case 'S'://秒
            default:
                $date['seconds'] += $value;
                break;
        }
        return self::format($format, mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], $date['mday'], $date['year']));
    }
    
    
    /**
     * 根据年份获取每个月的天数
     * 
     * @param int $year 年份
     * @return array 月份天数数组
     */
    public static function getDaysByMonthsOfYear($year = null)
    {
        $year = $year ? $year : self::format('Y');
        $months = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if(self::isLeapYear($year)) $months[1] = 29;
        return $months;
    }
    
    
    /**
     * 返回某年的某个月有多少天
     * 
     * @param int $month 月份
     * @param int $year 年份
     * @return int 月份天数
     */
    public static function getDaysByMonth($month, $year)
    {
        $months = self::getDaysByMonthsOfYear($year);
        $value = $months[$month - 1];
        return !$value ? 0 : $value;
    }
    
    
	/**
     * 获取年份的第一天
     * 
     * @param int $year 年份
     * @param int $format 返回的日期格式
     * @return string 返回的日期
     */
    public static function firstDayOfYear($year = null, $format = 'Y-m-d')
    {
        $year = $year ? $year : self::format('Y');
        return self::format($format, mktime(0, 0, 0, 1, 1, $year));
    }
    
    
    /**
     * 获取年份最后一天
     * 
     * @param int $year 年份
     * @param int $format 返回的日期格式
     * @return string 返回的日期
     */
    public static function lastDayOfYear($year = null, $format = 'Y-m-d')
    {
        $year = $year ? $year : self::format('Y');
        return self::format($format, mktime(0, 0, 0, 1, 0, $year+1));
    }
    
    
	/**
     * 获取月份的第一天
     * 
     * @param int $month 月份
     * @param int $year 年份
     * @param int $format 返回的日期格式
     * @return string 返回的日期
     */
    public static function firstDayOfMonth($month = null, $year = null, $format = 'Y-m-d')
    {
        $year = $year ? $year : self::format('Y');
        $month = $month ? $month : self::format('m');
        return self::format($format, mktime(0, 0, 0, $month, 1, $year));
    }
    
    
    /**
     * 获取月份最后一天
     * 
     * @param int $month 月份
     * @param int $year 年份
     * @param int $format 返回的日期格式
     * @return string 返回的日期
     */
    public static function lastDayOfMonth($month = null, $year = null, $format = 'Y-m-d')
    {
        $year = $year ? $year : self::format('Y');
        $month = $month ? $month : self::format('m');
        return self::format($format, mktime(0, 0, 0, $month+1, 0, $year));
    }
    
    
    /**
     * 获取两个日期之间范围
     * 
     * @param string $startDateTime
     * @param string $endDateTime
     * @param string $format
     * @return array 返回日期数组
     */
    public static function getDayRangeInBetweenDate($startDateTime, $endDateTime, $sort = false, $format = 'Y-m-d')
    {
        $startDateTime = self::getTimeStamp($startDateTime);
        $endDateTime = self::getTimeStamp($endDateTime);
        $num = ($endDateTime - $startDateTime) / 86400;
        $dateArr = array();
        for($i=0; $i<=$num; $i++)
        {
            $dateArr[] = self::format($format, $startDateTime + 86400 * $i);
        }
        return $sort ? array_reverse($dateArr) : $dateArr;
    }
 
    
}


if( !function_exists('bcdiv') )
{
    function bcdiv( $first, $second, $scale = 0 )
    {
        $res = $first / $second;
        return round( $res, $scale );
    }
}