<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/16
 * Time: 15:44
 */

namespace app\back\controller;


use app\back\model\ConfigModel;
use app\back\model\DoctorCategoryModel;
use think\Request;
use think\Validate;

class DoctorCategory extends Base
{
    public function getTop(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;

            //逻辑处理
            $model = new DoctorCategoryModel();
            return  $model->getTop();
        }
        return jsonData(400, '非法请求');
    }

    public function add(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;
            //接收参数
            $input['category_id']  = $request->post('category_id');//父级id
            $input['category_name']       = $request->post('category_name');//分类名称
            $input['state']= $request->post('state');//分类状态
            $input['type'] = $request->post('type');//分类类型1一级分类2二级分类
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'category_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v,false)) return '不合法的参数';
                    return true;
                },
                'category_name' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '分类名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 16) {
                        return '分类名称在1到16个字符';
                    } else {
                        return true;
                    }
                },
                'state' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的文章状态';
                    } else {
                        return true;
                    }
                },
                'type' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['1', '2'], true)) {
                        return '不合法的文章状态';
                    } else {
                        return true;
                    }
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            if ($input['type'] == 1) {
                $input['parent_id'] = 0;
            }else {
                $input['parent_id'] = $input['category_id'];
            }
            unset($input['category_id']);
            unset($input['type']);
            $model = new DoctorCategoryModel();
            return  $model->add($input);
        }
        return jsonData(400, '非法请求');
    }

    public function getList(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;
            //接收参数
            $input['category_name'] = $request->post('category_name');//文章标题
            $input['state']         = $request->post('state');//是否启用 1启用, 0禁用, -1删除
            $input['parent_id']   = $request->post('parent_id');//父级id
            $input['page']          = $request->post('page');//分页
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'category_name' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'state' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' !== $v && !in_array($v, ['0', '1','-1'], true)) {
                        return '不合法的分类状态';
                    } else {
                        return true;
                    }
                },
                'page' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v)) return '不合法的页数';
                    return true;
                },
                'parent_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if ('' !== $v && false == isPosInt($v)) return '不合法的参数';
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new DoctorCategoryModel();
            return  $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改分类状态
     * @param Request $request
     * @return array
     */
    public function changeState(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;

            //接收参数
            $input['category_id']  = $request->post('category_id');//分类id
            $input['state']= $request->post('state');//是否推荐
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'state' => function($v){
                    if (null === $v) return '缺少参数';
                    if (!in_array($v, ['0', '1','-1'], true)) return '不合法的参数';
                    return true;
                },
                'category_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v)) return '不合法的参数';
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new DoctorCategoryModel();
            return $model->changeState($input);
        }
        return jsonData(400, '非法请求');
    }

    public function uploadCover(Request $request)
    {
        //登录态检测
        if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
        //权限检测
        $auth = self::checkAuth($info,1);
        if (200 != $auth['code']) return $auth;

        //接收参数
        $input['category_id']  = $request->post('id');//文章id
        //参数校验
        $validate = new Validate();
        $validate->rule([
            'category_id' => function($v){
                if (null === $v) return '缺少参数';
                if (false == isPosInt($v)) return '不合法的参数';
                return true;
            }
        ]);
        if (!$validate->check($input)) {
            return jsonData(401, $validate->getError());
        }
        //获取配置
        $config = (new ConfigModel())->getConfig(['IMG_MAX','IMG_TYPE']);
        if (false == $config) return jsonData(401, '未获取到配置项');
        //获取文章原始封面
        $model = new DoctorCategoryModel();
        $coverInfo = $model->getCover($input);
        if (200 != $coverInfo['code']) return $info;
        //上传
        $file = $request->file('file');
        $info = $file->validate(['size'=>$config[0]['config_value'] *1024,'ext'=>$config[1]['config_value']])->move( config('app.upload_path') .'cover/');
        if($info){
            //保存到数据库
            if ($model->updateCover($input,'/uploads/cover/' . $info->getSaveName())) {
                if ($coverInfo['data']['category_cover'] != '') @unlink(config('app.static_path') . $coverInfo['data']['category_cover']);
                return jsonData(200, '上传成功',    []);
            } else {
                @unlink(config('app.upload_path') . 'cover/'.$info->getSaveName());
                return jsonData(403, '上传失败',    []);
            }
        }else{
            // 上传失败获取错误信息
            return jsonData(402, $file->getError());
        }

    }

    public function getInfo(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;
            //接收参数
            $input['category_id']   = $request->post('category_id');//父级id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'category_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v)) return '不合法的参数';
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new DoctorCategoryModel();
            return  $model->getInfo($input);
        }
        return jsonData(400, '非法请求');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        if (self::checkRequest($request)) {
            //登录态检测
            if (!($info = self::getStatus())) return jsonData(500, '登录态失效，请重新登录');
            //权限检测
            $auth = self::checkAuth($info,1);
            if (200 != $auth['code']) return $auth;
            //接收参数
            $input['category_id']  = $request->post('category_id');//分类id
            $input['parent_id']  = $request->post('parent_id');//父级id
            $input['category_name']       = $request->post('category_name');//分类名称
            $input['type'] = $request->post('type');//分类类型1一级分类2二级分类
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'category_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v,false)) return '不合法的参数';
                    return true;
                },
                'category_name' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '分类名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 16) {
                        return '分类名称在1到16个字符';
                    } else {
                        return true;
                    }
                },
                'parent_id' => function($v){
                    if (null === $v) return '缺少参数';
                    if (false == isPosInt($v,false)) return '不合法的参数';
                    return true;
                },
                'type' => function($v){
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['1', '2'], true)) {
                        return '不合法的文章状态';
                    } else {
                        return true;
                    }
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            if ($input['type'] == 1) {
                $input['parent_id'] = 0;
            }
            unset($input['type']);
            $model = new DoctorCategoryModel();
            return  $model->update($input);
        }
        return jsonData(400, '非法请求');
    }
}