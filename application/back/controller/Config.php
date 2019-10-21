<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/21
 * Time: 16:14
 */

namespace app\back\controller;


use app\back\model\ConfigModel;
use think\Request;
use think\Validate;

class Config extends Base
{
    private $level = 6;
    /**获取列表
     * @param Request $request
     * @return array
     */
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
            //接收参数
            $input['config_name']  = $request->post('config_name');//配置名称
            $input['config_state']  = $request->post('config_state');//配置状态
            $input['page']       = $request->post('page');//分页
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'config_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'config_state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' !== $v && !in_array($v, ['0', '1'], true)) {
                        return '不合法的参数';
                    } else {
                        return true;
                    }
                },
                'page' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的页数';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new ConfigModel();
            return $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改配置状态
     * @param Request $request
     * @return array
     */
    public function changeState(Request $request)
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
            $input['config_id'] = $request->post('config_id');//配置id
            $input['state']    = $request->post('state');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (!in_array($v, ['0', '1'], true)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'config_id' => function ($v) {
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
            $model = new HotelModel();
            return $model->changeState($input);
        }
        return jsonData(400, '非法请求');
    }
}