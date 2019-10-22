<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/22
 * Time: 14:05
 */

namespace app\back\controller;


use app\back\model\UserProductModel;
use think\Request;
use think\Validate;

class UserProduct extends Base
{
    private $level = 5;

    public function getProduct(Request $request)
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
            $input['product_name'] = $request->post('product_name');//产品名称
            $input['package_name'] = $request->post('package_name');//产品包名称
            $input['user_id']      = $request->post('user_id');//用户Id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'product_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数1';
                    }
                    return true;
                },
                'package_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数2';
                    }
                    return true;
                },
                'user_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new UserProductModel();
            return $model->getProduct($input);
        }
        return jsonData(400, '非法请求');
    }


    public function spend(Request $request)
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
            //接收参数
            $input['product_id'] = $request->post('product_id');//产品id
            $input['order_id']   = $request->post('order_id');//订单id
            $input['package_id'] = $request->post('package_id');//产品包id
            $input['user_id']    = $request->post('user_id');//用户Id
            $input['spend']      = $request->post('spend');//消费
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'product_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'order_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'package_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'user_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'spend' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new UserProductModel();
            return $model->spend($input);
        }
        return jsonData(400, '非法请求');
    }
}