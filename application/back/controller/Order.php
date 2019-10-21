<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/18
 * Time: 15:46
 */

namespace app\back\controller;


use app\back\model\OrderModel;
use think\Request;
use think\Validate;

class Order extends Base
{
    private $level = 3;
    public function getList(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) {
                return jsonData(500, '登录态失效，请重新登录');
            }
            //权限检测
            $auth = self::checkAuth($info, $this->level);
            if (200 != $auth['code']) {
                return $auth;
            }
            //接收参数 订单号, 支付订单号, 下单时间, 用户, 产品包id, 订单状态 用户名 产品包名
            $input['order_number']  = $request->post('order_number');//订单编号
            $input['wechat_number'] = $request->post('wechat_number');//微信支付号
            $input['user_name']     = $request->post('user_name');//用户名
            $input['package_name']  = $request->post('package_name');//产品包名
            $input['state']         = $request->post('state');//状态 是否入住, 1已入住, 2未入住, 0关闭
            $input['payment_state'] = $request->post('payment_state');//是否支付, 1是, 0否
            $input['refund_state']  = $request->post('refund_state');//是否退款, 1是, 2申请退款, 0否,
            $input['page']          = $request->post('page');//分页
            $input['end']           = $request->post('end');//下单起始时间
            $input['start']         = $request->post('start');//下单结束时间
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'order_number' => function ($v) {
                    if (null === $v) {
                        return '缺少参数1';
                    }
                    return true;
                },
                'wechat_number' => function ($v) {
                    if (null === $v) {
                        return '缺少参数1';
                    }
                    return true;
                },
                'user_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数1';
                    }
                    return true;
                },
                'package_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数1';
                    }
                    return true;
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数3';
                    } elseif ('' !== $v && !in_array($v, ['0', '1', '2'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                },
                'payment_state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数3';
                    } elseif ('' !== $v && !in_array($v, ['0', '1'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                },
                'refund_state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数3';
                    } elseif ('' !== $v && !in_array($v, ['0', '1', '2'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                },
                'page' => function ($v) {
                    if (null === $v) {
                        return '缺少参数5';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的页数';
                    }
                    return true;
                },
                'start' => function ($v) {
                    if (null === $v) {
                        return '缺少参数6';
                    }
                    if ('' !== $v && false == validateDate($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'end' => function ($v) {
                    if (null === $v) {
                        return '缺少参数7';
                    }
                    if ('' !== $v && false == validateDate($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new OrderModel();
            return $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    public function getProducts(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) {
                return jsonData(500, '登录态失效，请重新登录');
            }
            //权限检测
            $auth = self::checkAuth($info, $this->level);
            if (200 != $auth['code']) {
                return $auth;
            }
            //接收参数
            $input['product_ids']  = $request->post('product_ids');//产品ids
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'product_ids' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new OrderModel();
            return $model->getProducts($input);
        }
        return jsonData(400, '非法请求');
    }
}