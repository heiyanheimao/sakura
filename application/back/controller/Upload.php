<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/14
 * Time: 16:43
 */

namespace app\back\controller;


use app\back\model\ConfigModel;
use think\Request;

class Upload extends Base
{
    public function uploadImg(Request $request)
    {
        //获取配置
        $config = (new ConfigModel())->getConfig(['IMG_MAX','IMG_TYPE']);
        if (false == $config) return json_encode(['errno' => 1, 'msg' =>'未获取到配置项']);
        $data = [];
        foreach ($request->file() as $file) {
            $info = $file->validate(['size'=>$config[0]['config_value'] *1024,'ext'=>$config[1]['config_value']])->move( config('app.upload_path'));
            if($info){
                $data[] = config('app.upload_host') . 'uploads/'.$info->getSaveName();
            }else{
                // 上传失败获取错误信息
                return json_encode(['errno' => 1, 'msg' =>$file->getError()]);
            }
        }
        return json_encode(['errno' => 0, 'data' =>$data]);
    }

}