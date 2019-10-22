<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/18
 * Time: 10:35
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

class HotelModel
{
    private $table = 'skr_hotel';

    /**添加酒店
     * @param $input
     * @return array
     */
    public function add($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            $id = Db::table($this->table)->insertGetId($input);
            if ($id > 0) {
                AdminLogModel::writeLog($guy, '添加酒店', json_encode($input), '添加成功');
                return jsonData(200, '添加酒店成功');
            }
            AdminLogModel::writeLog($guy, '添加酒店', json_encode($input), '添加失败');
            return jsonData(400, '添加酒店失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '添加酒店', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->table);

            if ($input['hotel_name'] != '') {
                $db->where('hotel_name', 'like', "%{$input['hotel_name']}%");
            }
            if ($input['hotel_phone'] != '') {
                $db->where('hotel_phone', 'like', "{$input['hotel_phone']}%");
            }
            if ($input['state'] !== '') {
                $db->where('state', $input['state']);
            }
            if ($input['allow_order'] !== '') {
                $db->where('allow_order', $input['allow_order']);
            }
            if ($input['end'] != '') {
                $db->where('create_time', '<=', strtotime($input['end']));
            }
            if ($input['start'] != '') {
                $db->where('create_time', '>=', strtotime($input['start']));
            }
            $pageAll = ceil($db->count() / 10);
            //获取数据
            $db = Db::table($this->table);
            if ($input['hotel_name'] != '') {
                $db->where('hotel_name', 'like', "%{$input['hotel_name']}%");
            }
            if ($input['hotel_phone'] != '') {
                $db->where('hotel_phone', 'like', "{$input['hotel_phone']}%");
            }
            if ($input['state'] !== '') {
                $db->where('state', $input['state']);
            }
            if ($input['allow_order'] !== '') {
                $db->where('allow_order', $input['allow_order']);
            }
            if ($input['end'] != '') {
                $db->where('create_time', '<=', strtotime($input['end']));
            }
            if ($input['start'] != '') {
                $db->where('create_time', '>=', strtotime($input['start']));
            }

            $list = $db->field('hotel_id,hotel_name,hotel_address,hotel_cover,content,hotel_phone,order_start,order_end,entry_time,create_time,state,allow_order')
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
                switch ($list[$k]['allow_order']) {
                    case 0:
                        $list[$k]['allow_order'] = '已禁止';
                        break;
                    case 1:
                        $list[$k]['allow_order'] = '已允许';
                        break;
                }
            }
            return jsonData(200, '查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改酒店状态
     * @param $input
     * @return array
     */
    public function changeState($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询酒店当前状态
            $info = Db::table($this->table)->field('state')->where('hotel_id', $input['hotel_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前酒店');
            }
            if ($input['state'] == $info['state']) {
                return jsonData(402, '修改状态与当前状态一致，不能修改');
            }
            $rtn = Db::table($this->table)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改酒店状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改酒店状态', json_encode($input), '修改失败');
            return jsonData(403, '状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改酒店状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改酒店下单状态
     * @param $input
     * @return array
     */
    public function changeAllowOrder($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询酒店当前状态
            $info = Db::table($this->table)->field('allow_order')->where('hotel_id', $input['hotel_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前酒店');
            }
            if ($input['allow_order'] == $info['allow_order']) {
                return jsonData(402, '修改状态与当前状态一致，不能修改');
            }
            $rtn = Db::table($this->table)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改酒店下单状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改酒店下单状态', json_encode($input), '修改失败');
            return jsonData(403, '状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改酒店下单状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取酒店封面
     * @param $input
     * @return array
     */
    public function getCover($input)
    {
        try {
            //查询产品当前状态
            $info = Db::table($this->table)->field('hotel_cover')->where('hotel_id', $input['hotel_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前酒店封面');
            }

            return jsonData(200, '获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新封面
     * @param $input
     * @param $cover
     * @return bool
     */
    public function updateCover($input, $cover)
    {
        $guy = Session::get('info.admin_account');
        try {
            $data['hotel_id']    = $input['hotel_id'];
            $data['hotel_cover'] = $cover;

            $rtn = Db::table($this->table)->update($data);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新酒店封面', json_encode(array_merge($input, ['doctor_cover' => $cover])),
                    '更新成功');
                return true;
            }
            AdminLogModel::writeLog($guy, '更新酒店封面', json_encode($input), '更新失败');
            return false;
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新酒店封面', json_encode($input), '服务内部错误~');
            return false;
        }
    }

    /**获取单个酒店信息
     * @param $input
     * @return array
     */
    public function getInfo($input)
    {
        try {
            //获取医生个人信息
            $info = Db::table($this->table)->field('hotel_name,hotel_address,content,hotel_phone')
                ->where('hotel_id', $input['hotel_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前信息');
            }
            return jsonData(200, '获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新酒店信息
     * @param $input
     * @return array
     */
    public function edit($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            $rtn = Db::table($this->table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新酒店信息', json_encode($input), '更新成功');
                return jsonData(200, '更新酒店信息成功');
            }
            AdminLogModel::writeLog($guy, '更新酒店信息', json_encode($input), '更新失败');
            return jsonData(400, '更新酒店信息失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新酒店信息', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新时间
     * @param $input
     * @return array
     */
    public function setTime($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            $info = Db::table($this->table)->field('order_start,order_end,entry_time')
                ->where('hotel_id', $input['hotel_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前信息');
            }
            if ($info['order_start'] == $input['order_start'] && $info['order_end'] == $input['order_end'] && $info['entry_time'] == $input['entry_time']) {
                return jsonData(402, '数据没有任何更新!');
            }
            $rtn = Db::table($this->table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新时间', json_encode($input), '更新成功');
                return jsonData(200, '更新时间成功');
            }
            AdminLogModel::writeLog($guy, '更新时间', json_encode($input), '更新失败');
            return jsonData(400, '更新时间失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新时间', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}