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
                ->page($input['page'],10)->select();
            return ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']];
        } catch (DbException $e) {
            return false;
        }
    }
}