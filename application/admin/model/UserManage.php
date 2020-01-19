<?php
/**
 * User: LiuTong
 * Date: 2017/11/14
 * Time: 13:55
 */

namespace app\admin\model;

use think\Db;
use think\Exception;
use think\Model;

class UserManage extends Model
{
    /**
     * Date: 2017-11-13
     * 补录列表
     * @return mixed
     */
    public function getUserAdd()
    {
        $where['ua.isDelete'] = 0;
        $data = db('user_add ua')
            ->field('ua.*,a.name as adminName,s.name as shopName,sc.name as schoolName')
            ->join('admin a', 'a.id=ua.adminId', 'LEFT')
            ->join('shop s','s.id=ua.shopId','LEFT')
            ->join('school sc','sc.id=ua.schoolId','LEFT')
            ->where($where)
            ->order('isDone ASC')
            ->paginate(10, false);
        return $data;
    }

    /**
     * Date: 2017-11-13
     * 删除补录信息
     * @param $id
     * @return bool
     */
    public function deleteUser($id)
    {
        try {
            db('user_add')->where(['id' => $id])->update(['isDelete' => 1]);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-11-13
     * 获取补录的用户信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     */
    public function userInfoGet($id)
    {
        $data = db('user_add')->where(['id' => $id, 'isDelete' => 0, 'isDone' => 0])->find();
        if ($data) {
            if (!$data['babyNickName']) {
                $data['babyNickName'] = '';
            }
            if (!$data['babyBirth']) {
                $data['babyBirth'] = '';
            }
        }
        return $data;
    }

    /**
     * Date: 2017-11-13
     * 保存补录用户信息
     * @param $data
     * @return bool
     */
    public function saveUser($data)
    {
        try {
            $data['createTime'] = time();
            $data['babyBirthTime'] = 0;
            if ($data['babyBirth']) {
                $data['babyBirthTime'] = strtotime($data['babyBirth']);
            }
            db('user_add')->insert($data);
        } catch (Exception $e) {
            dump($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-11-14
     * 保存到用户表和用户孩子表
     * @param $id
     * @return bool
     */
    public function toUserDone($id)
    {
        $data = db('user_add')->where(['id' => $id])->find();
        if (empty($data)) {
            return false;
        }
        Db::startTrans();
        try {
            $userInfo['phone'] = $data['phone'];
            $userInfo['password'] = 0;
            $userInfo['isAdviser'] = 0;
            $userInfo['isReferrer'] = 0;
            $userInfo['createTime'] = time();
            $userInfo['token'] = 0;
            $userInfo['face'] = '';
            $userInfo['sex'] = 0;
            $userInfo['name'] = $data['name'];
            $userInfo['address'] = '';
            $userInfo['payPassword'] = '';
            $userInfo['balance'] = 0;
            $userInfo['doudou'] = 0;
            $userInfo['isMumDate'] = 0;
            $userInfo['isFictitious'] = 0;
            $userInfo['district'] = 0;
            $userInfo['longitude'] = 0;
            $userInfo['latitude'] = 0;
            $userInfo['referrerId'] = 0;
            $userInfo['memberLevel'] = 0;
            $userInfo['memberEndTime'] = 0;
            $userInfo['agr_no'] = '';
            $userInfo['status'] = 0;
            $userInfo['isCmbc'] = 0;
            $userInfo['isGive'] = 0;
            $userInfo['isBenefit'] = 0;
            $userInfo['shareId'] = 0;
            $userId = db('user')->insertGetId($userInfo);

            if ($data['babyBirth'] && $data['babyNickName']) {
                $babyInfo['userId'] = $userId;
                $babyInfo['sex'] = $data['babySex'];
                $babyInfo['nickname'] = $data['babyNickName'];
                $babyInfo['birth'] = $data['babyBirth'];
                $babyInfo['birthTime'] = $data['babyBirthTime'];
                $babyInfo['isDelete'] = 0;
                $babyInfo['createTime'] = time();
                db('user_baby')->insert($babyInfo);
            }

            db('user_add')->where(['id' => $id])->update(['isDone' => 1]);
        } catch (Exception $e) {
            Db::rollback();
            dump($e->getMessage());
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * Date: 2017-11-14
     * 更新用户录入信息
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateUser($id, $data)
    {
        try {
            $data['createTime'] = time();
            $data['babyBirthTime'] = 0;
            if ($data['babyBirth']) {
                $data['babyBirthTime'] = strtotime($data['babyBirth']);
            }
            db('user_add')->where(['id' => $id])->update($data);
        } catch (Exception $e) {
            dump($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Date: 2017-11-14
     * 检查用户手机号是否已经存在
     * @param $phone
     * @return bool
     */
    public function checkUserPhone($phone)
    {
        $ret = db('user')->where(['phone' => $phone])->value('id');
        return $ret ? true : false;
    }

}