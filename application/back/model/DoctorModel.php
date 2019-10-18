<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/17
 * Time: 14:36
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

class DoctorModel
{
    private $table = 'skr_doctor';
    private $category_table = 'skr_doctor_category';

    /**获取分类
     * @return array
     */
    public function getCategoryInfo()
    {
        try {
            $info = Db::table($this->category_table)->field('category_id,category_name')->where('parent_id', 'neq',
                0)->select();
            if ([] == $info) {
                return jsonData(401, '暂无数据');
            }
            return jsonData(200, '', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**添加医生
     * @param $input
     * @return array
     */
    public function add($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            $id = Db::table($this->table)->insertGetId($input);
            if ($id > 0) {
                AdminLogModel::writeLog($guy, '添加医生', json_encode($input), '添加成功');
                return jsonData(200, '添加医生成功');
            }
            AdminLogModel::writeLog($guy, '添加医生', json_encode($input), '添加失败');
            return jsonData(400, '添加医生失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '添加医生', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取医生列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->table);

            if ($input['doctor_name'] != '') {
                $db->where('doctor_name', 'like', "%{$input['doctor_name']}%");
            }
            if ($input['doctor_position'] != '') {
                $db->where('doctor_name', 'like', "%{$input['doctor_position']}%");
            }
            if ($input['state'] !== '') {
                $db->where('state', $input['state']);
            }
            if ($input['category_id'] !== '') {
                $db->where('category_id', $input['category_id']);
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
            if ($input['doctor_name'] != '') {
                $db->where('doctor_name', 'like', "%{$input['doctor_name']}%");
            }
            if ($input['doctor_position'] != '') {
                $db->where('doctor_name', 'like', "%{$input['doctor_position']}%");
            }
            if ($input['state'] !== '') {
                $db->where('a.state', $input['state']);
            }
            if ($input['category_id'] !== '') {
                $db->where('a.category_id', $input['category_id']);
            }
            if ($input['end'] != '') {
                $db->where('create_time', '<=', strtotime($input['end']));
            }
            if ($input['start'] != '') {
                $db->where('create_time', '>=', strtotime($input['start']));
            }

            $list = $db->field('b.category_name,doctor_id,a.category_id,doctor_name,doctor_position,doctor_cover,a.content,create_time,a.state')
                ->alias('a')
                ->join([$this->category_table => 'b'], 'a.category_id = b.category_id')
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
            }
            return jsonData(200, '查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
//            var_dump($e->getMessage());
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改医生分类状态
     * @param $input
     * @return array
     */
    public function changeState($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询产品当前状态
            $info = Db::table($this->table)->field('state')->where('doctor_id', $input['doctor_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前医生');
            }
            if ($input['state'] == $info['state']) {
                return jsonData(402, '修改状态与当前状态一致，不能修改');
            }
            $rtn = Db::table($this->table)->limit(1)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改医生状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改医生状态', json_encode($input), '修改失败');
            return jsonData(403, '状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改医生状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取医生分类封面
     * @param $input
     * @return array
     */
    public function getCover($input)
    {
        try {
            //查询产品当前状态
            $info = Db::table($this->table)->field('doctor_cover')->where('doctor_id', $input['doctor_id'])->find();
            if (null == $info) {
                return jsonData(401, '未找到当前医生分类封面');
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
            $data['doctor_id']    = $input['doctor_id'];
            $data['doctor_cover'] = $cover;
            $rtn                  = Db::table($this->table)->update($data);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新医生封面', json_encode(array_merge($input, ['doctor_cover' => $cover])),
                    '更新成功');
                return true;
            }
            AdminLogModel::writeLog($guy, '更新医生封面', json_encode($input), '更新失败');
            return false;
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新文医生封面', json_encode($input), '服务内部错误~');
            return false;
        }
    }

    /**获取单个分类信息
     * @param $input
     * @return array
     */
    public function getInfos($input)
    {
        try {
            //获取医生个人信息
            $doctorInfo = Db::table($this->table)->field('doctor_id,category_id,doctor_name,doctor_position,content')
                ->where('doctor_id', $input['doctor_id'])->find();
            if (null == $doctorInfo) {
                return jsonData(401, '未找到当前医生信息');
            }
            //获取分类信息
            $categoryInfo = Db::table($this->category_table)->field('category_id,category_name')->where('parent_id',
                'neq', 0)->select();
            if ([] == $categoryInfo) {
                return jsonData(402, '未找到分类信息');
            }
            return jsonData(200, '获取成功', ['doctor' => $doctorInfo, 'category' => $categoryInfo]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新医生
     * @param $input
     * @return array
     */
    public function update($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            $rtn = Db::table($this->table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新医生', json_encode($input), '更新成功');
                return jsonData(200, '更新医生成功');
            }
            AdminLogModel::writeLog($guy, '更新医生', json_encode($input), '更新失败');
            return jsonData(400, '更新医生分类失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新医生', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}