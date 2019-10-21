<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/21
 * Time: 11:08
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

class UserModel
{
    private $user = 'skr_user';
    private $checkinGreen = 'skr_checkin_green';
    private $checkinHealth = 'skr_checkin_health';

    /**获取列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->user);

            if ($input['user_name'] != '') {
                $db->where('user_name', 'like', "%{$input['user_name']}%");
            }
            if ($input['user_phone'] != '') {
                $db->where('user_phone', 'like', "%{$input['user_phone']}%");
            }
            if ($input['nickname'] != '') {
                $db->where('nickname', 'like', "%{$input['nickname']}%");
            }
            if ($input['state'] !== '') {
                $db->where('state', $input['state']);
            }
            if ($input['is_vip'] !== '') {
                $db->where('is_vip', $input['is_vip']);
            }
            if ($input['end'] != '') {
                $db->where('create_time', '<=', strtotime($input['end']));
            }
            if ($input['start'] != '') {
                $db->where('create_time', '>=', strtotime($input['start']));
            }
            $pageAll = ceil($db->count() / 10);
            //获取数据
            $db = Db::table($this->user);
            if ($input['user_name'] != '') {
                $db->where('user_name', 'like', "%{$input['user_name']}%");
            }
            if ($input['user_phone'] != '') {
                $db->where('user_phone', 'like', "%{$input['user_phone']}%");
            }
            if ($input['nickname'] != '') {
                $db->where('nickname', 'like', "%{$input['nickname']}%");
            }
            if ($input['state'] !== '') {
                $db->where('state', $input['state']);
            }
            if ($input['is_vip'] !== '') {
                $db->where('is_vip', $input['is_vip']);
            }
            if ($input['end'] != '') {
                $db->where('create_time', '<=', strtotime($input['end']));
            }
            if ($input['start'] != '') {
                $db->where('create_time', '>=', strtotime($input['start']));
            }

            $list = $db->field('user_id,openid,user_name,user_phone,nickname,headimgurl,sexual,is_vip,create_time,state')
                ->page($input['page'], 10)->order(['state' => 'desc', 'create_time' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201, '暂无数据');
            }
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $list[$k]['create_time']);
                switch ($list[$k]['state']) {
                    case 0:
                        $list[$k]['state'] = '已禁用';
                        break;
                    case 1:
                        $list[$k]['state'] = '已启用';
                        break;
                    case -1:
                        $list[$k]['state'] = '已删除';
                        break;
                }
                switch ($list[$k]['sexual']) {
                    case 0:
                        $list[$k]['sexual'] = '女';
                        break;
                    case 1:
                        $list[$k]['sexual'] = '男';
                        break;
                }
                switch ($list[$k]['is_vip']) {
                    case 0:
                        $list[$k]['is_vip'] = '否';
                        break;
                    case 1:
                        $list[$k]['is_vip'] = '是';
                        break;
                }
            }
            return jsonData(200, '查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改用户状态
     * @param $input
     * @return array
     */
    public function changeState($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询用户当前状态
            $info = Db::table($this->user)->field('state')->where('user_id', $input['user_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前用户信息');
            }
            if ($input['state'] == $info['state']) {
                return jsonData(402, '修改状态与当前状态一致，不能修改');
            }
            if (-1 == $info['state']) {
                return jsonData(403, '删除状态不能进行其他修改');
            }
            $rtn = Db::table($this->user)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改用户状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改用户状态', json_encode($input), '修改失败');
            return jsonData(403, '状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改用户状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取预约信息
     * @param $input
     * @return array
     */
    public function getCheckin($input)
    {
        try {
            //获取数据
            $db         = Db::table($this->checkinGreen);
            $greenList  = $db->field('checkin_name,checkin_phone,checkin_time,state,cancel_reason')
                ->where('user_id', $input['user_id'])
                ->order(['checkin_time' => 'desc'])->select();
            $db         = Db::table($this->checkinHealth);
            $healthList = $db->field('checkin_name,checkin_phone,checkin_time,state,cancel_reason')
                ->where('user_id', $input['user_id'])
                ->order(['checkin_time' => 'desc'])->select();
            foreach ($greenList as &$greenValue) {
                $greenValue['checkin_time'] = date('Y-m-d H:i:s', $greenValue['checkin_time']);
                $greenValue['checkin_type'] = '绿色预约';
                switch ($greenValue['state']) {
                    case 0:
                        $greenValue['state'] = '已取消';
                        break;
                    case 1:
                        $greenValue['state'] = '已确认';
                        break;
                    case 2:
                        $greenValue['state'] = '未确认';
                        break;
                }
            }
            foreach ($healthList as &$healthValue) {
                $healthValue['checkin_time'] = date('Y-m-d H:i:s', $healthValue['checkin_time']);
                $healthValue['checkin_type'] = '健康预约';
                switch ($healthValue['state']) {
                    case 0:
                        $healthValue['state'] = '已取消';
                        break;
                    case 1:
                        $healthValue['state'] = '已确认';
                        break;
                    case 2:
                        $healthValue['state'] = '未确认';
                        break;
                }
            }
            return jsonData(200, '查询成功', array_merge($greenList, $healthList));
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改用户名称
     * @param $input
     * @return array
     */
    public function editUserName($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询用户当前状态
            $info = Db::table($this->user)->field('user_name')->where('user_id', $input['user_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前用户信息');
            }
            if ($input['user_name'] == $info['user_name']) {
                return jsonData(402, '名字没有发生变化，请您仔细检查');
            }
            $rtn = Db::table($this->user)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改用户名称', json_encode($input), '修改成功');
                return jsonData(200, '修改用户名称成功');
            }
            AdminLogModel::writeLog($guy, '修改用户名称', json_encode($input), '修改失败');
            return jsonData(403, '修改用户名称失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改用户名称', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改用户手机号
     * @param $input
     * @return array
     */
    public function editUserPhone($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询用户当前状态
            $info = Db::table($this->user)->field('user_phone')->where('user_id', $input['user_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前用户信息');
            }
            if ($input['user_phone'] == $info['user_phone']) {
                return jsonData(402, '手机号没有发生变化，请您仔细检查');
            }
            $rtn = Db::table($this->user)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改用户手机号', json_encode($input), '修改成功');
                return jsonData(200, '修改用户手机号成功');
            }
            AdminLogModel::writeLog($guy, '修改用户手机号', json_encode($input), '修改失败');
            return jsonData(403, '修改用户手机号失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改用户手机号', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}