<?php
/**
 * User: LiuTong
 * Date: 2017-10-11
 * Time: 15:29
 */

namespace app\admin\model;

use think\Model;

class OrderPos extends Model
{
    /**
     * Date: 2017-10-11
     * pos机订单
     * @param int $posOrderId
     * @return \think\Paginator
     */
    public function getOrderPosList($posOrderId = 0)
    {
        $where = [];
        if ($posOrderId) {
            $where['po.posOrderId'] = $posOrderId;
        }
        $data = db('pos_order po')
            ->field('po.id,po.posOrderId,po.createTime,po.isDelete,a.name as adminName')
            ->join('admin a', 'a.id=po.adminId', 'LEFT')
            ->where($where)
            ->order('po.createTime DESC')
            ->paginate(10);
        return $data;
    }

    /**
     * Date: 2017-10-11
     * 新增作废订单号
     * @param $data
     * @return bool
     */
    public function saveOrderPos($data)
    {
        $data['createTime'] = time();
        $data['isDelete'] = 0;
        $ret = db('pos_order')->insertGetId($data);
        if ($ret) {
            operateLog('新增作废pos订单号', 'pos_order', $ret, $data['adminId']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-10-11
     * 启用pos订单
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function startOrderPos($id, $adminId)
    {
        $ret = db('pos_order')->where(['id' => $id])->update(['isDelete' => 0]);
        if ($ret) {
            operateLog('启用pos订单', 'pos_order', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-10-11
     * 作废pos订单
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function endOrderPos($id, $adminId)
    {
        $ret = db('pos_order')->where(['id' => $id])->update(['isDelete' => 1]);
        if ($ret) {
            operateLog('作废pos订单', 'pos_order', $id, $adminId);
            return true;
        } else {
            return false;
        }
    }
}