<?php
/**
 * User: LiuTong
 * Date: 2017-09-07
 * Time: 11:05
 */

namespace app\admin\model;

use think\Db;
use think\Exception;
use think\Model;

class ChildrenTip extends Model
{
    /**
     * 育儿师列表
     * Date: 2017-09-07
     * @param int $type
     * fix Date: 2017-09-12
     * fix Content: 性别筛选
     * @param int $sex
     * @return \think\Paginator
     */
    public function getChildrenTip($type = -1, $sex = -1)
    {
        $where['p.isDelete'] = 0;
        if ($type != -1) {
            $where['p.type'] = $type;
        }

        if ($sex != -1) {
            $where['p.sex'] = $sex;
        }

        $where['pti.isDelete'] = 0;
        $data = db('parent p')
            ->field('p.id,p.type,p.sex,p.title,p.createTime,pti.typeName')
            ->join('parent_type_img pti', 'pti.type=p.type', 'LEFT')
            ->where($where)
            ->paginate(10, false, ['query' => ['type' => $type, 'sex' => $sex]]);
        return $data;
    }

    /**
     * 新增育儿师
     * Date: 2017-09-07
     * @param $data
     * @return int|string
     */
    public function saveChildrenTip($data)
    {
        $data['createTime'] = time();
        $data['isDelete'] = 0;
        $ret = db('parent')->insert($data);
        return $ret;
    }

    /**
     * 查询育儿师内容
     * Date: 2017-09-07
     * @param $id
     * @return mixed
     */
    public function getChildrenTipContent($id)
    {
        $data = db('parent')->where(['id' => $id])->value('content');
        return $data;
    }

    /**
     * 育儿师详细信息
     * Date: 2017-09-07
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getChildrenTipAllContent($id)
    {
        $data = db('parent')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * 更新育儿师
     * Date: 2017-09-07
     * @param $id
     * @param $data
     * @return int|string
     */
    public function updateChildrenTip($id, $data)
    {
        $data = db('parent')->where(['id' => $id])->update($data);
        return $data;
    }

    /**
     * 删除育儿师
     * Date: 2017-09-07
     * @param $id
     * @return int|string
     */
    public function deleteChildrenTip($id)
    {
        $ret = db('parent')->where(['id' => $id])->update(['isDelete' => 1]);
        return $ret;
    }

    /**
     * Date: 2017-10-11
     * 育儿师类型图片列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getChildrenTipTypeImage()
    {
        $where['isDelete'] = 0;
        $data = db('parent_type_img')->where($where)->select();
        return $data;
    }

    /**
     * Date: 2017-10-11
     * 获取已存在的类型 已存在的只能修改或删除后新增
     * @return mixed
     */
    public function getCTI()
    {
        $where['isDelete'] = 0;
        $data = db('parent_type_img')->field('id,type,typeName')->where($where)->select();
        return $data;
    }

    /**
     * Date: 2017-10-11
     * 获取详细
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getCTTI($id)
    {
        $where['id'] = $id;
        $data = db('parent_type_img')->where($where)->find();
        return $data;
    }

    /**
     * Date: 2017-10-11
     * 新增保存
     * @param $data
     * @return bool
     */
    public function saveCTTI($data)
    {
        $maxType = db('parent_type_img')->max('type');
        $data['createTime'] = time();
        $data['isDelete'] = 0;
        $data['type'] = $maxType + 1;
        $ret = db('parent_type_img')->insert($data);
        return $ret ? true : false;
    }

    /**
     * Date: 2017-10-11
     * 修改保存
     * @param $id
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function updateCTTI($id, $data, $adminId)
    {
        $preData = db('parent_type_img')->field('type,img')->where(['id' => $id])->find();
        if (!isset($data['img'])) {
            $data['img'] = $preData['img'];
        }
        Db::startTrans();
        try {
            $data['createTime'] = time();
            $data['isDelete'] = 0;
            $data['type'] = $preData['type'];
            db('parent_type_img')->insert($data);
            db('parent_type_img')->where(['id' => $id])->update(['isDelete' => 1]);
            operateLog('修改育儿师类别图片', 'parent_type_img', $id, $adminId);
        } catch (Exception $e) {
            dump($e->getMessage());
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-10-11
     * 删除
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function deleteTypeImage($id, $adminId)
    {
        $where['id'] = $id;
        $ret = db('parent_type_img')->where($where)->update(['isDelete' => 1]);
        if ($ret) {
            operateLog('删除育儿师类别图片', 'parent_type_img', $id, $adminId);
        }
        return $ret ? true : false;
    }
}