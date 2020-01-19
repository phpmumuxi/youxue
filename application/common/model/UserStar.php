<?php

namespace app\common\model;

use think\Db;
use think\Model;

//星星变动
class UserStar extends Model
{
    /**
     * [changeStar 星星变动记录]
     * @param  [type] $uid   [用户id]
     * @param  [type] $type    [类型1优选课程收入星星2消费星星3已过期的星星]
     * @param  [type] $orderId [记录表id]
     * @param  [type] $starNum [变动星星数]
     */
    public function changeStar($uid,$starNum,$orderId,$type)
    {
        $re = $this->userStarInfo($uid);
        switch ($type) {
            case 1:
                $change = $re['starNum']+$starNum;
                break;
            case 2:
            case 3:
                $change = $re['starNum']-$starNum;
                break;
            default:
                return false;
                break;
        }

        if($change<0){
            return false;
        }

        Db::startTrans();
        try{
            db('star_user')->update([
                    'id'=>$re['id'],
                    'starNum'=>$change
                ]);
            db('star_change')->insert([
                    'userId'=>$uid,
                    'nowStar'=>$re['starNum'],
                    'changeStar'=>$change,
                    'starNum'=>$starNum,
                    'type'=>$type,
                    'orderId'=>$orderId,
                    'createTime'=>time()
                ]);
            Db::commit();
        } catch (\Exception $e) {
            // echo $e->getMessage();exit;
                // 回滚事务
                Db::rollback();
                return false;
        }
        return true;

    }

    private function userStarInfo($uid)
    {
        $data = db('star_user')->where('userId',$uid)->find();
        return $data;
    }
}