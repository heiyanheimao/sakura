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
}