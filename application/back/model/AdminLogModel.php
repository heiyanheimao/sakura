<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/10
 * Time: 9:35
 */

namespace app\back\model;


use think\Db;
use think\facade\Env;

class AdminLogModel
{
    private static $table = 'skr_admin_log';

    /**写入日志
     * @param string $guy 操作人
     * @param string $operation 执行动作
     * @param string $json 执行数据
     * @param string $rtn 执行结果
     */
    public static function writeLog($guy,$operation,$json,$rtn)
    {
        try {
            $data['log_content'] = $guy . '执行' . $operation . ',' .$rtn . ',数据：' . $json;
            $data['create_time'] = time();
            Db::table(self::$table)
                ->insertGetId($data);
        } catch (\Exception $e) {
            $str = $guy . '执行' . $operation . '异常' . '数据：' . $json . '执行时间:' . date('Y-m-d H:i:s') . PHP_EOL;
            file_put_contents(Env::get('runtime_path') . 'log/error.log', $str, FILE_APPEND);
        }

    }
}