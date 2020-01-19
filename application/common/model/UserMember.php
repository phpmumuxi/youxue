<?php
/**
 * User: Xin
 * Date: 2017-09-21
 */

namespace app\common\model;

use think\Model;
use think\Db;

//用户会员
class UserMember extends Model
{
    public function userMemberInfo($uid)
    {
        $data = db('user')->field('memberLevel,memberEndTime')->where('id',$uid)->find();
        $arr = [];
        if($data['memberEndTime']<time()){
            $re = $this->useMember($uid);
            if($re){
                $arr['memberLevel']=$re['level'];
                $arr['memberEndTime']=$data['memberEndTime']+$re['month']*2635200;
                Db::startTrans();
                try{
                    db('user')->where('id',$uid)->update($arr);
                    db('user_member_use')->where('id',$re['id'])->update(['$isUse'=>1]);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return false;
                }
            }
        }
        return $arr;
    }

    private function useMember($uid)
    {
        $data = db('user_member_use')->field('id,level,month')->where('isUse=0 and userId='.$uid)->order('level desc')->find();
        return $data;
    }
}