<?php
/**
 * User: LiuTong
 * Date: 2017-07-27
 * Time: 14:11
 */

namespace app\admin\model;

use think\Model;

class HomeIco extends Model
{
    /**
     * 首页图标列表
     * Date: 2017-07-27
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getHomeIcoList()
    {
        $where['status'] = ['exp', 'in(0,1)'];
        $data = db('home_ico')->where($where)->order('sort ASC')->select();
        if ($data) {
//            模块类型1品牌课程2福利专区3妈妈约4团购5万人砍6vip7女王权杖8顾问入口9星星点灯
            foreach ($data as $k => $v) {
                switch ($v['type']) {
                    case 1:
                        $data[$k]['typeName'] = '品牌课程';
                        break;
                    case 2:
                        $data[$k]['typeName'] = '福利专区';
                        break;
                    case 3:
                        $data[$k]['typeName'] = '妈妈约';
                        break;
                    case 4:
                        $data[$k]['typeName'] = '团购';
                        break;
                    case 5:
                        $data[$k]['typeName'] = '万人砍';
                        break;
                    case 6:
                        $data[$k]['typeName'] = 'VIP';
                        break;
                    case 7:
                        $data[$k]['typeName'] = '女王权杖';
                        break;
                    case 8:
                        $data[$k]['typeName'] = '顾问入口';
                        break;
                    case 9:
                        $data[$k]['typeName'] = '星星点灯';
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * 图标新增保存
     * Date: 2017-07-27
     * @param $data
     * @return bool
     */
    public function saveIco($data)
    {
        try {
            $data['createTime'] = time();
            $data['status'] = 0;
            db('home_ico')->insert($data);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 判断是否存在同类以启用图标
     * Date: 2017-07-27
     * @param $id
     * @return bool
     */
    public function ifIcoHave($id)
    {
        $type = db('home_ico')->where(['id' => $id])->value('type');
        $ret = db('home_ico')->where(['type' => $type, 'status' => 1])->find();
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 图标启用
     * Date: 2017-07-27
     * @param $id
     * @return int|string
     */
    public function icoStart($id)
    {
        $ret = db('home_ico')->where(['id' => $id])->update(['status' => 1]);
        return $ret;
    }

    /**
     * 图标停用
     * Date: 2017-07-27
     * @param $id
     * @return int|string
     */
    public function icoStop($id)
    {
        $ret = db('home_ico')->where(['id' => $id])->update(['status' => 0]);
        return $ret;
    }

    /**
     * 图标删除
     * Date: 2017-07-27
     * @param $id
     * @return int|string
     */
    public function icoDel($id)
    {
        $ret = db('home_ico')->where(['id' => $id])->update(['status' => 2]);
        return $ret;
    }

    /**
     * 图标详情
     * Date: 2017-07-27
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getIcoDetail($id)
    {
        $data = db('home_ico')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * 图标修改保存
     * Date: 2017-07-27
     * @param $data
     * @param $id
     * @return int|string
     */
    public function saveIcoEdit($data, $id)
    {
        $data['createTime'] = time();
        $data['status'] = 0;// 修改后需要重新启用 避免更改类型是出现冲突
        $ret = db('home_ico')->where(['id' => $id])->update($data);
        return $ret;
    }
}