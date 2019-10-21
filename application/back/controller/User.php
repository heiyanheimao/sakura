<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/21
 * Time: 11:05
 */

namespace app\back\controller;


use app\back\model\UserModel;
use think\Request;
use think\Validate;
use think\validate\ValidateRule;

class User extends Base
{
    private $level = 5;
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
            $input['user_name']  = $request->post('user_name');//用户名称
            $input['user_phone'] = $request->post('user_phone');//用户电话
            $input['nickname']   = $request->post('nickname');//用户昵称
            $input['state']      = $request->post('state');//状态 1启用, 0禁用, -1删除
            $input['is_vip']     = $request->post('is_vip');//是否购买过产品 1是, 0否
            $input['page']       = $request->post('page');//分页
            $input['end']        = $request->post('end');//起始时间
            $input['start']      = $request->post('start');//结束时间
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'user_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'user_phone' => function ($v) {
                    if (null === $v) {
                        return '缺少参数2';
                    }
                    return true;
                },
                'nickname' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' !== $v && !in_array($v, ['0', '1', '-1'], true)) {
                        return '不合法的参数';
                    } else {
                        return true;
                    }
                },
                'is_vip' => function ($v) {
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
                'start' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if ('' !== $v && false == validateDate($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'end' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
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
            $model = new UserModel();
            return $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改用户状态
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
            $input['user_id'] = $request->post('user_id');//用户id
            $input['state']   = $request->post('state');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (!in_array($v, ['0', '1', '-1'], true)) {
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
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new UserModel();
            return $model->changeState($input);
        }
        return jsonData(400, '非法请求');
    }

    /**获取预约信息
     * @param Request $request
     * @return array
     */
    public function getCheckin(Request $request)
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
            $input['user_id'] = $request->post('user_id');//用户id
            //参数校验
            $validate = new Validate();
            $validate->rule([
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
            $model = new UserModel();
            return $model->getCheckin($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改用户姓名
     * @param Request $request
     * @return array
     */
    public function editUserName(Request $request)
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
            $input['user_id'] = $request->post('user_id');//用户id
            $input['user_name'] = $request->post('user_name');//用户名称
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'user_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'user_name' => function ($v) {
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
            $model = new UserModel();
            return $model->editUserName($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改用户手机号
     * @param Request $request
     * @return array
     */
    public function editUserPhone(Request $request)
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
            $input['user_id'] = $request->post('user_id');//用户id
            $input['user_phone'] = $request->post('user_phone');//用户名称
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'user_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'user_phone' => ValidateRule::isRequire(null, '缺少参数')->regex('/^(1\d{10})$/',
                    '请输入正确的座机号或者手机号')
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new UserModel();
            return $model->editUserPhone($input);
        }
        return jsonData(400, '非法请求');
    }
}