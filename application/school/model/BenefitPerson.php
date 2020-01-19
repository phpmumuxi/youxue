<?php
/**
 * User: LiuTong
 * Date: 2017-09-21
 * Time: 14:03
 */

namespace app\school\model;

use app\common\model\SchoolCommon;
use think\Db;
use think\Exception;
use think\Model;

class BenefitPerson extends Model
{
    /**
     * Date: 2017-09-21
     * 受益人id
     * @param $schoolId
     * @param $phone
     * @return \think\Paginator
     */
    public function getBenefitPersonList($schoolId, $phone = '')
    {
        $where['schoolId'] = $schoolId;
        $where['isDelete'] = 0;

        if ($phone) {
            $where['phone'] = $phone;
        }
        $data = db('user_benefit')->where($where)->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-09-21
     * 新增受益人
     * @param $data
     * @param $adminId
     * @return bool
     */
    public function benefitPersonAdd($data, $adminId)
    {
        $schoolCommonModel = new SchoolCommon();
        $adminInfo = $schoolCommonModel->getSchoolAdminInfo($adminId);
        $data['shopId'] = $adminInfo['shopId'];
        $data['schoolId'] = $adminInfo['schoolId'];
        $data['createTime'] = time();
        $data['isDelete'] = 0;

        Db::startTrans();
        try {
            db('user_benefit')->insert($data);
            db('user')->where(['id' => $data['userId']])->update(['isBenefit' => 1]);
        } catch (Exception $e) {
            //  $error = $e->getMessage();
            //  dump($error);
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-09-21
     * 检测受益人是否已存在
     * @param $phone
     * @param $schoolId
     * @return mixed
     */
    public function checkIfHadPerson($phone, $schoolId)
    {
        $ret = db('user_benefit')->where(['phone' => $phone, 'schoolId' => $schoolId, 'isDelete' => 0])->value('id');
        return $ret;
    }

    /**
     * Date: 2017-09-21
     * 检测当前手机号是否已注册成为会员
     * @param $phone
     * @return mixed
     */
    public function searchBenefitPerson($phone)
    {
        $data = db('user')->where(['phone' => $phone])->value('id');
        return $data;
    }

    /**
     * Date: 2017-09-21
     * 判断新增加的用户是否是同一人
     * @param $userId
     * @param $id
     * @return bool
     */
    public function checkIfSamePerson($userId, $id)
    {
        $uId = db('user_benefit')->where(['id' => $id])->value('userId');
        if ($userId == $uId) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date: 2017-09-21
     * 修改受益人信息
     * @param $phone
     * @param $name
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function updateBenefitPerson($phone, $name, $id, $adminId)
    {
        $data['phone'] = $phone;
        $data['name'] = $name;
        try {
            db('user_benefit')->where(['id' => $id])->update($data);
            operateLog('更新受益人信息', 'user_benefit', $id, $adminId);
        } catch (Exception $e) {
            //  $error = $e->getMessage();
            //  dump($error);
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-09-21
     * 删除受益人
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function deleteBenefitPerson($id, $adminId)
    {
        $userId = db('user_benefit')->where(['id' => $id])->value('userId');
        Db::startTrans();
        try {
            db('user_benefit')->where(['id' => $id])->update(['isDelete' => 1]);
            db('user')->where(['id' => $userId])->update(['isBenefit' => 0]);
            operateLog('删除受益人', 'user_benefit', $id, $adminId);
        } catch (Exception $e) {
            //  $error = $e->getMessage();
            //  dump($error);
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

}