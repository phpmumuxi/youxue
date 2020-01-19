<?php
/**
 * User: LiuTong
 * Date: 2017-09-01
 * Time: 9:34
 */

namespace app\common\model;

use think\Model;

class SchoolCommon extends Model
{
    /**
     * 获取登陆人校区id
     * Date: 2017-09-01
     * @param int $adminId
     * @return bool|mixed
     */
    public function getSchoolIdFromAdminId($adminId = 0)
    {
        if (!$adminId) {
            return false;
        }

        $schoolId = db('admin')->where(['id' => $adminId])->value('schoolId');
        return $schoolId;
    }

    /**
     * Date: 2017-09-21
     * 获取管理员的商户id和校区id
     * @param $adminId
     * @return array
     */
    public function getSchoolAdminInfo($adminId)
    {
        $data = db('admin')->field('shopId,schoolId')->where(['id' => $adminId])->find();
        return ['shopId' => $data['shopId'], 'schoolId' => $data['schoolId']];
    }

}