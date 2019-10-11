<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/8
 * Time: 10:09
 */

namespace app\back\model;


use think\Db;
use think\exception\DbException;
use think\facade\Session;

class AdminModel
{
    private $table = 'skr_admin';

    /**管理员登录数据库查询
     * @param $input
     * @return array|bool
     */
    public function login($input)
    {
        try {
            $rtn = Db::table($this->table)
                ->field('admin_password,admin_name,admin_type,state')
                ->where('admin_account', $input['admin_account'])
                ->find();
            if (null == $rtn) return [];
            return $rtn;
        } catch (DbException $e) {
            return false;
        }
    }

    /**获取权限
     * @param $admin_account
     * @return array|bool
     */
    public function getAuth($admin_account)
    {
        try {
            $rtn = Db::table($this->table)
                ->field('admin_type,state,level')
                ->where('admin_account', $admin_account)
                ->find();
            if (null == $rtn) return [];
            return $rtn;
        } catch (DbException $e) {
            return false;
        }
    }

    /**获取列表
     * @param $input
     * @return array|bool
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->table);
            if ($input['admin_account'] != '') $db->where('admin_account', 'like', "%{$input['admin_account']}%");
            if ($input['admin_name'] != '') $db->where('admin_name', 'like', "%{$input['admin_name']}%");
            $pageAll = ceil($db->count()/10);
            //获取数据
            $db = Db::table($this->table);
            if ($input['admin_account'] != '') $db->where('admin_account', 'like', "%{$input['admin_account']}%");
            if ($input['admin_name'] != '') $db->where('admin_name', 'like', "%{$input['admin_name']}%");
            $list = $db->field('admin_id,admin_account,admin_name,admin_type,level,create_time,state')
                ->page($input['page'],10)->order(['state' => 'desc','create_time' => 'desc'])->select();
            return ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']];
        } catch (DbException $e) {
            return false;
        }
    }

    /**添加管理员
     * @param $input
     * @return array|bool
     */
    public function add($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //查询账号是否被注册
            $info = Db::table($this->table)
                ->field('admin_account')
                ->where('admin_account', $input['admin_account'])
                ->find();
            if ($info) return jsonData(401, '账号已注册');
            Db::table($this->table)->insertGetId($input);
            unset($input['admin_password']);
            AdminLogModel::writeLog($guy, '添加管理员', json_encode($input), '添加成功');
            return jsonData(200, '账号注册成功');
        } catch (DbException $e) {
            unset($input['admin_password']);
            AdminLogModel::writeLog($guy, '添加管理员', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        } catch (\Exception $e) {
            unset($input['admin_password']);
            AdminLogModel::writeLog($guy, '添加管理员', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        }
    }

    /**修改状态
     * @param $input
     * @return array
     */
    public function changeStatus($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //获取当前状态
            $info = Db::table($this->table)
                ->field('state')
                ->where('admin_id', $input['admin_id'])
                ->find();
            if (!$info) return jsonData(401, '未找到账号,无法操作');
            if ($info['state'] == -1) return jsonData(402, '管理员已删除，不能进行当前操作');
            $rtn = Db::table($this->table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改状态', json_encode($input), '修改成功');
                switch ($input['state']) {
                    case 1:
                        $state = '已启用';
                        break;
                    case 0:
                        $state = '已禁用';
                        break;
                    case -1:
                        $state = '已删除';
                        break;
                }
                return jsonData(200, '管理员状态修改成功',$state);
            }
            AdminLogModel::writeLog($guy, '修改状态', json_encode($input), '修改失败');
            return jsonData(403, '管理员状态修改失败');
        } catch (DbException $e) {
            AdminLogModel::writeLog($guy, '添加管理员', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '添加管理员', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改权限
     * @param $input
     * @return array
     */
    public function changeAuth($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //获取当前状态
            $info = Db::table($this->table)
                ->field('state')
                ->where('admin_id', $input['admin_id'])
                ->find();
            if (!$info) return jsonData(401, '未找到账号,无法操作');
            if ($info['state'] == -1) return jsonData(402, '管理员已删除，不能进行当前操作');
            $rtn = Db::table($this->table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '修改权限', json_encode($input), '修改成功');
                return jsonData(200, '管理员权限修改成功');
            }
            AdminLogModel::writeLog($guy, '修改权限', json_encode($input), '修改失败');
            return jsonData(403, '管理员权限修改失败');
        } catch (DbException $e) {
            AdminLogModel::writeLog($guy, '修改权限', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改权限', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改密码
     * @param $input
     * @return array
     */
    public function changePwd($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            //获取当前密码
            $info = Db::table($this->table)
                ->field('state,admin_password')
                ->where('admin_id', $input['admin_id'])
                ->find();
            if (!$info) return jsonData(401, '未找到账号,无法操作');
            if ($info['state'] == -1) return jsonData(402, '管理员已删除，不能进行当前操作');
            if (md5($input['admin_oldpassword']) != $info['admin_password']) return jsonData(403, '旧密码输入错误');
            unset($input['admin_oldpassword']);
            $rtn = Db::table($this->table)->update($input);
            unset($input['admin_password']);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '管理员修改密码', json_encode($input), '修改成功');
                return jsonData(200, '管理员密码修改成功');
            }
            AdminLogModel::writeLog($guy, '管理员修改密码', json_encode($input), '修改失败');
            return jsonData(403, '管理员密码修改失败');
        } catch (DbException $e) {
            unset($input['admin_password']);
            AdminLogModel::writeLog($guy, '管理员修改密码', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        } catch (\Exception $e) {
            unset($input['admin_password']);
            AdminLogModel::writeLog($guy, '管理员修改密码', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**批量删除
     * @param $input
     * @return array
     */
    public function delAll($input)
    {
        $guy = Session::get('info.admin_account');
        try {
            $rtn = Db::table($this->table)->where('admin_id','in',$input['admin_id'])->update(['state' => -1]);
            if ($rtn >= 1) {
                AdminLogModel::writeLog($guy, '管理员批量删除', json_encode($input), '批量删除成功');
                return jsonData(200, '管理员批量删除成功');
            }
            AdminLogModel::writeLog($guy, '管理员批量删除', json_encode($input), '批量删除失败');
            return jsonData(403, '管理员批量删除失败');
        } catch (DbException $e) {
            AdminLogModel::writeLog($guy, '管理员批量删除', json_encode($input), '服务内部错误~');
            return jsonData(300, '服务内部错误~');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '管理员批量删除', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}