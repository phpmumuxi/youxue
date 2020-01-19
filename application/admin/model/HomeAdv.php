<?php
/**
 * User: LiuTong
 * Date: 2017-07-25
 * Time: 9:56
 */

namespace app\admin\model;

use think\Model;

class HomeAdv extends Model
{
    /**
     * 广告列表
     * Date: 2017-07-25
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAdvList()
    {
        $data = db('advertise')->where(['isDelete' => 0])->order('sort ASC')->select();
        if ($data) {

            foreach ($data as $k => $v) {
                //类型1普通广告(h5)2女王3福利4优选品牌5万人砍6团购7vip
                switch ($v['type']) {
                    case 1:
                        $data[$k]['typeName'] = '普通广告';
                        break;
                    case 2:
                        $data[$k]['typeName'] = '女王权杖';
                        break;
                    case 3:
                        $data[$k]['typeName'] = '福利专区';
                        break;
                    case 4:
                        $data[$k]['typeName'] = '优选品牌';
                        break;
                    case 5:
                        $data[$k]['typeName'] = '万人砍';
                        break;
                    case 6:
                        $data[$k]['typeName'] = '团购';
                        break;
                    case 7:
                        $data[$k]['typeName'] = 'VIP';
                        break;
                    case 8:
                        $data[$k]['typeName'] = '星星点灯';
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * 广告图文详情
     * Date: 2017-07-25
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAdvDetail($id)
    {
        $data = db('advertise')->field('id,url,img,type,createTime,content,sort')->where(['id' => $id])->find();
        if ($data) {
            if (!empty($data['content'])) {
                $data['content'] = htmlspecialchars_decode($data['content']);
            }
        }
        return $data;
    }

    /**
     * 广告新增保存
     * Date: 2107-07-25
     * @return bool
     */
    public function saveAdv($data)
    {
        try {
            $data['createTime'] = time();
            $data['isDelete'] = 0;
            $id = db('advertise')->insertGetId($data);
            if ($data['type'] == 1) {
                $url = ['url' => '/advertise.html?id=' . $id];
                db('advertise')->where(['id' => $id])->update($url);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 广告修改保存
     * Date: 2107-07-25
     * @return bool
     */
    public function saveEidtAdv($data, $id)
    {
        try {
            $data['createTime'] = time();
            if ($data['type'] == 1) {
                $url = ['url' => '/advertise.html?id=' . $id];
            }
            db('advertise')->where(['id' => $id])->update($data);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 广告删除
     * Date: 2107-07-25
     */
    public function delAdv($id)
    {
        $ret = db('advertise')->where(['id' => $id])->update(['isDelete' => 1]);
        return $ret;
    }

    public function advHave()
    {
        $data = db('advertise')->where(['isDelete' => 0])->select();
        return $data;
    }

}