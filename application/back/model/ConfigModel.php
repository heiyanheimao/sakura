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
                $rtn = $db->where('config_name', 'IN', $config_name)->select();
            }
            if ([] == $rtn) {
                return false;
            }
            return $rtn;
        } catch (\Exception $e) {
            return false;
        }

    }

    /**获取后台可以配置的列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->table);

            if ($input['config_name'] != '') {
                $db->where('config_name', 'like', "%{$input['config_name']}%");
            }
            if ($input['config_state'] !== '') {
                $db->where('config_state', $input['config_state']);
            }
            $db->where('config_auth', 1);
            $pageAll = ceil($db->count() / 10);
            //获取数据
            $db = Db::table($this->table);
            if ($input['config_name'] != '') {
                $db->where('config_name', 'like', "%{$input['config_name']}%");
            }
            if ($input['config_state'] !== '') {
                $db->where('config_state', $input['config_state']);
            }
            $db->where('config_auth', 1);
            $list = $db->field('config_id,config_name,config_value,config_desc,config_state')
                ->page($input['page'], 10)->order(['config_state' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201, '暂无数据');
            }
            foreach ($list as $k => $v) {
                switch ($list[$k]['config_state']) {
                    case 0:
                        $list[$k]['state'] = '已禁用';
                        break;
                    case 1:
                        $list[$k]['state'] = '已启用';
                        break;
                }
            }
            return jsonData(200, '查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
//            var_dump($e->getMessage());
            return jsonData(301, '服务内部错误~');
        }
    }
}