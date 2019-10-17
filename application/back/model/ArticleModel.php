<?php
/**
 * Created by PhpStorm.
 * User: HeiYanHeiMao
 * Date: 2019/10/15
 * Time: 10:58
 */

namespace app\back\model;


use think\Db;
use think\facade\Session;

class ArticleModel
{
    private $table = 'skr_article';

    /**添加文章
     * @param $input
     * @return array
     */
    public function add($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            $id = Db::table($this->table)->insertGetId($input);
            if ($id >=11) {
                AdminLogModel::writeLog($guy, '添加疗养文章', json_encode($input), '添加成功');
                return jsonData(200, '添加疗养文章成功');
            }
            AdminLogModel::writeLog($guy, '添加疗养文章', json_encode($input), '添加失败');
            return jsonData(400, '添加疗养文章失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '添加疗养文章', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取文章列表
     * @param $input
     * @return array
     */
    public function getList($input)
    {
        try {
            //获取总页数
            $db = Db::table($this->table);

            if ($input['article_title'] != '')  $db->where('article_title', 'like', "%{$input['article_title']}%");
            if ($input['state'] !== '')         $db->where('state', $input['state']);
            if ($input['is_recommend'] !== '')  $db->where('is_recommend', $input['is_recommend']);
            if ($input['category_id'] !== '')   $db->where('category_id', $input['category_id']);
            if ($input['start'] != '')          $db->where('create_time', '>=', strtotime($input['start']));
            if ($input['end'] != '')            $db->where('create_time', '<=', strtotime($input['end']));
            $pageAll = ceil($db->count()/10);
            //获取数据
            $db = Db::table($this->table);
            if ($input['article_title'] != '')  $db->where('article_title', 'like', "%{$input['article_title']}%");
            if ($input['state'] !== '')         $db->where('state', $input['state']);
            if ($input['is_recommend'] !== '')  $db->where('is_recommend', $input['is_recommend']);
            if ($input['category_id'] !== '')   $db->where('category_id', $input['category_id']);
            if ($input['start'] != '')          $db->where('create_time', '>=', strtotime($input['start']));
            if ($input['end'] != '')            $db->where('create_time', '<=', strtotime($input['end']));

            $list = $db->field('article_id,category_id,article_title,article_cover,article_back,article_source,is_recommend,create_time,state')
                ->page($input['page'],10)->order(['category_id','state' => 'desc','create_time' => 'desc'])->select();
            if ($list == []) {
                return jsonData(201,'暂无数据');
            }
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $list[$k]['create_time']);
                switch ($list[$k]['category_id']) {
                    case 1:
                        $list[$k]['category_id'] = '公司简介';
                        break;
                    case 2:
                        $list[$k]['category_id'] = '疗养科普';
                        break;
                }
                switch ($list[$k]['state']) {
                    case 0:
                        $list[$k]['state'] = '已禁用';
                        break;
                    case 1:
                        $list[$k]['state'] = '已启用';
                        break;
                    case -1:
                        $list[$k]['state'] = '已删除';
                        break;
                }
                switch ($list[$k]['is_recommend']) {
                    case 1:
                        $list[$k]['is_recommend'] = '推荐';
                        break;
                    case 0:
                        $list[$k]['is_recommend'] = '不推荐';
                        break;
                }
            }
            return jsonData(200,'查询成功', ['pageAll' => $pageAll, 'list' => $list, 'page' => $input['page']]);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改文章是否推荐
     * @param $input
     * @return array
     */
    public function changeRecommend($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('is_recommend')->where('article_id', $input['article_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前文章');
            if ($input['is_recommend'] == $info['is_recommend']) return jsonData(402, '修改状态与当前状态一致，不能修改');
            $rtn = Db::table($this->table)->update($input);
            if ($rtn >=1) {
                AdminLogModel::writeLog($guy, '修改文章推荐状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改文章推荐状态', json_encode($input), '修改失败');
            return jsonData(403,'状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改文章推荐状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**修改文章状态
     * @param $input
     * @return array
     */
    public function changeState($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('state')->where('article_id', $input['article_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前文章');
            if ($input['state'] == $info['state']) return jsonData(402, '修改状态与当前状态一致，不能修改');
            $rtn = Db::table($this->table)->update($input);
            if ($rtn >=1) {
                AdminLogModel::writeLog($guy, '修改文章状态', json_encode($input), '修改成功');
                return jsonData(200, '状态修改成功');
            }
            AdminLogModel::writeLog($guy, '修改文章状态', json_encode($input), '修改失败');
            return jsonData(403,'状态修改失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '修改文章状态', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }

    /**删除文章
     * @param $input
     * @return array
     */
    public function del($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('state')->where('article_id', $input['article_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前文章');
            if (-1 == $info['state']) return jsonData(402, '文章已删除不能再次删除');
            $rtn = Db::table($this->table)->update($input);
            if ($rtn >=1) {
                AdminLogModel::writeLog($guy, '删除文章', json_encode($input), '删除成功');
                return jsonData(200, '删除文章成功');
            }
            AdminLogModel::writeLog($guy, '删除文章', json_encode($input), '删除失败');
            return jsonData(403,'删除失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '删除文章', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
    /**获取文章内容
     * @param $input
     * @return array
     */
    public function showArticle($input)
    {
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('content')->where('article_id', $input['article_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前文章内容');

            return jsonData(200,'获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**获取文章封面
     * @param $input
     * @return array
     */
    public function getCover($input)
    {
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('article_cover')->where('article_id', $input['article_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前文章封面');

            return jsonData(200,'获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新封面
     * @param $input
     * @param $cover
     */
    public function updateCover($input,$cover)
    {
        $guy = Session::get('info.admin_account');
        try {
            $data['article_id'] = $input['article_id'];
            $data['article_cover'] = $cover;
            $rtn = Db::table($this->table)->update($data);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新文章封面', json_encode(array_merge($input,['article_cover' => $cover])), '更新成功');
                return true;
            }
            AdminLogModel::writeLog($guy, '更新文章封面', json_encode($input), '更新失败');
            return false;
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新文章封面', json_encode($input), '服务内部错误~');
            return false;
        }
    }

    /**获取单个文章信息
     * @param $input
     * @return array
     */
    public function getArticle($input)
    {
        try{
            //查询产品当前状态
            $info = Db::table($this->table)->field('article_id,category_id,article_title,article_cover,article_back,article_source,is_recommend,state,content')->where('article_id', $input['article_id'])->find();
            if (null == $info) return jsonData(401,'未找到当前文章内容');

            return jsonData(200,'获取成功', $info);
        } catch (\Exception $e) {
            return jsonData(301, '服务内部错误~');
        }
    }

    /**更新文章
     * @param $input
     */
    public function update($input)
    {
        $guy = Session::get('info.admin_account');
        try{
            $rtn = Db::table($this->table)->update($input);
            if ($rtn == 1) {
                AdminLogModel::writeLog($guy, '更新文章', json_encode($input), '更新成功');
                return jsonData(200, '更新文章成功');
            }
            AdminLogModel::writeLog($guy, '更新文章', json_encode($input), '更新失败');
            return jsonData(400, '更新文章失败');
        } catch (\Exception $e) {
            AdminLogModel::writeLog($guy, '更新文章', json_encode($input), '服务内部错误~');
            return jsonData(301, '服务内部错误~');
        }
    }
}