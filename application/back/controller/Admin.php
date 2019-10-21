<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/8
 * Time: 9:27
 */

namespace app\back\controller;


use app\back\model\AdminModel;
use think\facade\Session;
use think\Request;
use think\Validate;
use think\validate\ValidateRule;

/**系统用户
 * Class Admin
 * @package app\back\controller
 */
class Admin extends Base
{
    private $level = 5;

    /**登录页面渲染
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {

        if (self::checkRequest($request)) {
            //接收参数
            $input['admin_account']  = $request->post('username');//管理员账号
            $input['admin_password'] = $request->post('password');//管理员密码
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'admin_account' => ValidateRule::isRequire(null, '请输入管理员账号')->regex('/^[0-9A-Za-z]{4,12}$/',
                    '账号必须由数字，大小写字母组成，4到12位'),
                'admin_password' => ValidateRule::isRequire(null, '请输入管理员密码')->regex('/^[0-9A-Za-z]{4,12}$/',
                    '密码必须由数字，大小写字母组成，4到12位')
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new AdminModel();
            $rtn   = $model->login($input);
            if (false === $rtn) {
                return jsonData(300, '服务内部错误~');
            }
            if ([] == $rtn) {
                return jsonData(401, '该用户不存在');
            }
            if (md5($input['admin_password']) != $rtn['admin_password']) {
                return jsonData(402, '密码错误');
            }
            if (1 != $rtn['admin_type']) {
                return jsonData(403, '非管理员无法登录后台');
            }
            if (0 == $rtn['state']) {
                return jsonData(403, '账号已禁用，请联系超级管理员');
            }
            if (-1 == $rtn['state']) {
                return jsonData(403, '账号已删除，请联系超级管理员');
            }
            Session::set('info', ['admin_account' => $input['admin_account'], 'admin_name' => $rtn['admin_name']]);
            return jsonData(200, '登录成功');
        }
        return jsonData(400, '非法请求');
    }

    /*
     * @param Request $request
     */
    public function getLists(Request $request)
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
            $input['admin_account'] = $request->post('admin_account');//管理员账号
            $input['admin_name']    = $request->post('admin_name');//管理员名称
            $input['page']          = $request->post('page');//当前页
            //逻辑处理
            $model = new AdminModel();
            $rtn   = $model->getList($input);
            if (false === $rtn) {
                return jsonData(300, '服务内部错误~');
            }
            if ([] == $rtn['list']) {
                return jsonData(401, '暂无数据');
            }
            $level = config('app.level');
            //重组数据
            $rtn['list'] = array_column($rtn['list'], null, 'admin_id');
            foreach ($rtn['list'] as $k => $v) {
                $rtn['list'][$k]['create_time'] = date('Y-m-d H:i:s', $rtn['list'][$k]['create_time']);
                switch ($rtn['list'][$k]['state']) {
                    case 1:
                        $rtn['list'][$k]['state'] = '已启用';
                        break;
                    case 0:
                        $rtn['list'][$k]['state'] = '已禁用';
                        break;
                    case -1:
                        $rtn['list'][$k]['state'] = '已删除';
                        break;
                }
                switch ($rtn['list'][$k]['admin_type']) {
                    case 1:
                        $rtn['list'][$k]['admin_type'] = '后台';
                        break;
                    case 2:
                        $rtn['list'][$k]['admin_type'] = '酒店';
                        break;
                }
                $str = '';
                if ($rtn['list'][$k]['level']) {
                    foreach (explode(',', $rtn['list'][$k]['level']) as $vv) {
                        $str .= $level[$vv] . ',';
                    }
                    $rtn['list'][$k]['level'] = rtrim($str, ',');
                }
            }
            return jsonData(200, '获取数据成功', $rtn);
        }
        return jsonData(400, '非法请求');
    }

    /**添加
     * @param Request $request
     * @return array|bool
     */
    public function add(Request $request)
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
            $input['admin_account']    = $request->post('admin_account');//管理员账号
            $input['admin_password']   = $request->post('admin_password');//管理员密码
            $input['admin_repassword'] = $request->post('admin_repassword');//管理员二次密码
            $input['admin_name']       = $request->post('admin_name');//管理员名称
            $input['admin_type']       = $request->post('admin_type');//管理员类型, 1后台, 2酒店
            $input['level']            = $request->post('level');//管理员权限
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'admin_account' => ValidateRule::isRequire(null, '请输入管理员账号')->regex('/^[0-9A-Za-z]{4,12}$/',
                    '账号必须由数字，大小写字母组成，4到12位'),
                'admin_password' => ValidateRule::isRequire(null, '请输入管理员密码')->regex('/^[0-9A-Za-z]{4,12}$/',
                    '密码必须由数字，大小写字母组成，4到12位'),
                'admin_repassword' => function ($v, $all) {
                    if ($v != $all['admin_password']) {
                        return '两次密码不一致';
                    }
                    return true;
                },
                'admin_name' => function ($v) {
                    if (mb_strlen($v) <= 1 || mb_strlen($v) > 12) {
                        return '姓名在2到12个字符';
                    }
                    return true;
                },
                'admin_type' => ValidateRule::regex('/^[12]$/', '请输入一个合法的类型'),
                'level' => function ($v, $all) {
                    if ($all['admin_type'] == 1 && null == $v) {
                        return '请给一个权限';
                    }
                    if ($all['admin_type'] == 2 && null != $v) {
                        return '请勿给一个权限';
                    }
                    if ($all['admin_type'] == 1) {
                        foreach ($v as $k => $vv) {
                            if (!in_array($k, [1, 2, 3, 4, 5, 6, 9])) {
                                return '请输入一个合法的权限';
                            }
                        }
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }

            //逻辑处理
            $model = new AdminModel();
            if (1 == $input['admin_type']) {
                if (array_key_exists(9, $input['level'])) {
                    $input['level'] = 9;
                } else {
                    $input['level'] = implode(',', array_keys($input['level']));
                }
            } else {
                $input['level'] = '';
            }
            $input['create_time']    = time();
            $input['state']          = 1;
            $input['admin_password'] = md5($input['admin_password']);
            unset($input['admin_repassword']);
            return $model->add($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改状态
     * @param Request $request
     * @return array|bool
     */
    public function changeStatus(Request $request)
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
            $input['state']    = $request->post('state');//状态变更
            $input['admin_id'] = $request->post('admin_id');//管理员id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'state' => function ($v) {
                    if ($v == '启用' || $v == '禁用' || $v == '删除') {
                        return true;
                    }
                    return '请给一个合法的数据';
                },
                'admin_id' => function ($v) {
                    if (!is_numeric($v) || $v < 0) {
                        return '请给一个合法的数据';
                    }
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new AdminModel();
            switch ($input['state']) {
                case '启用':
                    $input['state'] = 1;
                    break;
                case '禁用':
                    $input['state'] = 0;
                    break;
                case '删除':
                    $input['state'] = -1;
                    break;
            }
            return $model->changeStatus($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改权限
     * @param Request $request
     * @return array
     */
    public function changeAuth(Request $request)
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
            $input['admin_id']   = $request->post('admin_id');//管理员id
            $input['admin_type'] = $request->post('admin_type');//管理员类型, 1后台, 2酒店
            $input['level']      = $request->post('level');//管理员权限
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'admin_type' => ValidateRule::regex('/^[12]$/', '请输入一个合法的类型'),
                'level' => function ($v, $all) {
                    if ($all['admin_type'] == 1 && null == $v) {
                        return '请给一个权限';
                    }
                    if ($all['admin_type'] == 2 && null != $v) {
                        return '请勿给一个权限';
                    }
                    if ($all['admin_type'] == 1) {
                        foreach ($v as $k => $vv) {
                            if (!in_array($k, [1, 2, 3, 4, 5, 6, 9])) {
                                return '请输入一个合法的权限';
                            }
                        }
                    }
                    return true;
                },
                'admin_id' => function ($v) {
                    if (!is_numeric($v) || $v < 0) {
                        return '请给一个合法的数据';
                    }
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }

            //逻辑处理
            $model = new AdminModel();
            if (1 == $input['admin_type']) {
                if (array_key_exists(9, $input['level'])) {
                    $input['level'] = 9;
                } else {
                    $input['level'] = implode(',', array_keys($input['level']));
                }
            } else {
                $input['level'] = '';
            }
            return $model->changeAuth($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改密码
     * @param Request $request
     * @return array
     */
    public function changePwd(Request $request)
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
            $input['admin_id']          = $request->post('admin_id');//管理员id
            $input['admin_repassword']  = $request->post('admin_repassword');//二次密码
            $input['admin_password']    = $request->post('admin_password');//新密码
            $input['admin_oldpassword'] = $request->post('admin_oldpassword');//旧密码
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'admin_id' => function ($v) {
                    if (!is_numeric($v) || $v < 0) {
                        return '请给一个合法的数据';
                    }
                    return true;
                },
                'admin_password' => ValidateRule::isRequire(null, '请输入管理员密码')->regex('/^[0-9A-Za-z]{4,12}$/',
                    '密码必须由数字，大小写字母组成，4到12位'),
                'admin_repassword' => function ($v, $all) {
                    if ($v != $all['admin_password']) {
                        return '两次密码不一致';
                    }
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }

            //逻辑处理
            unset($input['admin_repassword']);
            $input['admin_password'] = md5($input['admin_password']);
            $model                   = new AdminModel();
            return $model->changePwd($input);
        }
        return jsonData(400, '非法请求');
    }

    /**批量删除
     * @param Request $request
     * @return array
     */
    public function delAll(Request $request)
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
            $input['admin_id'] = $request->post('admin_id');//管理员id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'admin_id' => function ($v) {
                    if (!is_array($v)) {
                        return '请给一个合法的数据';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }

            $model = new AdminModel();
            return $model->delAll($input);
        }
        return jsonData(400, '非法请求');
    }
}