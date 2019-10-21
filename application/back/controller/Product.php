<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/13
 * Time: 11:02
 */

namespace app\back\controller;


use app\back\model\ProductModel;
use think\Request;
use think\Validate;

class Product extends Base
{
    private $level = 2;
    public function packageList(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['package_name'] = $request->post('package_name');//产品包名称
            $input['state']        = $request->post('state');//产品包状态1上架0下架
            $input['start']        = $request->post('start');//创建时间起始
            $input['end']          = $request->post('end');//结束
            $input['page']         = $request->post('page');//结束
            //参数校验
            if (null === $input['package_name']) return jsonData(401, '缺少参数');

            if (null === $input['state']) {
                return jsonData(402, '缺少参数');
            } else if('' !== $input['state'] && !in_array($input['state'], ['0', '1'], true)) {
                return jsonData(403, '不合法的产品包状态');
            }
            if (null === $input['start']) return jsonData(404, '缺少参数');
            if (null === $input['end']) return jsonData(405, '缺少参数');
            if (null === $input['page']) {
                return jsonData(406, '缺少参数');
            } else if(!is_numeric($input['page']) || strpos($input['page'], '.') || $input < 1) {
                return jsonData(407, '不合法的页码');
            }
            //逻辑处理
            $model = new ProductModel();
            return $model->packageList($input);
        }
        return jsonData(400, '非法请求');
    }


    /**添加产品包
     * @param Request $request
     * @return array
     */
    public function packageAdd(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['package_name']  = $request->post('package_name');//产品包名称
            $input['package_price'] = $request->post('package_price');//产品包价格
            $input['state']         = $request->post('state');//产品包状态1上架0下架
            $input['package_desc']  = $request->post('package_desc');//产品包描述
            //参数校验
            if (null === $input['package_name']) {
                return jsonData(401, '缺少参数');
            } else if(mb_strlen($input['package_name']) < 1 || mb_strlen($input['package_name']) > 16) {
                return jsonData(401, '产品包名称在1到16个字符');
            }
            if (null === $input['package_price']) {
                return jsonData(401, '缺少参数');
            } else if(!is_numeric($input['package_price']) || $input['package_price'] <= 0) {
                return jsonData(401, '不合法的产品包价格');
            }
            if (null === $input['state']) {
                return jsonData(401, '缺少参数');
            } else if(!in_array($input['state'], ['0', '1'], true)) {
                return jsonData(401, '不合法的产品包状态');
            }
            if (null === $input['package_desc']) {
                return jsonData(401, '缺少参数');
            }
            //逻辑处理
            $input['create_time'] = time();
            $model = new ProductModel();
            return $model->packageAdd($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改产品包状态
     * @param Request $request
     * @return array
     */
    public function changePackageState(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['package_id']  = $request->post('package_id');//产品包名称
            $input['state']         = $request->post('state');//产品包状态1上架0下架
            //参数校验
            if (null === $input['package_id']) {
                return jsonData(401, '缺少参数');
            } else if(!is_numeric($input['package_id']) || strpos($input['package_id'], '.') || $input < 1) {
                return jsonData(402, '不合法的参数');
            }

            if (null === $input['state']) {
                return jsonData(403, '缺少参数');
            } else if(!in_array($input['state'], ['0', '1'], true)) {
                return jsonData(404, '不合法的产品包状态');
            }
            //逻辑处理
            $model = new ProductModel();
            return $model->changePackageState($input);
        }
        return jsonData(400, '非法请求');
    }

    /**编辑产品包
     * @param Request $request
     * @return array
     */
    public function packageEdit(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['package_id']    = $request->post('package_id');//产品包id
            $input['package_name']  = $request->post('package_name');//产品包名称
            $input['package_price'] = $request->post('package_price');//产品包价格
            $input['package_desc']  = $request->post('package_desc');//产品包描述
            //参数校验
            if (null === $input['package_id']) {
                return jsonData(401, '缺少参数');
            } else if(!is_numeric($input['package_id']) || strpos($input['package_id'], '.') || $input < 1) {
                return jsonData(402, '不合法的参数');
            }
            if (null === $input['package_name']) {
                return jsonData(403, '缺少参数');
            } else if(mb_strlen($input['package_name']) < 1 || mb_strlen($input['package_name']) > 16) {
                return jsonData(404, '产品包名称在1到16个字符');
            }
            if (null === $input['package_price']) {
                return jsonData(405, '缺少参数');
            } else if( '' !== $input['package_price'] && (!is_numeric($input['package_price']) || $input['package_price'] <= 0)) {
                return jsonData(406, '不合法的产品包价格');
            }
            if (null === $input['package_desc']) {
                return jsonData(407, '缺少参数');
            }
            if ($input['package_name'] === '' && $input['package_name'] === '' && $input['package_name'] === '') {
                return jsonData(408, '请至少填写一个数据');
            }
            //逻辑处理
            if ('' === $input['package_name'])  unset($input['package_name']);
            if ('' === $input['package_price']) unset($input['package_price']);
            if ('' === $input['package_desc'])  unset($input['package_desc']);
            $model = new ProductModel();
            return $model->packageEdit($input);
        }
        return jsonData(400, '非法请求');
    }

    /**获取产品列表
     * @param Request $request
     * @return array
     */
    public function productList(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;
            //接收参数
            $input['product_name'] = $request->post('product_name');//产品名称
            $input['product_type'] = $request->post('product_type');//产品类型1次数卡2储值卡
            $input['state']        = $request->post('state');//产品状态1启用0禁用
            $input['page']         = $request->post('page');//当前页
            $input['start']        = $request->post('start');//查询起始时间
            $input['end']          = $request->post('end');//查询结束时间
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'product_name' => function($v){
                    if (null === $v) return '缺少参数1';
                    if ('' !== $v && (mb_strlen($v) < 1 || mb_strlen($v) > 16)) return '产品名称在1到16个字符';
                    return true;
                },
                'product_type' => function($v){
                    if (null === $v) return '缺少参数2';
                    if ('' !== $v && !in_array($v, ['1', '2'], true)) return '不合法的产品类型';
                    return true;
                },
                'state' => function($v){
                    if (null === $v) return '缺少参数3';
                    if ('' !== $v && !in_array($v, ['0', '1'], true)) return '不合法的产品状态';
                    return true;
                },
                'page' => function($v){
                    if (null === $v) return '缺少参数4';
                    if (false == isPosInt($v)) return '不合法的页数';
                    return true;
                },
                'start' => function($v){
                    if (null === $v) return '缺少参数5';
                    if ('' !== $v && false == validateDate($v)) return '不合法的时间';
                    return true;
                },
                'end' => function($v){
                    if (null === $v) return '缺少参数5';
                    if ('' !== $v && false == validateDate($v)) return '不合法的时间';
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new ProductModel();
            return  $model->productList($input);
        }
        return jsonData(400, '非法请求');
    }


    /**为产品提供产品包列表
     * @param Request $request
     * @return array
     */
    public function getPackageList(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;
            //逻辑处理
            $model = new ProductModel();
            return $model->getPackageList();
        }
        return jsonData(400, '非法请求');
    }

    /**添加产品
     * @param Request $request
     * @return array
     */
    public function productAdd(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;
            //接收参数
            $input['product_name']  = $request->post('product_name');//产品名称
            $input['product_desc']  = $request->post('product_desc');//产品描述
            $input['product_num']   = $request->post('product_num');//产品剩余次数
            $input['product_money'] = $request->post('product_money');//产品剩余金额
            $input['product_type']  = $request->post('product_type');//产品类型1次数卡2储值卡
            $input['state']         = $request->post('state');//产品状态1启用0禁用
            $input['package_id']    = $request->post('package_id');//产品包id
            //参数校验
            if (null === $input['product_name']) {
                return jsonData(401, '缺少参数');
            } else if(mb_strlen($input['product_name']) < 1 || mb_strlen($input['product_name']) > 16) {
                return jsonData(402, '产品名称在1到16个字符');
            }
            if (null === $input['product_desc']) {
                return jsonData(403, '缺少参数');
            }
            if (null === $input['product_money']) {
                return jsonData(404, '缺少参数');
            }if (null === $input['product_num']) {
                return jsonData(405, '缺少参数');
            }
            if (null === $input['product_type']) {
                return jsonData(406, '缺少参数');
            } else if(!in_array($input['product_type'], ['1', '2'], true)) {
                return jsonData(407, '不合法的产品类型');
            }
            if (null === $input['state']) {
                return jsonData(408, '缺少参数');
            } else if(!in_array($input['state'], ['0', '1'], true)) {
                return jsonData(409, '不合法的产品包状态');
            }
            if (null === $input['package_id']) {
                return jsonData(410, '缺少参数');
            } else if(!is_numeric($input['package_id']) || strpos($input['package_id'], '.') || $input < 1) {
                return jsonData(411, '不合法的参数');
            }

            switch ($input['product_type']) {
                case '1'://次数卡
                    if ('' !== $input['product_num'] && (!is_numeric($input['product_num']) || $input['product_num'] < 0)) return jsonData(412, '不合法的参数');
                    unset($input['product_money']);
                    break;
                case '2'://储值卡
                    if ('' !== $input['product_money'] && (!is_numeric($input['product_money']) || $input['product_money'] < 0)) return jsonData(413, '不合法的参数');
                    unset($input['product_num']);
                    break;
            }
            //逻辑处理
            $input['create_time'] = time();
            $model = new ProductModel();
            return  $model->productAdd($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改产品状态
     * @param Request $request
     * @return array
     */
    public function changeProductState(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['product_id']  = $request->post('product_id');//产品包名称
            $input['state']         = $request->post('state');//产品包状态1上架0下架
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'state' => function($v){
                    if (null === $v) return '缺少参数';
                    if ('' !== $v && !in_array($v, ['0', '1'], true)) return '不合法的产品状态';
                    return true;
                },
                'product_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v)) return '不合法的参数';
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new ProductModel();
            return $model->changeProductState($input);
        }
        return jsonData(400, '非法请求');
    }

    /**编辑产品
     * @param Request $request
     * @return array
     */
    public function productEdit(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,$this->level);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['product_name']  = $request->post('product_name');//产品名称
            $input['product_desc']  = $request->post('product_desc');//产品描述
            $input['package_id']    = $request->post('package_id');//产品包id
            $input['product_id']    = $request->post('product_id');//产品id
            $input['state']         = $request->post('state');//产品状态1启用0禁用
            $input['product_type']  = $request->post('product_type');//产品类型1次数卡2储值卡
            $input['product_num']   = $request->post('product_num');//产品剩余次数
            $input['product_money'] = $request->post('product_money');//产品剩余金额
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'product_name' => function($v) use(&$input){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        unset($input['product_name']);
                        return true;
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 16) {
                        return '产品名称在1到16个字符';
                    } else {
                        return true;
                    }
                },
                'product_desc' => function($v) use(&$input){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        unset($input['product_desc']);
                        return true;
                    } else {
                        return true;
                    }
                },
                'product_type' => function($v) use(&$input){
                    if (null === $v) {
                        return '缺少参数2';
                    } elseif ('' === $v) {
                        unset($input['product_type']);
                        return true;
                    } elseif (!in_array($v, ['1', '2'], true)) {
                        return '不合法的产品类型';
                    } else {
                        return true;
                    }
                },
                'state' => function($v) use(&$input){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        unset($input['state']);
                        return true;
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的产品状态';
                    } else {
                        return true;
                    }
                },
                'package_id' => function($v) use(&$input){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        unset($input['package_id']);
                        return true;
                    } elseif (false == isPosInt($v)) {
                        return '不合法的参数';
                    } else {
                        return true;
                    }
                },
                'product_id' => function($v) use(&$input){
                    if (null === $v) {
                        return '缺少参数';
                    }elseif (false == isPosInt($v)) {
                        return '不合法的参数';
                    } else {
                        return true;
                    }
                },
                'product_money' => function($v,$all) use(&$input){
                    if (null === $v) {
                        return '缺少参数';
                    } else {
                        switch ($all['product_type']) {
                            case '':
                                unset($input['product_money']);
                                unset($input['product_num']);
                                break;
                            case '1'://1次数卡
                                unset($input['product_money']);
                                break;
                            case '2'://2储值卡
                                unset($input['product_num']);
                                break;
                        }
                        return true;
                    }
                },
                'product_num' => function($v,$all) use(&$input){
                    if (null === $v) {
                        return '缺少参数';
                    } else {
                        switch ($all['product_type']) {
                            case '':
                                unset($input['product_money']);
                                unset($input['product_num']);
                                break;
                            case '1'://1次数卡
                                unset($input['product_money']);
                                break;
                            case '2'://2储值卡
                                unset($input['product_num']);
                                break;
                        }
                        return true;
                    }
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            if (count($input) ==1) return jsonData(401, '请至少编辑一项数据');
            //逻辑处理
            $model = new ProductModel();
            return $model->productEdit($input);
        }
        return jsonData(400, '非法请求');
    }
}