<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/18
 * Time: 16:05
 */

namespace app\back\model;


use think\Db;

class OrderModel
{
    private $order = 'skr_order';
    private $package = 'skr_product_package';
    private $product = 'skr_product';
    private $user = 'skr_user';

    /**获取列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->order)
                ->alias('o')
                ->leftJoin([$this->user => 'u'], 'u.user_id = o.user_id')
                ->leftJoin([$this->package => 'p'], 'p.package_id = o.package_id');

            if ($input['order_number'] != '') {
                $db->where('o.order_number', 'like', "%{$input['order_number']}%");
            }
            if ($input['wechat_number'] != '') {
                $db->where('o.wechat_number', 'like', "%{$input['wechat_number']}%");
            }
            if ($input['user_name'] != '') {
                $db->where('u.user_name', 'like', "%{$input['user_name']}%");
            }
            if ($input['package_name'] != '') {
                $db->where('p.package_name', 'like', "%{$input['package_name']}%");
            }
            if ($input['state'] !== '') {
                $db->where('o.state', $input['state']);
            }
            if ($input['payment_state'] !== '') {
                $db->where('o.payment_state', $input['payment_state']);
            }
            if ($input['refund_state'] !== '') {
                $db->where('o.refund_state', $input['refund_state']);
            }
            if ($input['end'] != '') {
                $db->where('o.create_time', '<=', strtotime($input['end']));
            }
            if ($input['start'] != '') {
                $db->where('o.create_time', '>=', strtotime($input['start']));
            }
            $pageAll = ceil($db->count() / 10);
            //获取数据
            $db = Db::table($this->order)
                ->alias('o')
                ->leftJoin([$this->user => 'u'], 'u.user_id = o.user_id')
                ->leftJoin([$this->package => 'p'], 'p.package_id = o.package_id');

            if ($input['order_number'] != '') {
                $db->where('o.order_number', 'like', "%{$input['order_number']}%");
            }
            if ($input['wechat_number'] != '') {
                $db->where('o.wechat_number', 'like', "%{$input['wechat_number']}%");
            }
            if ($input['user_name'] != '') {
                $db->where('u.user_name', 'like', "%{$input['user_name']}%");
            }
            if ($input['package_name'] != '') {
                $db->where('p.package_name', 'like', "%{$input['package_name']}%");
            }
            if ($input['state'] !== '') {
                $db->where('o.state', $input['state']);
            }
            if ($input['payment_state'] !== '') {
                $db->where('o.payment_state', $input['payment_state']);
            }
            if ($input['refund_state'] !== '') {
                $db->where('o.refund_state', $input['refund_state']);
            }
            if ($input['end'] != '') {
                $db->where('o.create_time', '<=', strtotime($input['end']));
            }
            if ($input['start'] != '') {
                $db->where('o.create_time', '>=', strtotime($input['start']));
            }
            $list = $db->field('o.order_id,o.user_id,u.user_name,o.order_number,o.wechat_number,o.order_price,o.package_id,p.package_name,o.create_time,o.state,o.product_ids,o.payment_state,o.payment_time,o.refund_state,o.refund_time,o.refund_reason')
                ->page($input['page'], 10)->order(['o.state' => 'desc', 'o.create_time' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201, '暂无数据');
            }
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $list[$k]['create_time']);
                if ($list[$k]['payment_time'] == 0) {
                    $list[$k]['payment_time'] = '';
                } else {
                    $list[$k]['payment_time'] = date('Y-m-d H:i:s', $list[$k]['payment_time']);
                }
                if ($list[$k]['refund_time'] == 0) {
                    $list[$k]['refund_time'] = '';
                } else {
                    $list[$k]['refund_time'] = date('Y-m-d H:i:s', $list[$k]['refund_time']);
                }
                switch ($list[$k]['state']) {
                    //1已入住, 2未入住, 0关闭'
                    case 0:
                        $list[$k]['state'] = '关闭';
                        break;
                    case 1:
                        $list[$k]['state'] = '已入住';
                        break;
                    case 2:
                        $list[$k]['state'] = '未入住';
                        break;
                }
                switch ($list[$k]['refund_state']) {
                    //1是, 2申请退款, 0否, 默认0
                    case 0:
                        $list[$k]['refund_state'] = '否';
                        break;
                    case 1:
                        $list[$k]['refund_state'] = '是';
                        break;
                    case 2:
                        $list[$k]['refund_state'] = '申请退款';
                        break;
                }
                switch ($list[$k]['payment_state']) {
                    //'是否支付, 1是, 0否, 默认0',
                    case 0:
                        $list[$k]['payment_state'] = '否';
                        break;
                    case 1:
                        $list[$k]['payment_state'] = '是';
                        break;
                }
            }
            return jsonData(200, '查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取产品相关信息
     * @param $input
     * @return array
     */
    public function getProducts($input)
    {
        try {
            //获取总页数
            $info = Db::table($this->product)
                ->field('product_name,product_num,product_money,product_type')
                ->where('product_id', 'in', $input['product_ids'])
                ->select();
            if ([] == $info) return jsonData(401, '未找到产品信息');
            foreach ($info as &$v) {
                switch ($v['product_type']) {
                    case 1://次数卡
                        $v['product_type'] = '次数卡';
                        break;
                    case 2://储值卡
                        $v['product_type'] = '储值卡';
                        break;
                }
            }
            return jsonData(200, '查询成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }
}