<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/17
 * Time: 14:35
 */

namespace app\back\controller;


use app\back\model\DoctorModel;
use think\Request;
use think\Validate;

class Doctor extends Base
{
    public function getCategoryInfo(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;

            //逻辑处理
            $model = new DoctorModel();
            return  $model->getCategoryInfo();
        }
        return jsonData(400, '非法请求');
    }

    public function add(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;
            //接收参数
            $input['category_id']       = $request->post('category_id');//分类id
            $input['content']           = $request->post('content');//医生介绍
            $input['doctor_name']       = $request->post('doctor_name');//医生名称
            $input['doctor_position']   = $request->post('doctor_position');//医生职位
            $input['state']             = $request->post('state');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'category_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v,false)) return '不合法的参数';
                    return true;
                },
                'content' => function($v){
                    if (null === $v) return '缺少参数';
                    return true;
                },
                'doctor_name' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '医生名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '医生名称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'doctor_position' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '医生职称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '医生职称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'state' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的文章状态';
                    } else {
                        return true;
                    }
                }
            ]);

            //逻辑处理
            $input['content'] = filterScript($input['content']);
            $input['create_time'] = time();
            $input['doctor_cover'] = '';
            $model = new DoctorModel();
            return  $model->add($input);
        }
        return jsonData(400, '非法请求');
    }
}