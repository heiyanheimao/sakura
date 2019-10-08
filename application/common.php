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
