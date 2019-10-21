<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/8
 * Time: 9:28
 */

namespace app\back\controller;


use app\back\model\AdminModel;
use think\Controller;
use think\facade\Session;
use think\Request;

class Base extends Controller
{
    /**检测请求方式
     * @param Request $request
     * @return bool
     */
    public static function checkRequest(Request $request)
    {

        if ($request->isAjax() && $request->isPost()) {
            return true;
        }
        return false;
    }


    /**检测登录态
     * @param Request $request
     * @return array
     */
    public function getAuth(Request $request)
    {
        if (self::checkRequest($request)) {
            if ($info = Session::get('info')) {
                $model = new AdminModel();
                $rtn = $model->getAuth($info['admin_account']);
                if (false === $rtn) return jsonData(300, '服务内部错误~');
                if ([] == $rtn) return jsonData(401, '该用户不存在');
                if (1 != $rtn['admin_type']) return jsonData(402, '非管理员没有操作权限');
                return jsonData(200, '', ['admin_name' => $info['admin_name'], 'level' => $rtn['level']]);
            }
            return jsonData(500, '登录态失效，请重新登录');
        }
        return jsonData(400, '非法请求');
    }

    /**获取登录态
     * @return bool|mixed
     */
    public static function getStatus()
    {
        if ($info = Session::get('info')) {
            return $info;
        }
        return false;
    }

    /**检测管理员权限
     * @param array $info session信息
     * @param int $level 管理模块
     * @return array
     */
    public static function checkAuth($info, $level)
    {
        $model = new AdminModel();
        $rtn = $model->getAuth($info['admin_account']);
        if (false === $rtn) return jsonData(300, '服务内部错误~');
        if ([] == $rtn) return jsonData(401, '该用户不存在');
        if (1 != $rtn['admin_type']) return jsonData(402, '非管理员没有权限操作');
        if (9 != $rtn['level'] && !in_array($level, explode(',', $rtn['level']))) return jsonData(403, '您没有权限进行当前操作');
        if (0 == $rtn['state']) return jsonData(404, '您已经被禁用无法操作');
        if (-1 == $rtn['state']) return jsonData(404, '您已经被删除无法操作');
        return jsonData(200, '');
    }
}