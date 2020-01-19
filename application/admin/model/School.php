<?php
/**
 * User: LiuTong
 * Date: 2017-11-02
 * Time: 13:08
 */

namespace app\admin\model;

use think\Db;
use think\Exception;
use think\Model;

class School extends Model
{
    /**
     * Date: 2017-11-02
     * 校区列表
     * @param int $shopId
     * @param string $schoolName
     * @return mixed
     */
    public function getSchoolList($shopId = -1, $schoolName = '')
    {
        if ($shopId != -1) {
            $where['sc.shopId'] = $shopId;
        }

        if ($schoolName) {
            $where['sc.name'] = ['like', '%' . $schoolName . '%'];
        }
        $where['sc.status'] = ['<', 3];
        $data = db('school sc')
            ->field('sc.id,sc.name as schoolName,s.name as shopName,sc.userName,sc.phone,sc.status')
            ->join('shop s', 's.id=sc.shopId', 'LEFT')
            ->where($where)
            ->order('sc.status asc')
            ->paginate(10, false, ['query' => ['shopId' => $shopId]]);
        return $data;
    }

    public function shopLists()
    {
        $data = db('shop')->field('id,name')->where(['status' => 1])->select();
        return $data;
    }

    /**
     * Date: 2017-11-02
     * 统计校区是否存在已审核过的课程
     * @param $schoolId
     * @return int|string
     */
    public function ifSchoolHaveCourse($schoolId)
    {
        $ret = db('class_school')->where(['status' => 2, 'schoolId' => $schoolId])->count('id');
        return $ret;
    }

    /**
     * Date: 2017-11-02
     * 校区上架
     * @param $id
     * @param $adminId
     * @return bool
     */
    public function upSchool($id, $adminId)
    {
        $schoolInfo = db('school')->where(['id' => $id])->find();
        if (empty($schoolInfo)) {
            return false;
        }
        try {
            db('school')->where(['id' => $id])->update(['status' => 1]);
            operateLog('校区上架', 'school', $id, $adminId);
            db('shop')->where(['id' => $schoolInfo['shopId']])->setInc('schoolNum');
            db('brand')->where(['id' => $schoolInfo['brandId']])->setInc('schoolNum');
        } catch (Exception $e) {
//            echo $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-11-02
     * 校区下架
     * @param $schoolId
     * @param $adminId
     * @return bool
     * @internal param $id
     */
    public function downSchool($schoolId, $adminId)
    {
        // 下架校区 下架校区的课程 删除课程审核规则 减商户课程数 品牌课程数
        $schoolInfo = db('school')->where(['id' => $schoolId])->find();
        if (empty($schoolInfo)) {
            return false;
        }

        $shopId = $schoolInfo['shopId'];
        $brandId = $schoolInfo['brandId'];

        // 查询当前品牌是否属于优选品牌
        $goodBrandInfo = db('brand_home')->field('id,likeNum')->where(['brandId' => $brandId])->find();

        // 商户校区数
        $shopInfo = db('shop')->field('schoolNum,classNum')->where(['id' => $shopId])->find();
        // 品牌课程数
        $brandInfo = db('brand')->field('schoolNum,classNum')->where(['id' => $brandId])->find();

        // 课程数 上架状态
        $classSchoolIdsArray = Db::name('class_school')->field('id')->where(['schoolId' => $schoolId, 'status' => 2])->select();
        $classNum = count($classSchoolIdsArray);

        $classSchoolIds = '';
        if ($classNum > 0) {
            $classSchoolIdsTmp = [];
            foreach ($classSchoolIdsArray as $k => $v) {
                array_push($classSchoolIdsTmp, $v['id']);
            }
            $classSchoolIds = implode(',', $classSchoolIdsTmp);
        }

        try {
            // 课程下架
            Db::name('class_school')->where(['schoolId' => $schoolId])->update(['status' => 3]);
            operateLog('校区课程下架', 'class_school schoolId = ' . $schoolId, 0, $adminId);
            // 返现规则 删除
            if ($classSchoolIds) {
                $where['classSchoolId'] = ['in', $classSchoolIds];
                Db::name('class_rule')->where($where)->update(['isDelete' => 1]);
                operateLog('校区课程审核规则删除 classSchoolIds = ' . $classSchoolIds, 'class_rule', 0, $adminId);
            }
            // 校区
            Db::name('school')->where(['id' => $schoolId])->update(['status' => 2]);
            operateLog('校区下架', 'school', $schoolId, $adminId);

            // 防止溢减
            $updateShop['schoolNum'] = 0;
            if ($shopInfo['schoolNum'] > 0) {
                $updateShop['schoolNum'] = ['exp', 'schoolNum-1'];
            }
            $updateShop['classNum'] = 0;
            if ($shopInfo['classNum'] > $classNum) {
                $updateShop['classNum'] = ['exp', 'classNum-' . $classNum];
            }

            $updateBrand['schoolNum'] = 0;
            if ($brandInfo['schoolNum'] > 0) {
                $updateBrand['schoolNum'] = ['exp', 'schoolNum-1'];
            }
            $updateBrand['classNum'] = 0;
            if ($brandInfo['classNum'] > $classNum) {
                $updateBrand['classNum'] = ['exp', 'classNum-' . $classNum];
            }

            // 当品牌课程数为0时 删除该品牌所属的优选品牌
            if ($updateBrand['classNum'] == 0 && !empty($goodBrandInfo)) {
                db('brand_home')->where(['id' => $goodBrandInfo['id']])->delete();
                operateLog('品牌课程为0时,(实际)删除该优选品牌 likeNum = ' . $goodBrandInfo['likeNum'], 'brand_home', 0, $adminId);
            }

            // 商户
            Db::name('shop')->where(['id' => $shopId])->update($updateShop);
            operateLog('商户减 校区以及校区课程数 courseNum = ' . $classNum, 'shop', 0, $adminId);
            // 品牌
            Db::name('brand')->where(['id' => $brandId])->update($updateBrand);
            operateLog('品牌减 校区以及校区课程数 courseNum = ' . $classNum, 'brand', 0, $adminId);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

}