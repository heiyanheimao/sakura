<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/15
 * Time: 16:22
 */

namespace app\back\controller;


use app\back\model\ArticleModel;
use app\back\model\ConfigModel;
use think\Request;
use think\Validate;

class Article extends Base
{
    private $level = 1;

    /**添加文章
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
            $input['article_back']   = $request->post('article_back');//文章封底
            $input['content']        = $request->post('content');//文章内容
            $input['article_source'] = $request->post('article_source');//文章来源
            $input['article_title']  = $request->post('article_title');//文章标题
            $input['is_recommend']   = $request->post('is_recommend');//是否推荐 1推荐, 0不推荐
            $input['state']          = $request->post('state');//是否启用 1启用, 0禁用, -1删除
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'article_back' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return true;
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 200) {
                        return '文章封底在1到200个字符';
                    } else {
                        return true;
                    }
                },
                'content' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '内容不能为空';
                    } else {
                        return true;
                    }
                },
                'article_source' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return true;
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 10) {
                        return '文章来源在1到10个字符';
                    } else {
                        return true;
                    }
                },
                'article_title' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '标题不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 32) {
                        return '文章标题在1到10个字符';
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
                },
                'is_recommend' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的文章推荐';
                    } else {
                        return true;
                    }
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $input['content']     = filterScript($input['content']);
            $input['category_id'] = 2;
            $input['create_time'] = time();
            $model                = new ArticleModel();
            return $model->add($input);
        }
        return jsonData(400, '非法请求');
    }

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
            $input['article_title'] = $request->post('article_title');//文章标题
            $input['state']         = $request->post('state');//是否启用 1启用, 0禁用, -1删除
            $input['is_recommend']  = $request->post('is_recommend');//是否推荐 1推荐, 0不推荐
            $input['category_id']   = $request->post('category_id');//分类
            $input['page']          = $request->post('page');//分页
            $input['start']         = $request->post('start');//创建起始时间
            $input['end']           = $request->post('end');//创建结束时间
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'article_title' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    return true;
                },
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' !== $v && !in_array($v, ['0', '1'], true)) {
                        return '不合法的文章状态';
                    } else {
                        return true;
                    }
                },
                'is_recommend' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' !== $v && !in_array($v, ['0', '1'], true)) {
                        return '不合法的文章推荐';
                    } else {
                        return true;
                    }
                },
                'category_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' !== $v && !in_array($v, ['1', '2'], true)) {
                        return '不合法的文章分类';
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
                'start' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if ('' !== $v && false == validateDate($v)) {
                        return '不合法的时间';
                    }
                    return true;
                },
                'end' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if ('' !== $v && false == validateDate($v)) {
                        return '不合法的时间';
                    }
                    return true;
                },
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model = new ArticleModel();
            return $model->getList($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改文章是否推荐
     * @param Request $request
     * @return array
     */
    public function changeRecommend(Request $request)
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
            $input['article_id']   = $request->post('article_id');//文章id
            $input['is_recommend'] = $request->post('is_recommend');//是否推荐
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'is_recommend' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (!in_array($v, ['推荐', '不推荐'], true)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'article_id' => function ($v) {
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
            $input['is_recommend'] = $input['is_recommend'] == '推荐' ? 0 : 1;
            $model                 = new ArticleModel();
            return $model->changeRecommend($input);
        }
        return jsonData(400, '非法请求');
    }

    /**修改文章状态
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
            $input['article_id'] = $request->post('article_id');//文章id
            $input['state']      = $request->post('state');//是否推荐
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'state' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (!in_array($v, ['已启用', '已禁用'], true)) {
                        return '不合法的参数';
                    }
                    return true;
                },
                'article_id' => function ($v) {
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
            if ($input['state'] == '已启用') {
                $input['state'] = 0;
            } else {
                $input['state'] = -1;
            }
            $model = new ArticleModel();
            return $model->changeState($input);
        }
        return jsonData(400, '非法请求');
    }

    /**删除文章
     * @param Request $request
     * @return array
     */
    public function del(Request $request)
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
            $input['article_id'] = $request->post('article_id');//文章id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'article_id' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    }
                    if (false == isPosInt($v)) {
                        return '不合法的参数';
                    }
                    if ($v <= 10) {
                        return '非疗养科普文章不能删除';
                    }
                    return true;
                }
            ]);
            if (!$validate->check($input)) {
                return jsonData(401, $validate->getError());
            }
            //逻辑处理
            $model          = new ArticleModel();
            $input['state'] = -1;
            return $model->del($input);
        }
        return jsonData(400, '非法请求');
    }

    /**显示文章内容
     * @param Request $request
     * @return array
     */
    public function showArticle(Request $request)
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
            $input['article_id'] = $request->post('article_id');//文章id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'article_id' => function ($v) {
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
            $model = new ArticleModel();
            return $model->showArticle($input);
        }
        return jsonData(400, '非法请求');
    }

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
        $input['article_id'] = $request->post('id');//文章id
        //参数校验
        $validate = new Validate();
        $validate->rule([
            'article_id' => function ($v) {
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
        $model     = new ArticleModel();
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
            if ($model->updateCover($input, config('app.upload_host') . 'uploads/cover/' . $info->getSaveName())) {
                if ($coverInfo['data']['article_cover'] != '') {
                    //获取路径
                    $path = explode('/', $coverInfo['data']['article_cover']);
                    unset($path[0]);
                    unset($path[1]);
                    unset($path[2]);
                    unset($path[3]);
                    @unlink(config('app.upload_path') . implode('/', $path));
                }
                return jsonData(200, '上传成功', []);
            } else {
                @unlink(config('app.upload_path') . 'cover/' . $info->getSaveName());
                return jsonData(403, '上传失败', []);
            }
        } else {
            // 上传失败获取错误信息
            return jsonData(402, $file->getError());
        }

    }

    /**
     * @param Request $request
     * @return array
     */
    public function getArticle(Request $request)
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
            $input['article_id'] = $request->post('article_id');//文章id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'article_id' => function ($v) {
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
            $model = new ArticleModel();
            return $model->getArticle($input);
        }
        return jsonData(400, '非法请求');
    }

    /**添加文章
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
            $input['article_back']   = $request->post('article_back');//文章封底
            $input['content']        = $request->post('content');//文章内容
            $input['article_source'] = $request->post('article_source');//文章来源
            $input['article_title']  = $request->post('article_title');//文章标题
            $input['is_recommend']   = $request->post('is_recommend');//是否推荐 1推荐, 0不推荐
            $input['state']          = $request->post('state');//是否启用 1启用, 0禁用, -1删除
            $input['article_id']     = $request->post('article_id');//文章id
            //参数校验
            $validate = new Validate();
            $validate->rule([
                'article_back' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return true;
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 200) {
                        return '文章封底在1到200个字符';
                    } else {
                        return true;
                    }
                },
                'content' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '内容不能为空';
                    } else {
                        return true;
                    }
                },
                'article_source' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return true;
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 10) {
                        return '文章来源在1到10个字符';
                    } else {
                        return true;
                    }
                },
                'article_title' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif ('' === $v) {
                        return '标题不能为空';
                    } elseif (mb_strlen($v) < 1 || mb_strlen($v) > 32) {
                        return '文章标题在1到10个字符';
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
                },
                'is_recommend' => function ($v) {
                    if (null === $v) {
                        return '缺少参数';
                    } elseif (!in_array($v, ['0', '1'], true)) {
                        return '不合法的文章推荐';
                    } else {
                        return true;
                    }
                },
                'article_id' => function ($v) {
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
            $input['content'] = filterScript($input['content']);
            $model            = new ArticleModel();
            return $model->update($input);
        }
        return jsonData(400, '非法请求');
    }
}