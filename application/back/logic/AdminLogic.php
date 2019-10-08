<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/8
 * Time: 10:02
 */

namespace app\back\logic;


use app\back\model\AdminModel;
use think\facade\Session;

class AdminLogic extends BaseLogic
{
    /**管理员登录逻辑处理
     * @param $input
     */
    public function login($input)
    {
        $model = new AdminModel();
        $rtn = $model->login($input);
        if (false === $rtn) return jsonData(300, '服务内部错误~');
        if ([] == $rtn) return jsonData(401, '该用户不存在');
        if (md5($input['admin_password']) != $rtn['admin_password']) return jsonData(402, '密码错误');
        if (1 != $rtn['admin_type']) return jsonData(403, '非管理员无法登录后台');
        if (0 == $rtn['state']) return jsonData(403, '账号已禁用，请联系超级管理员');
        if (-1 == $rtn['state']) return jsonData(403, '账号已删除，请联系超级管理员');
        Session::set('info', ['admin_account' => $input['admin_account'], 'admin_name' => $rtn['admin_name']]);
        return jsonData(200, '登录成功');
    }

    /**获取管理员列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        $model = new AdminModel();
        $rtn = $model->getList($input);
        if (false === $rtn) return jsonData(300, '服务内部错误~');
        if ([] == $rtn['list']) return jsonData(401, '暂无数据');
        $level = config('app.level');
        //重组数据
        foreach ($rtn['list'] as $k =>$v) {
            $rtn['list'][$k]['create_time'] = date('Y-m-d H:i:s', $rtn['list'][$k]['create_time']);
            $str = '';
            foreach (explode(',', $rtn['list'][$k]['level']) as $vv) {
                $str .= $level[$vv] . ',';
            }
            $rtn['list'][$k]['level'] = rtrim($str, ',');
        }
        return jsonData(200, '获取数据成功', $rtn);
    }
}