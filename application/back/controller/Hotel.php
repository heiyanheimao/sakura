<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/18
 * Time: 10:35
 */

namespace app\back\controller;


use app\back\model\HotelModel;
use think\Request;
use think\Validate;
use think\validate\ValidateRule;

class Hotel extends Base
{
    /**添加酒店
     * @param Request $request
     * @return array
     */
    public function add(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) {
                return jsonData(500, '登录态失效，请重新登录');
            }
            //权限检测
            $auth = self::checkAuth($info, 1);
            if (200 != $auth['code']) {
                return $auth;
            }
            //接收参数
            $input['hotel_name']    = $request->post('hotel_name');//酒店名称
            $input['hotel_address'] = $request->post('hotel_address');//酒店地址
            $input['hotel_phone']   = $request->post('hotel_phone');//酒店电话
            $input['content']       = $request->post('content');//酒店内容
            $input['state']         = $request->post('state');//酒店状态 1启用 0 禁用
            $input['allow_order']   = $request->post('allow_order');//是否允许下单 1允许0禁止
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'hotel_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '酒店名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '酒店名称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'hotel_address' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '酒店地址不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 50) {
                        return '酒店地址在1到50个字符';
                    } else {
                        return true;
                    }
                },
                'hotel_phone' => ValidateRule::isRequire(null, '缺少参数')->regex('/^(0[\d]{2,3}-[\d]{7,8})|(1\d{10})$/', '请输入正确的座机号或者手机号'),
                'content' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                },
                'allow_order' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $input['content']      = filterScript($input['content']);
            $input['create_time']  = time();
            $input['hotel_cover'] = '';

            $model = new HotelModel();
            return $model->add($input);
        }
        return jsonData(400, '非法请求');
    }
}