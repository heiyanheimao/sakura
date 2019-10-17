<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/11
 * Time: 10:43
 */

namespace app\back\model;


use think\Db;
use think\exception\DbException;
use think\facade\Session;

class CheckinModel
{
    private $greenTable = 'skr_checkin_green';
    private $healthTable = 'skr_checkin_health';
    /**获取列表
     * @param $input
     * @return array|bool
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $table = null;
            switch ($input['checkin_type']) {
                case '1':
                    $table = $this->greenTable;
                    break;
                case '2':
                    $table = $this->healthTable;
                    break;
            }
            $db = Db::table($table);
            if ($input['checkin_name'] != '') $db->where('checkin_name', 'like', "%{$input['checkin_name']}%");
            if ($input['checkin_phone'] != '') $db->where('checkin_phone', 'like', "{$input['checkin_phone']}%");
            if ($input['start'] != '') $db->where('checkin_time', '>=', strtotime($input['start']));
            if ($input['end'] != '') $db->where('checkin_time', '<=', strtotime($input['end']));
            if ($input['state'] !== '') $db->where('state', $input['state']);
            $pageAll = ceil($db->count()/10);
            //获取数据
            $db = Db::table($table);
            if ($input['checkin_name'] != '') $db->where('checkin_name', 'like', "%{$input['checkin_name']}%");
            if ($input['checkin_phone'] != '') $db->where('checkin_phone', 'like', "{$input['checkin_phone']}%");
            if ($input['start'] != '') $db->where('checkin_time', '>=', strtotime($input['start']));
            if ($input['end'] != '') $db->where('checkin_time', '<=', strtotime($input['end']));
            if ($input['state'] !== '') $db->where('state', $input['state']);

            $list = $db->field('checkin_id,user_id,checkin_name,checkin_phone,checkin_time,state,cancel_reason')
                ->page($input['page'],10)->order(['state' => 'desc','checkin_time' => 'desc'])->select();
            return ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']];
        } catch (DbException $e) {
            return false;
        }
    }
    /**修改状态
     * @param $input
     * @return array
     */
    public function changeStatus($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //获取当前状态
            $table = null;
            switch ($input['type']) {
                case '绿色预约':
                    $table = $this->greenTable;
                    break;
                case '健康预约':
                    $table = $this->healthTable;
                    break;
            }
            $info = Db::table($table)
                ->field('state')
                ->where('checkin_id', $input['checkin_id'])
                ->find();
            if (!$info) return jsonData(401, '未找到当前预约,无法操作');
            if ($info['state'] == 0) {
                return jsonData(402, '预约已取消，不能进行当前操作');
            } elseif ($info['state'] == 1 && $input['state']) {
                return jsonData(403, '预约已确认，不能再次确认');
            }
            unset($input['type']);
            $rtn = Db::table($table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '预约状态修改', json_encode($input), '修改成功');
                switch ($input['state']) {
                    case 2:
                        $state = '未确认';
                        break;
                    case 1:
                        $state = '已确认';
                        break;
                    case 0:
                        $state = '已取消';
                        break;
                }
                return jsonData(200, '预约状态修改成功',['state' => $state, 'cancel_reason' => $input['cancel_reason']]);
            }
            AdminLogModel::writeLog($guy, '预约状态修改', json_encode($input), '修改失败');
            return jsonData(403, '预约状态修改失败');
        } catch (DbException $e) {
            AdminLogModel::writeLog($guy, '预约状态修改', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '预约状态修改', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**
     * @param $input
     */
    public function update($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //获取当前状态
            $table = null;
            switch ($input['type']) {
                case '1':
                    $table = $this->greenTable;
                    break;
                case '2':
                    $table = $this->healthTable;
                    break;
            }
            $info = Db::table($table)
                ->field('state')
                ->where('checkin_id', $input['checkin_id'])
                ->find();
            if (!$info) return jsonData(401, '未找到当前预约,无法操作');
            if ($info['state'] == 0) return jsonData(402, '预约已取消，不能进行当前操作');

            unset($input['type']);
            $rtn = Db::table($table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '预约信息修改', json_encode($input), '修改成功');
                return jsonData(200, '预约信息修改成功');
            }
            AdminLogModel::writeLog($guy, '预约信息修改', json_encode($input), '修改失败');
            return jsonData(403, '预约信息修改失败');
        } catch (DbException $e) {
            AdminLogModel::writeLog($guy, '预约信息修改', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '预约信息修改', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}