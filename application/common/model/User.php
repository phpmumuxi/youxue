<?php
/**
 * Created by PhpStorm.
 * User: Liu
 * Date: 2017-07-05
 * Time: 13:47
 */

namespace app\common\model;

use think\Db;
use think\Model;

class User extends Model
{

    //  从token获取uid
    public function getIdFromToken($token)
    {
        $uid = Db::name('user')->field('id')->where(['token' => $token])->find();
        return $uid;
    }

    //获取用户vip等级
    public function userMember($uid)
    {
        $data = Db::name('user')
            ->field('isAdviser,isReferrer,memberLevel,memberEndTime,balance,referrerId,isGive,isFictitious,isCmbc')
            ->where('id', $uid)
            ->find();
        return $data;
    }

    //获取支付密码
    public function userPayPassword($id)
    {
        $pwd = db('user')->where('id', $id)->value('payPassword');
        return $pwd;
    }

    //验证验证码
    public function verifyCodeData($phone, $code)
    {
        $data = db('phone_code')->where(['phone' => $phone, 'code' => $code, 'status' => 0])->find();
        if (!$data) return false;
        if (($data['createTime'] + 1800) < time()) {
            db('phone_code')->update([
                'id' => $data['id'],
                'status' => 2
            ]);
            return false;
        }
        $a = db('phone_code')->update([
            'id' => $data['id'],
            'status' => 1
        ]);
        return $a ? true : false;
    }

    /**
     * Date: 2017-10-19
     * 获取用户手机
     * @param $id
     * @return mixed
     */
    public function getPhoneFromId($id)
    {
        $phone = db('user')->where(['id' => $id])->value('phone');
        return $phone;
    }
}