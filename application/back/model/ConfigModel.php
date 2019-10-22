<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/15
 * Time: 9:37
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

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

    /**修改配置状态
     * @param $input
     * @return array
     */
    public function changeState($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询配置当前状态
            $info = Db::table($this->table)->field('config_state')->where('config_id', $input['config_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前配置');
            }
            if ($input['config_state'] == $info['config_state']) {
                return jsonData(402, '修改状态与当前状态一致，不能修改');
            }
            $rtn = Db::table($this->table)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改配置状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改配置状态', json_encode($input), '修改失败');
            return jsonData(403, '状态修改失败');
        } catch (\Exception $e) {
//            var_dump($e->getMessage());
            AdminLogModel::writeLog($guy, '修改配置状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改配置描述
     * @param $input
     * @return array
     */
    public function editDesc($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询配置当前状态
            $info = Db::table($this->table)->field('config_desc')->where('config_id', $input['config_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前配置');
            }
            if ($input['config_desc'] == $info['config_desc']) {
                return jsonData(402, '修改前与当前一致，不能修改');
            }
            $rtn = Db::table($this->table)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改配置描述', json_encode($input), '修改成功');
                return jsonData(200, '描述修改成功');
            }
            AdminLogModel::writeLog($guy, '修改配置描述', json_encode($input), '修改失败');
            return jsonData(403, '描述修改失败');
        } catch (\Exception $e) {
//            var_dump($e->getMessage());
            AdminLogModel::writeLog($guy, '修改配置描述', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改配置值
     * @param $input
     * @return array
     */
    public function editValue($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询配置当前状态
            $info = Db::table($this->table)->field('config_value')->where('config_id', $input['config_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前配置');
            }
            if ($input['config_value'] == $info['config_value']) {
                return jsonData(402, '修改前与当前一致，不能修改');
            }
            $rtn = Db::table($this->table)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改配置值', json_encode($input), '修改成功');
                return jsonData(200, '配置值修改成功');
            }
            AdminLogModel::writeLog($guy, '修改配置值', json_encode($input), '修改失败');
            return jsonData(403, '配置值修改失败');
        } catch (\Exception $e) {
//            var_dump($e->getMessage());
            AdminLogModel::writeLog($guy, '修改配置值', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取Icon
     * @param $input
     * @return array
     */
    public function getIcon($input)
    {
        try {
            //查询产品当前状态
            $info = Db::table($this->table)->field('config_value')->where('config_id', $input['config_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前icon');
            }

            return jsonData(200, '获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新Icon
     * @param $input
     * @param $icon
     * @return bool
     */
    public function updateIcon($input, $icon)
    {
        $guy = Session::get('info.admin_account');
        try {
            $data['config_id']    = $input['config_id'];
            $data['config_value'] = $icon;

            $rtn = Db::table($this->table)->update($data);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新Icon', json_encode(array_merge($input, ['icon' => $icon])),
                    '更新成功');
                return true;
            }
            AdminLogModel::writeLog($guy, '更新Icon', json_encode($input), '更新失败');
            return false;
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新Icon', json_encode($input), '服务内部错误~');
            return false;
        }
    }
}