<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/15
 * Time: 9:37
 */

namespace app\back\model;


use think\Db;

class ConfigModel
{
    private $table = 'skr_config';

    /**获取配置
     * @param array $config_name
     * @return array|bool
     */
    public function getConfig($config_name = [])
    {
        try {
            $db = Db::table($this->table)->field('config_value,config_state');
            if ($config_name == []) {
                $rtn = $db->select();
            } else {
                $rtn = $db->where('config_name','IN',$config_name)->select();
            }
            if ([] == $rtn) return false;
            return $rtn;
        } catch (\Exception $e) {
            return false;
        }

    }
}