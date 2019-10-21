<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/11
 * Time: 10:41
 */

namespace app\back\controller;


use app\back\model\CheckinModel;
use think\Request;
use think\Validate;

/**用户预约
 * Class CheckinGreen
 * @package app\back\controller
 */
class Checkin extends Base
{
    private $level = 4;

    /**获取预约列表
     * @param Request $request
     * @return array
     */
    public function getLists(Request $request)
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
            $input['checkin_name']  = $request->post('checkin_name');//管理员账号
            $input['checkin_phone'] = $request->post('checkin_phone');//管理员名称
            $input['page']          = $request->post('page');//当前页
            $input['start']         = $request->post('start');//开始时间
            $input['end']           = $request->post('end');//截止时间
            $input['checkin_type']  = $request->post('checkin_type');//预约类型1绿色预约2健康预约
            $input['state']         = $request->post('state');//预约状态
            //参数校验
            if (!in_array($input['checkin_type'], ['1', '2'], true)) {
                return jsonData(401, '请输入一个合法参数');
            }
            //逻辑处理
            $model = new CheckinModel();
            $rtn   = $model->getList($input);
            if (false === $rtn) {
                return jsonData(300, '服务内部错误~');
            }
            if ([] == $rtn['list']) {
                return jsonData(401, '暂无数据');
            }
            //重组数据
            foreach ($rtn['list'] as $k => $list) {
                $rtn['list'][$k]['checkin_time'] = date('Y-m-d H:i:s', $rtn['list'][$k]['checkin_time']);
                switch ($input['checkin_type']) {
                    case 1:
                        $rtn['list'][$k]['checkin_type'] = '绿色预约';
                        break;
                    case 2:
                        $rtn['list'][$k]['checkin_type'] = '健康预约';
                        break;
                }

                switch ($rtn['list'][$k]['state']) {
                    case 2:
                        $rtn['list'][$k]['state'] = '未确认';
                        break;
                    case 1:
                        $rtn['list'][$k]['state'] = '已确认';
                        break;
                    case 0:
                        $rtn['list'][$k]['state'] = '已取消';
                        break;
                }
            }
            return jsonData(200, '获取数据成功', $rtn);
        }
        return jsonData(400, '非法请求');
    }

    /**修改状态
     * @param Request $request
     * @return array
     */
    public function changeStatus(Request $request)
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
            $input['state']         = $request->post('state');//状态变更  确认  取消
            $input['checkin_id']    = $request->post('checkin_id');//预约id
            $input['type']          = $request->post('type');//预约类型 绿色预约 健康预约
            $input['cancel_reason'] = $request->post('cancel_reason');//取消原因
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'state' => function ($v) {
                    if ($v == '确认' || $v == '取消') {
                        return true;
                    }
                    return '请给一个合法的数据';
                },
                'checkin_id' => function ($v) {
                    if (!is_numeric($v) || $v < 0) {
                        return '请给一个合法的数据';
                    }
                    return true;
                },
                'type' => function ($v) {
                    if ($v == '绿色预约' || $v == '绿色预约') {
                        return true;
                    }
                    return '请给一个合法的数据';
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new CheckinModel();
            switch ($input['state']) {
                case '确认':
                    $input['state'] = 1;
                    break;
                case '取消':
                    $input['state'] = 0;
                    break;
            }
            if (null == $input['cancel_reason']) {
                $input['cancel_reason'] = '';
            }
            return $model->changeStatus($input);
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
            $input['checkin_name']  = $request->post('checkin_name');//预约人名称
            $input['checkin_phone'] = $request->post('checkin_phone');//预约人电话
            $input['checkin_time']  = $request->post('checkin_time');//预约时间
            $input['checkin_id']    = $request->post('checkin_id');//预约id
            $input['type']          = $request->post('type');//预约类型
            //参数校验
            if ($input['checkin_name'] == '' && $input['checkin_phone'] == '' && $input['checkin_time'] == '') {
                return jsonData(401, '请至少填写一个合法数据');
            }
            if ($input['checkin_id'] === null) {
                return jsonData(401, '缺少参数');
            }
            if (!in_array($input['type'], ['1', '2'], true)) {
                return jsonData(401, '不合法的参数');
            }

            //逻辑处理
            $model = new CheckinModel();
            if ($input['checkin_name'] === '') {
                unset($input['checkin_name']);
            }
            if ($input['checkin_phone'] === '') {
                unset($input['checkin_phone']);
            }
            if ($input['checkin_time'] === '') {
                unset($input['checkin_time']);
            } else {
                $input['checkin_time'] = strtotime($input['checkin_time']);
            }
            return $model->update($input);
        }
        return jsonData(400, '非法请求');
    }
}