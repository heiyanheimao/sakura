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
            $info = Db::table($this->category_table)->field('category_id,category_name')->where('parent_id','neq',0)->select();
            if ([] == $info) return jsonData(401,'暂无数据');
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
        try{
            $id = Db::table($this->table)->insertGetId($input);
            if ($id >0) {
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
}