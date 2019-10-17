<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/13
 * Time: 11:03
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

class ProductModel
{
    private $package = 'skr_product_package';//产品包
    private $product = 'skr_product';//产品
    private $product_relation = 'skr_product_relation';//关联表


    /**获取产品包列表
     * @param $input
     * @return array
     */
    public function packageList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->package);
            if ($input['package_name'] != '') $db->where('package_name', 'like', "%{$input['package_name']}%");
            if ($input['start'] != '') $db->where('create_time', '>=', strtotime($input['start']));
            if ($input['end'] != '') $db->where('create_time', '<=', strtotime($input['end']));
            if ($input['state'] !== '') $db->where('state', $input['state']);
            $pageAll = ceil($db->count()/10);
            //获取数据
            $db = Db::table($this->package);
            if ($input['package_name'] != '') $db->where('package_name', 'like', "%{$input['package_name']}%");
            if ($input['start'] != '') $db->where('create_time', '>=', strtotime($input['start']));
            if ($input['end'] != '') $db->where('create_time', '<=', strtotime($input['end']));
            if ($input['state'] !== '') $db->where('state', $input['state']);

            $list = $db->field('package_id,package_name,package_desc,package_price,create_time,state')
                ->page($input['page'],10)->order(['state' => 'desc','create_time' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201,'暂无数据');
            }
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $list[$k]['create_time']);
                switch ($list[$k]['state']) {
                    case '0':
                        $list[$k]['state'] = '已下架';
                        break;
                    case '1':
                        $list[$k]['state'] = '已上架';
                        break;
                }
            }
            return jsonData(200,'查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**添加产品包
     * @param $input
     * @return array
     */
    public function packageAdd($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            Db::table($this->package)->insertGetId($input);
            AdminLogModel::writeLog($guy, '添加产品包', json_encode($input), '添加成功');
            return jsonData(200, '添加产品包成功');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '添加产品包', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改产品包状态
     * @param $input
     * @return array
     */
    public function changePackageState($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            //查询产品包当前状态
            $info = Db::table($this->package)->field('state')->where('package_id', $input['package_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前产品包');
            if ($input['state'] == $info['state']) return jsonData(402, '修改状态与当前状态一直，不能修改');
            $rtn = Db::table($this->package)->update($input);
            if ($rtn >=1) {
                AdminLogModel::writeLog($guy, '修改产品包状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改产品包状态', json_encode($input), '修改失败');
            return jsonData(403,'状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改产品包状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**编辑产品包信息
     * @param $input
     * @return array
     */
    public function packageEdit($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            //查询产品包当前状态
            $info = Db::table($this->package)->field('package_id')->where('package_id', $input['package_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前产品包');
            $rtn = Db::table($this->package)->update($input);
            if ($rtn >=1) {
                AdminLogModel::writeLog($guy, '修改产品包状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改产品包状态', json_encode($input), '修改失败');
            return jsonData(403,'状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改产品包状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    public function productList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->product);

            if ($input['product_name'] != '')   $db->where('product_name', 'like', "%{$input['product_name']}%");
            if ($input['product_type'] != '')   $db->where('product_type', $input['product_type']);
            if ($input['state'] !== '')         $db->where('state', $input['state']);
            if ($input['start'] != '')          $db->where('create_time', '>=', strtotime($input['start']));
            if ($input['end'] != '')            $db->where('create_time', '<=', strtotime($input['end']));
            $pageAll = ceil($db->count()/10);
            //获取数据
            $db = Db::table($this->product);
            if ($input['product_name'] != '')   $db->where('product_name', 'like', "%{$input['product_name']}%");
            if ($input['product_type'] != '')   $db->where('product_type', $input['product_type']);
            if ($input['state'] !== '')         $db->where('state', $input['state']);
            if ($input['start'] != '')          $db->where('create_time', '>=', strtotime($input['start']));
            if ($input['end'] != '')            $db->where('create_time', '<=', strtotime($input['end']));

            $list = $db->field('product_id,product_name,product_desc,product_num,product_money,product_type,create_time,state')
                ->page($input['page'],10)->order(['state' => 'desc','create_time' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201,'暂无数据');
            }
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $list[$k]['create_time']);
                switch ($list[$k]['state']) {
                    case 0:
                        $list[$k]['state'] = '已禁用';
                        break;
                    case 1:
                        $list[$k]['state'] = '已启用';
                        break;
                }
                switch ($list[$k]['product_type']) {
                    case 1:
                        $list[$k]['product_type'] = '次数卡';
                        break;
                    case 2:
                        $list[$k]['product_type'] = '储值卡';
                        break;
                }
            }
            return jsonData(200,'查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**为产品提供产品包列表
     * @return array
     */
    public function getPackageList()
    {
        try{
            //查询产品包当前状态
            $info = Db::table($this->package)->field('package_id,package_name')->order('create_time','desc')->select();
            return jsonData(200, '', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**添加产品
     * @param $input
     * @return array
     */
    public function productAdd($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            $packageId = $input['package_id'];
            unset($input['package_id']);
            //查询产品包
            $info = Db::table($this->package)->field('package_id')->where('package_id', $packageId)->find();
            if (null == $info) return jsonData(401, '未找到对应的产品包信息，添加失败');

            Db::startTrans();
            $productId = Db::table($this->product)->insertGetId($input);
            $rtn = Db::table($this->product_relation)->insert(['package_id' => $packageId, 'product_id' => $productId]);
            if ($productId >=1 && $rtn == 1) {
                Db::commit();
                AdminLogModel::writeLog($guy, '添加产品', json_encode($input), '添加成功');
                return jsonData(200, '添加产品成功');
            }
            Db::rollback();
            AdminLogModel::writeLog($guy, '添加产品', json_encode($input), '添加失败');
            return jsonData(400, '添加产品失败');
        } catch (\Exception $e) {
            Db::rollback();
            AdminLogModel::writeLog($guy, '添加产品包', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改产品状态
     * @param $input
     * @return array
     */
    public function changeProductState($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            //查询产品当前状态
            $info = Db::table($this->product)->field('state')->where('product_id', $input['product_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前产品');
            if ($input['state'] == $info['state']) return jsonData(402, '修改状态与当前状态一直，不能修改');
            $rtn = Db::table($this->product)->update($input);
            if ($rtn >=1) {
                AdminLogModel::writeLog($guy, '修改产品状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改产品状态', json_encode($input), '修改失败');
            return jsonData(403,'状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改产品状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**编辑产品信息
     * @param $input
     * @return array
     */
    public function productEdit($input)
    {
        $guy = Session::get('info.admin_account');
        $data = $input;
        try{
            Db::startTrans();
            //查询产品包信息
            $rtn1 = 1;
            if (isset($input['package_id'])) {
                $rtn1 = Db::table($this->product_relation)
                    ->where(['product_id' => $input['product_id']])
                    ->update(['package_id' => $input['package_id']]);
                unset($input['product_id']);
            }
            //查询产品信息
            $rtn2 = 1;
            if (count($input) > 1) {
                $info = Db::table($this->product)->field('product_id')->where('product_id', $input['product_id'])->find();
                if (null == $info) return jsonData(401,'未找到当前产品包');
                $rtn2 = Db::table($this->product)->update($input);
            }
            if ($rtn2 ==1 && $rtn1 == 1) {
                Db::commit();
                AdminLogModel::writeLog($guy, '编辑产品信息', json_encode($data), '编辑成功');
                return jsonData(200, '编辑成功');
            }
            Db::rollback();
            AdminLogModel::writeLog($guy, '编辑产品信息', json_encode($data), '编辑失败');
            return jsonData(403,'编辑失败');
        } catch (\Exception $e) {
            Db::rollback();
            AdminLogModel::writeLog($guy, '编辑产品信息', json_encode($data), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}