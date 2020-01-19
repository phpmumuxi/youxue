<?php
/**
 * User: LiuTong
 * Date: 2017/11/14
 * Time: 14:22
 */

namespace app\school\model;

use app\common\model\SchoolCommon;
use think\Db;
use think\Exception;
use think\Model;

class WrkActivityMoney extends Model
{

    /**
     * Date: 2017-11-14
     * 万人砍活动订单列表
     * @param $schoolId
     * @return mixed
     */
    public function getWrkActivityOrderList($schoolId)
    {
        $where['awo.status'] = 1;
        $where['awo.isDelete'] = 0;
        $where['awi.schoolId'] = $schoolId;
        $data = db('activity_wrk_order awo')
            ->field('awo.id,awo.name as wrkName,awo.status,awo.payDate,awo.adviserId,an.name as adviserName,u.name as uName,u.phone,awo.orderNo')
            ->join('activity_wrk_info awi', 'awi.wrkOrderId=awo.id', 'LEFT')
            ->join('adviser_name an', 'an.id=awo.adviserId', 'LEFT')
            ->join('user u', 'u.id=awo.userId', 'LEFT')
            ->where($where)
            ->group('awo.id')
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

}