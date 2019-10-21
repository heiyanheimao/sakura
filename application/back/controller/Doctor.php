<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/17
 * Time: 14:35
 */

namespace app\back\controller;


use app\back\model\ConfigModel;
use app\back\model\DoctorModel;
use think\Request;
use think\Validate;

class Doctor extends Base
{
    private $level = 1;
    public function getCategoryInfo(Request $request)
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

            //逻辑处理
            $model = new DoctorModel();
            return $model->getCategoryInfo();
        }
        return jsonData(400, '非法请求');
    }

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
            $input['category_id']     = $request->post('category_id');//分类id
            $input['content']         = $request->post('content');//医生介绍
            $input['doctor_name']     = $request->post('doctor_name');//医生名称
            $input['doctor_position'] = $request->post('doctor_position');//医生职位
            $input['state']           = $request->post('state');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'category_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v, false)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'content' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'doctor_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '医生名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '医生名称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'doctor_position' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '医生职称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '医生职称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
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
            $input['content']      = filterScript($input['content']);
            $input['create_time']  = time();
            $input['doctor_cover'] = '';
            $model                 = new DoctorModel();
            return $model->add($input);
        }
        return jsonData(400, '非法请求');
    }

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
            $input['doctor_name']     = $request->post('doctor_name');//医生名称
            $input['doctor_position'] = $request->post('doctor_position');//医生职位
            $input['state']           = $request->post('state');//是否启用 1启用, 0禁用, -1删除
            $input['category_id']     = $request->post('category_id');//分类id
            $input['page']            = $request->post('page');//分页
            $input['end']             = $request->post('end');//起始时间
            $input['start']           = $request->post('start');//结束时间
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'doctor_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数1';
                    }
                    return true;
                },
                'doctor_position' => function ($v) {
                    if (null === $v) {
                        return '缺少参数2';
                    }
                    return true;
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数3';
                    } elseif ('' !== $v && !in_array($v, ['0', '1', '-1'], true)) {
                        return '不合法的文章状态';
                    } else {
                        return true;
                    }
                },
                'category_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数4';
                    }
                    if ('' !== $v && false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'page' => function ($v) {
                    if (null === $v) {
                        return '缺少参数5';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的页数';
                    }
                    return true;
                },
                'start' => function ($v) {
                    if (null === $v) {
                        return '缺少参数6';
                    }
                    if ('' !== $v && false == validateDate($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'end' => function ($v) {
                    if (null === $v) {
                        return '缺少参数7';
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
            $model = new DoctorModel();
            return $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改医生状态
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
            $input['doctor_id'] = $request->post('doctor_id');//分类id
            $input['state']     = $request->post('state');//状态
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
                'doctor_id' => function ($v) {
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
            $model = new DoctorModel();
            return $model->changeState($input);
        }
        return jsonData(400, '非法请求');
    }

    /**上传封面
     * @param Request $request
     * @return array|bool|false|mixed|\think\File
     */
    public function uploadCover(Request $request)
    {
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
        $input['doctor_id'] = $request->post('doctor_id');//医生id
        //参数校验
        $validate = new Validate();
        $validate->rule([
            'doctor_id' => function ($v) {
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
        //获取配置
        $config = (new ConfigModel())->getConfig(['IMG_MAX', 'IMG_TYPE']);
        if (false == $config) {
            return jsonData(401, '未获取到配置项');
        }
        //获取文章原始封面
        $model     = new DoctorModel();
        $coverInfo = $model->getCover($input);
        if (200 != $coverInfo['code']) {
            return $info;
        }
        //上传
        $file = $request->file('file');
        $info = $file->validate([
            'size' => $config[0]['config_value'] * 1024,
            'ext' => $config[1]['config_value']
        ])->move(config('app.upload_path') . 'cover/');
        if ($info) {
            //保存到数据库
            if ($model->updateCover($input, '/uploads/cover/' . $info->getSaveName())) {
                if ($coverInfo['data']['doctor_cover'] != '') {
                    @unlink(config('app.static_path') . $coverInfo['data']['doctor_cover']);
                }
                return jsonData(200, '上传成功', ['url' => '/uploads/cover/' . $info->getSaveName()]);
            } else {
                @unlink(config('app.upload_path') . 'cover/' . $info->getSaveName());
                return jsonData(403, '上传失败', []);
            }
        } else {
            // 上传失败获取错误信息
            return jsonData(402, $file->getError());
        }
    }

    /**获取个人信息以及分类信息
     * @param Request $request
     * @return array
     */
    public function getInfos(Request $request)
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
            $input['doctor_id'] = $request->post('doctor_id');//父级id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'doctor_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new DoctorModel();
            return $model->getInfos($input);
        }
        return jsonData(400, '非法请求');
    }

    /**更新数据
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
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
            $input['category_id']     = $request->post('category_id');//分类id
            $input['content']         = $request->post('content');//医生介绍
            $input['doctor_name']     = $request->post('doctor_name');//医生名称
            $input['doctor_position'] = $request->post('doctor_position');//医生职位
            $input['doctor_id']       = $request->post('doctor_id');//医生职位
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'category_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v, false)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'doctor_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v, false)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'content' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'doctor_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '医生名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '医生名称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'doctor_position' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '医生职称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '医生职称在1到20个字符';
                    } else {
                        return true;
                    }
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $input['content'] = filterScript($input['content']);
            $model            = new DoctorModel();
            return $model->update($input);
        }
        return jsonData(400, '非法请求');
    }
}