<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**准备json返回的数据
 * @param int $code
 * @param string $msg
 * @param array $data
 * @return array
 */
function jsonData($code = 200, $msg = '', $data = [])
{
    return [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
    ];
}

/**判断是否是正整数
 * @param string $num
 * @param bool $bool
 * @return bool
 */
function isPosInt($num,$bool = false)
{
    if ($bool == false) {
        if ($num < 1 || !is_numeric($num) || strpos($num, '.')) return false;
    } else {
        if ($num < 0 || !is_numeric($num) || strpos($num, '.')) return false;
    }
    return true;
}


/**判断是否是正小数
 * @param string $num
 * @param bool $bool
 * @return bool
 */
function isPosFloat($num,$bool = false)
{
    if ($bool == false) {
        if ($num < 1 || !is_numeric($num)) return false;
    } else {
        if ($num < 0 || !is_numeric($num)) return false;
    }
    return true;
}

/**判断时间
 * @param string $time
 * @param string $format
 * @return bool
 */
function validateDate($time, $format = 'Y-m-d H:i:s')
{
    $unixTime = strtotime($time);
    if (!$unixTime) return false;
    if (date($format, $unixTime) == $time) return true;
    return false;
}

/**过滤js标签
 * @param string $str
 */
function filterScript($str = '')
{
    return str_replace(['<script',"</script>"],['&lt;script','&lt;script&gt;'], $str);
}