<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/21
 * Time: 16:14
 */

namespace app\back\controller;


use app\back\model\ConfigModel;
use think\Request;
use think\Validate;

class Config extends Base
{
    private $level = 6;
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
            $input['config_name']  = $request->post('config_name');//配置名称
            $input['config_state']  = $request->post('config_state');//配置状态
            $input['page']       = $request->post('page');//分页
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'config_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'config_state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' !== $v && !in_array($v, ['0', '1'], true)) {
                        return '不合法的参数';
                    } else {
                        return true;
                    }
                },
                'page' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的页数';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new ConfigModel();
            return $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改配置状态
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
            $input['config_id'] = $request->post('config_id');//配置id
            $input['config_state']    = $request->post('config_state');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'config_state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (!in_array($v, ['0', '1'], true)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'config_id' => function ($v) {
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
            $model = new ConfigModel();
            return $model->changeState($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改配置描述
     * @param Request $request
     * @return array
     */
    public function editDesc(Request $request)
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
            $input['config_id'] = $request->post('config_id');//配置id
            $input['config_desc']    = $request->post('config_desc');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'config_desc' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'config_id' => function ($v) {
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
            $model = new ConfigModel();
            return $model->editDesc($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改配置值
     * @param Request $request
     * @return array
     */
    public function editValue(Request $request)
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
            $input['config_id'] = $request->post('config_id');//配置id
            $input['config_value']    = $request->post('config_value');//状态
            $input['type']    = $request->post('type');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'config_value' => function ($v, $all) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if ($all['type'] == 'time' && preg_match('/^(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/', $v) == 0) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'config_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'type' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (!in_array($v, ['string', 'time'], true)) {
                        return '不合法的参数';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            unset($input['type']);
            $model = new ConfigModel();
            return $model->editValue($input);
        }
        return jsonData(400, '非法请求');
    }

    /**上传icon
     * @param Request $request
     * @return array|bool|false|mixed|\think\File
     */
    public function uploadIcon(Request $request)
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
        $input['config_id'] = $request->post('config_id');//配置id
        //参数校验
        $validate = new Validate();
        $validate->rule([
            'config_id' => function ($v) {
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
        $model     = new ConfigModel();
        $coverInfo = $model->getIcon($input);
        if (200 != $coverInfo['code']) {
            return $info;
        }
        //上传
        $file = $request->file('file');
        $info = $file->validate([
            'size' => $config[0]['config_value'] * 1024,
            'ext' => $config[1]['config_value']
        ])->move(config('app.upload_path') . 'icon/');
        if ($info) {
            //保存到数据库
            if ($model->updateIcon($input, '/uploads/icon/' . $info->getSaveName())) {
                if ($coverInfo['data']['config_value'] != '') {
                    @unlink(config('app.static_path') . $coverInfo['data']['config_value']);
                }
                return jsonData(200, '上传成功', ['url' => '/uploads/icon/' . $info->getSaveName()]);
            } else {
                @unlink(config('app.upload_path') . 'icon/' . $info->getSaveName());
                return jsonData(403, '上传失败', []);
            }
        } else {
            // 上传失败获取错误信息
            return jsonData(402, $file->getError());
        }
    }
}