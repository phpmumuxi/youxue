<?php
/**
 * User: LiuTong
 * Date: 2017-08-17
 * Time: 10:05
 */

namespace app\admin\model;

use think\Db;
use think\Exception;
use think\Model;

class GroupBuy extends Model
{
    /**
     * Date: 2017-08-17
     * fix Date: 2017-09-04
     * fix Content: 增加搜索
     * @param $orderNo
     * @return \think\Paginator
     */
    public function getGroupBuyOrderList($orderNo = '')
    {
//        $where['ob.status'] = ['exp', '<>0'];
        $where = [];
        if ($orderNo) {
            $where['ob.orderNo'] = $orderNo;
        }
        $data = db('order_buy')
            ->alias('ob')
            ->field('ob.id,ob.orderNo,ob.money,ob.createTime,ob.name,ob.status,u.name as uName,ob.isDispose,ob.termDay,ob.type')
            ->join('user u', 'u.id=ob.userId', 'LEFT')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-10-16
     * 团购列表
     * @param $name
     * @return mixed
     */
    public function getGroupBuyList($name = null)
    {
        if ($name) {
            $where['name'] = $name;
        }
        $where['status'] = ['<', 3];
        $data = db('buy')
            ->field('id,name,createTime,bigImg,smallImg,money,term,explains,adminId,upTime,status,isCmbc,title,introduct,brandNum,type')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-10-16
     * 团购上架
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function upGroupBuy($id, $adminId)
    {
        $ret = db('buy')->where(['id' => $id])->update(['status' => 1, 'upTime' => time(), 'adminId' => $adminId]);
        if ($ret) {
            operateLog('团购上架', 'buy', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-10-16
     * 团购下架
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function downGroupBuy($id, $adminId)
    {
        $ret = db('buy')->where(['id' => $id])->update(['status' => 2, 'adminId' => $adminId]);
        if ($ret) {
            operateLog('团购下架', 'buy', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-10-16
     * 团购删除
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function deleteGroupBuy($id, $adminId)
    {
        $ret = db('buy')->where(['id' => $id])->update(['status' => 3]);
        if ($ret) {
            operateLog('团购删除', 'buy', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-10-16
     * 获取团购信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getGroupBuyOne($id)
    {
        $data = db('buy')->where(['id' => $id])->find();
        return $data;
    }

    /**
     * Date: 2017-10-16
     * 品牌列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBrands()
    {
        $where['isDelete'] = 0;
        $data = db('brand')->field('id,name')->where($where)->select();
        return $data;
    }

    /**
     * Date: 2017-10-17
     * 获取已选的品牌
     * @param $buyId
     * @return mixed
     */
    public function getSelectedBrands($buyId)
    {
        $where['isDelete'] = 0;
        $where['buyId'] = $buyId;
        $data = db('buy_brand')->where($where)->field('brandId')->select();
        return $data;
    }

    /**
     * Date: 2017-10-17
     * 更新团购
     * @param $data
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function updateGroupBuy($data, $id, $adminId)
    {
        $bpInsertData = [];
        foreach ($data['brand'] as $k => $v) {
            $bpInsertData[] = [
                'buyId' => $id,
                'brandId' => $v,
                'isDelete' => 0,
                'createTime' => time(),
            ];
        }
        unset($data['brand']);
        Db::startTrans();
        try {
            db('buy_brand')->where(['buyId' => $id])->update(['isDelete' => 1]);
            db('buy_brand')->insertAll($bpInsertData);
            operateLog('团购品牌批量更新-删除旧的 批量增加新的', 'buy_brand', '', $adminId);
            $where['id'] = $id;
            db('buy')->where($where)->update($data);
            operateLog('团购更新内容', 'buy', $id, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date：2017-10-17
     * 团购新增
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function saveGroupBuy($data, $adminId)
    {
        $data['createTime'] = time();
        $data['adminId'] = $adminId;
        $data['status'] = 2;
        $brands = $data['brand'];
        unset($data['brand']);
        Db::startTrans();
        try {
            $buyId = db('buy')->insertGetId($data);
            operateLog('团购新增', 'buy', $buyId, $adminId);
            $bpInsertData = [];
            foreach ($brands as $k => $v) {
                $bpInsertData[] = [
                    'buyId' => $buyId,
                    'brandId' => $v,
                    'isDelete' => 0,
                    'createTime' => time(),
                ];
            }
            db('buy_brand')->insertAll($bpInsertData);
            operateLog('团购品牌批量新增', 'buy_brand', '', $adminId);
        } catch (Exception $e) {
            die($e->getMessage());
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

}