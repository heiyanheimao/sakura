<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/18
 * Time: 10:35
 */

namespace app\back\controller;


use app\back\model\ConfigModel;
use app\back\model\HotelModel;
use think\Request;
use think\Validate;
use think\validate\ValidateRule;

class Hotel extends Base
{
    private $level = 1;

    /**添加酒店
     * @param Request $request
     * @return array
     */
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
            $input['hotel_name']    = $request->post('hotel_name');//酒店名称
            $input['hotel_address'] = $request->post('hotel_address');//酒店地址
            $input['hotel_phone']   = $request->post('hotel_phone');//酒店电话
            $input['content']       = $request->post('content');//酒店内容
            $input['state']         = $request->post('state');//酒店状态 1启用 0 禁用
            $input['allow_order']   = $request->post('allow_order');//是否允许下单 1允许0禁止
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'hotel_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '酒店名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '酒店名称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'hotel_address' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '酒店地址不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 50) {
                        return '酒店地址在1到50个字符';
                    } else {
                        return true;
                    }
                },
                'hotel_phone' => ValidateRule::isRequire(null, '缺少参数')->regex('/^(0[\d]{2,3}-[\d]{7,8})|(1\d{10})$/',
                    '请输入正确的座机号或者手机号'),
                'content' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                },
                'allow_order' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $input['content']     = filterScript($input['content']);
            $input['create_time'] = time();
            $input['hotel_cover'] = '';

            $model = new HotelModel();
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
            $input['hotel_name']  = $request->post('hotel_name');//酒店名称
            $input['hotel_phone'] = $request->post('hotel_phone');//酒店电话
            $input['state']       = $request->post('state');//状态 1启用, 0禁用, -1删除
            $input['allow_order'] = $request->post('allow_order');//是否允许 1允许, 0禁止
            $input['page']        = $request->post('page');//分页
            $input['end']         = $request->post('end');//起始时间
            $input['start']       = $request->post('start');//结束时间
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'hotel_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数1';
                    }
                    return true;
                },
                'hotel_phone' => function ($v) {
                    if (null === $v) {
                        return '缺少参数2';
                    }
                    return true;
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数3';
                    } elseif ('' !== $v && !in_array($v, ['0', '1', '-1'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
                },
                'allow_order' => function ($v) {
                    if (null === $v) {
                        return '缺少参数3';
                    } elseif ('' !== $v && !in_array($v, ['0', '1'], true)) {
                        return '不合法的状态';
                    } else {
                        return true;
                    }
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
            $model = new HotelModel();
            return $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改酒店状态
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
            $input['hotel_id'] = $request->post('hotel_id');//酒店id
            $input['state']    = $request->post('state');//状态
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
                'hotel_id' => function ($v) {
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
            $model = new HotelModel();
            return $model->changeState($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改酒店下单状态
     * @param Request $request
     * @return array
     */
    public function changeAllowOrder(Request $request)
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
            $input['hotel_id']    = $request->post('hotel_id');//酒店id
            $input['allow_order'] = $request->post('allow_order');//状态
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'allow_order' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (!in_array($v, ['0', '1'], true)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'hotel_id' => function ($v) {
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
            $model = new HotelModel();
            return $model->changeAllowOrder($input);
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
        $input['hotel_id'] = $request->post('hotel_id');//酒店id
        //参数校验
        $validate = new Validate();
        $validate->rule([
            'hotel_id' => function ($v) {
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
        $model     = new HotelModel();
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
                if ($coverInfo['data']['hotel_cover'] != '') {
                    @unlink(config('app.static_path') . $coverInfo['data']['hotel_cover']);
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

    /**获取酒店信息
     * @param Request $request
     * @return array
     */
    public function getInfo(Request $request)
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
            $input['hotel_id'] = $request->post('hotel_id');//酒店id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'hotel_id' => function ($v) {
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
            $model = new HotelModel();
            return $model->getInfo($input);
        }
        return jsonData(400, '非法请求');
    }

    /**更新数据
     * @param Request $request
     * @return array
     */
    public function edit(Request $request)
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
            $input['hotel_name']    = $request->post('hotel_name');//酒店名称
            $input['hotel_address'] = $request->post('hotel_address');//酒店地址
            $input['hotel_phone']   = $request->post('hotel_phone');//酒店电话
            $input['content']       = $request->post('content');//酒店内容
            $input['hotel_id']      = $request->post('hotel_id');//酒店id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'hotel_name' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '酒店名称不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 20) {
                        return '酒店名称在1到20个字符';
                    } else {
                        return true;
                    }
                },
                'hotel_address' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '酒店地址不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 50) {
                        return '酒店地址在1到50个字符';
                    } else {
                        return true;
                    }
                },
                'hotel_phone' => ValidateRule::isRequire(null, '缺少参数')->regex('/^(0[\d]{2,3}-[\d]{7,8})|(1\d{10})$/',
                    '请输入正确的座机号或者手机号'),
                'content' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'hotel_id' => function ($v) {
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
            $input['content'] = filterScript($input['content']);
            $model            = new HotelModel();
            return $model->edit($input);
        }
        return jsonData(400, '非法请求');
    }
}