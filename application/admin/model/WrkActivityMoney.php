<?php
/**
 * User: LiuTong
 * Date: 2017/11/15
 * Time: 11:57
 */

namespace app\admin\model;

use app\common\model\SchoolCommon;
use think\Db;
use think\Exception;
use think\Model;

class WrkActivityMoney extends Model
{

    /**
     * Date: 2017-11-14
     * 万人砍活动订单列表
     * @return mixed
     */
    public function getWrkActivityOrderList()
    {
        $where['awo.status'] = 1;
        $where['awo.isDelete'] = 0;
        $data = db('activity_wrk_order awo')
            ->field('awo.id,awo.name as wrkName,awo.status,awo.payDate,awo.adviserId,an.name as adviserName,u.name as uName,u.phone,awo.orderNo')
            ->join('adviser_name an', 'an.id=awo.adviserId', 'LEFT')
            ->join('user u', 'u.id=awo.userId', 'LEFT')
            ->where($where)
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-11-14
     * 查询万人砍是否已经分配了业绩顾问
     * @param $wrkOrderId
     * @return bool
     */
    public function getWrkOrderAdviserId($wrkOrderId)
    {
        $ret = db('activity_wrk_order')->where(['id' => $wrkOrderId])->value('adviserId');
        return $ret ? true : false;
    }

    /**
     * Date: 2017-11-14
     * 确认子单是否已经分配了顾问
     * @param $wrkOrderId
     * @return bool
     */
    public function getWrkOrderInfoAdviserId($wrkOrderId)
    {
        $data = db('activity_wrk_info')->where(['wrkOrderId' => $wrkOrderId])->find();
        if ($data['adviserId'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * Date: 2017-11-14
     * 分配业绩顾问
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function adviserAssign($id, $adminId)
    {
        $ret = $this->getWrkOrderAdviserId($id);
        if ($ret) {
            return false;
        }
        $schoolCommon = new SchoolCommon();
        $schoolId = $schoolCommon->getSchoolIdFromAdminId($adminId);
        $info = db('activity_wrk_info')->where(['wrkOrderId' => $id, 'schoolId' => $schoolId])->find();
        if (empty($info)) {
            return false;
        }
        $adviserId = $info['adviserId'];
        Db::startTrans();
        try {
            db('activity_wrk_order')->where(['id' => $id])->update(['adviserId' => $adviserId]);
            operateLog('分配业绩顾问', 'activity_wrk_order', $id, $adminId);
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-15
     * 查询万人砍子单信息
     * @param $wrkOrderId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function wrkOrderInfoGet($wrkOrderId)
    {
        $data = db('activity_wrk_info awi')
            ->field('awi.*,s.name as shopName,sc.name as schoolName,an.name as adviserName')
            ->join('shop s', 's.id=awi.shopId', 'LEFT')
            ->join('school sc', 'sc.id=awi.schoolId', 'LEFT')
            ->join('adviser_name an', 'an.id=awi.adviserId', 'LEFT')
            ->where(['awi.wrkOrderId' => $wrkOrderId])
            ->select();

        if ($data) {
            foreach ($data as $k => $v) {
                if ($v['isSign'] == 1) {
                    $data[$k]['signTime'] = date('Y-m-d H:i', $v['signTime']);
                } else {
                    $data[$k]['signTime'] = '未签约';
                }
                if (!$v['adviserId']) {
                    $data[$k]['adviserName'] = '无';
                }
            }
        }
        return $data;
    }

    /**
     * Date: 2017-11-15
     * 重新分配业绩顾问
     * @param $wrkOrderId
     * @param $wrkInfoId
     * @param $adminId
     * @return bool
     */
    public function adviserReAssign($wrkOrderId, $wrkInfoId, $adminId)
    {
        $wrkInfo = db('activity_wrk_info')->where(['id' => $wrkInfoId])->find();
        $wrkOrder = db('activity_wrk_order')->where(['id' => $wrkOrderId])->find();
        if ($wrkInfo['wrkOrderId'] != $wrkOrder['id']) {
            return false;
        }
        Db::startTrans();
        try {
            db('activity_wrk_order')->where(['id' => $wrkOrderId])->update(['adviserId' => $wrkInfo['adviserId']]);
            operateLog('重新分配业绩顾问', 'activity_wrk_order', $wrkOrderId, $adminId);
        } catch (Exception $e) {
            dump($e->getMessage());
            die;
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

}