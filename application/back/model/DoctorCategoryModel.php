<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/16
 * Time: 15:46
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

class DoctorCategoryModel
{
    private $table = 'skr_doctor_category';

    /**获取一级分类
     * @return array
     */
    public function getTop()
    {
        try {
            $info = Db::table($this->table)->field('category_id,category_name')->where('parent_id',0)->select();
            if ([] == $info) return jsonData(401,'暂无数据');
            return jsonData(200, '', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**添加分类
     * @param $input
     * @return array
     */
    public function add($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            $id = Db::table($this->table)->insertGetId($input);
            if ($id >0) {
                AdminLogModel::writeLog($guy, '添加医生分类', json_encode($input), '添加成功');
                return jsonData(200, '添加医生分类成功');
            }
            AdminLogModel::writeLog($guy, '添加医生分类', json_encode($input), '添加失败');
            return jsonData(400, '添加医生分类失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '添加医生分类', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取文章列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->table);

            if ($input['category_name'] != '')  $db->where('category_name', 'like', "%{$input['category_name']}%");
            if ($input['state'] !== '')         $db->where('state', $input['state']);
            if ($input['parent_id'] !== '')   $db->where('category_id', $input['parent_id']);
            $pageAll = ceil($db->count()/10);
            //获取数据
            $db = Db::table($this->table);
            if ($input['category_name'] != '')  $db->where('a.category_name', 'like', "%{$input['category_name']}%");
            if ($input['state'] !== '')         $db->where('state', $input['state']);
            if ($input['parent_id'] !== '')   $db->where('a.parent_id', $input['parent_id']);


            $table = Db::table($this->table)->field('category_name,category_id')->where('parent_id',0)->buildSql();

            $list = $db->field('a.category_id,a.parent_id,b.category_name as parent_name,a.category_name,category_cover,state')
                ->alias('a')
                ->leftJoin ([$table=>'b'],'a.parent_id = b.category_id')
                ->page($input['page'],10)->order(['parent_id'=>'asc','state' => 'desc','a.category_id' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201,'暂无数据');
            }
            foreach ($list as $k => $v) {
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
            return jsonData(200,'查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
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
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('state')->where('category_id', $input['category_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前分类');
            if ($input['state'] == $info['state']) return jsonData(402, '修改状态与当前状态一致，不能修改');
            $rtn = Db::table($this->table)->limit(1)->update($input);
            if ($rtn ==1) {
                AdminLogModel::writeLog($guy, '修改医生分类状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改医生分类状态', json_encode($input), '修改失败');
            return jsonData(403,'状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改医生分类状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取医生分类封面
     * @param $input
     * @return array
     */
    public function getCover($input)
    {
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('category_cover')->where('category_id', $input['category_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前医生分类封面');

            return jsonData(200,'获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新封面
     * @param $input
     * @param $cover
     */
    public function updateCover($input,$cover)
    {
        $guy = Session::get('info.admin_account');
        try {
            $data['category_id'] = $input['category_id'];
            $data['category_cover'] = $cover;
            $rtn = Db::table($this->table)->update($data);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新医生分类封面', json_encode(array_merge($input,['category_cover' => $cover])), '更新成功');
                return true;
            }
            AdminLogModel::writeLog($guy, '更新医生分类封面', json_encode($input), '更新失败');
            return false;
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新文医生分类封面', json_encode($input), '服务内部错误~');
            return false;
        }
    }

    /**获取单个分类信息
     * @param $input
     * @return array
     */
    public function getInfo($input)
    {
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('parent_id,category_name')->where('category_id', $input['category_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前医生分类信息');

            return jsonData(200,'获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新分类
     * @param $input
     * @return array
     */
    public function update($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            $rtn = Db::table($this->table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新医生分类', json_encode($input), '更新成功');
                return jsonData(200, '更新医生分类成功');
            }
            AdminLogModel::writeLog($guy, '更新医生分类', json_encode($input), '更新失败');
            return jsonData(400, '更新医生分类失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新医生分类', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}