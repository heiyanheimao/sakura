<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/22
 * Time: 14:10
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

class UserProductModel
{
    private $userProduct = 'skr_user_product';
    public function getProduct($input)
    {
        try {
            //获取数据
            $db = Db::table($this->userProduct)
                ->alias('up')
                ->join(['skr_product_package' => 'pp'],'pp.package_id = up.package_id')
                ->join(['skr_product' => 'p'], 'p.product_id = up.product_id');
            if ($input['product_name'] != '') {
                $db->where('product_name', 'like', "%{$input['product_name']}%");
            }
//            var_dump($input);
            if ($input['package_name'] != '') {
                $db->where('package_name', 'like', "%{$input['package_name']}%");
            }

            $list = $db->field('up.user_id,up.order_id,pp.package_name,p.product_name,up.package_id,up.product_id,up.product_num,up.surplus_num,up.product_money,up.surplus_money,up.product_type')
               ->order(['order_id' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201, '暂无数据');
            }
            return jsonData(200, '查询成功', ['list' => $list]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改消费资格
     * @param $input
     * @return bool
     */
    public function spend($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            $info = Db::table($this->userProduct)->field('product_type,surplus_num,surplus_money')
                ->where('product_id',$input['product_id'])
                ->where('order_id',$input['order_id'])
                ->where('package_id',$input['package_id'])
                ->where('user_id',$input['user_id'])
                ->find();
            if (null == $info) return jsonData(400, '没有找到相对应的数据');
            switch ($info['product_type']) {
                case 1://次数卡
                    if ($input['spend'] > $info['surplus_num']) return jsonData(401, '消费次数大于剩余次数，消费失败');
                    $rtn = Db::table($this->userProduct)
                        ->where('product_id',$input['product_id'])
                        ->where('order_id',$input['order_id'])
                        ->where('package_id',$input['package_id'])
                        ->where('user_id',$input['user_id'])
                        ->where('surplus_num', 'egt', $input['spend'])
                        ->update([
                            'surplus_num'	=>	Db::raw('surplus_num-' . $input['spend'])
                        ]);
                    if ($rtn === 1) {
                        AdminLogModel::writeLog($guy, '用户服务余量', json_encode($input), '更新成功');
                        return jsonData(200, '操作成功');
                    }
                    break;
                case 2://储值卡
                    if ($input['spend'] > $info['surplus_money']) return jsonData(401, '消费金额大于剩余金额，消费失败');
                    $rtn = Db::table($this->userProduct)
                        ->where('product_id',$input['product_id'])
                        ->where('order_id',$input['order_id'])
                        ->where('package_id',$input['package_id'])
                        ->where('user_id',$input['user_id'])
                        ->where('surplus_money', 'egt', $input['spend'])
                        ->update([
                            'surplus_money'	=>	Db::raw('surplus_money -' . $input['spend'])
                        ]);
                    if ($rtn === 1) {
                        AdminLogModel::writeLog($guy, '用户服务余量', json_encode($input), '更新成功');
                        return jsonData(200, '操作成功');
                    }
                    break;
            }
            AdminLogModel::writeLog($guy, '用户服务余量', json_encode($input), '更新失败');
            return false;
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '用户服务余量', json_encode($input), '服务内部错误~');
            return false;
        }
    }
}