<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/8
 * Time: 9:27
 */

namespace app\back\controller;


use app\back\logic\AdminLogic;
use think\Request;
use think\Validate;
use think\validate\ValidateRule;

class Admin extends Base
{
    /**登录页面渲染
     * @return mixed
     */
    public function login()
    {
        return $this->fetch();
    }

    /**后台登录请求
     * @return array
     */
    public function loginAjax(Request $request)
    {
        if (self::checkRequest($request)) {
            //接收参数
            $input['admin_account']  = $request->post('username');//管理员账号
            $input['admin_password'] = $request->post('password');//管理员密码
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'admin_account'  => ValidateRule::isRequire(null, '请输入管理员账号')->regex('/^[0-9A-Za-z]{4,12}$/', '账号必须由数字，大小写字母组成，4到12位'),
                'admin_password' => ValidateRule::isRequire(null, '请输入管理员密码')->regex('/^[0-9A-Za-z]{4,12}$/', '密码必须由数字，大小写字母组成，4到12位')
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $logic = new AdminLogic();
            return $logic->login($input);
        }
        return jsonData(400, '非法请求');

    }

    public function index()
    {
        return $this->fetch();
    }

    public function lists(Request $request)
    {
        //登录态检测
        if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
        //权限检测
        $auth = self::checkAuth($info,5);
        if (200 != $auth['code']) return $auth;

        //接收参数
        $input['admin_account']  = $request->post('admin_account');//管理员账号
        $input['admin_name'] = $request->post('admin_name');//管理员名称
        $input['page'] = $request->post('page');//当前页
        //逻辑处理
        $logic = new AdminLogic();
        return $logic->getList($input);
//        return $this->fetch();
    }

    /*
     * @param Request $request
     */
    public function getList(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,5);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['admin_account']  = $request->post('admin_account');//管理员账号
            $input['admin_name'] = $request->post('admin_name');//管理员名称
            $input['page'] = $request->post('page');//当前页
            //逻辑处理
            $logic = new AdminLogic();
            return $logic->getList($input);
        }
        return jsonData(400, '非法请求');
    }
}