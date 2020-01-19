<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-23
 */

namespace app\api\model;

use think\Db;
use think\Model;

class AddShare extends Model
{
    public function addShareDate($arr,$userId)
    {
        $share = db('star_user')->where('userId',$userId)->find();
        $free = [];
        if(!$arr['memberLevel']){
            $free = [
                'userId'=>$arr['id'],
                'shareId'=>$userId,
                'num'=>2,
                'remainNum'=>2,
                'endTime'=>strtotime(date('Y-m-d 23:59:59',time()))+2592000,
                'createTime'=>time()
            ];
        }
        if(!$share){
            $data = db('star_rule')->where('isDelete=0')->select();

            $array = [];
            foreach ($data as $k => $v) {
                $array[]=[
                    'userId'=>$userId,
                    'starId'=>$v['id'],
                    'img'=>$v['img'],
                    'name'=>$v['name'],
                    'level'=>$v['level'],
                    'days'=>$v['days'],
                    'endTime'=>strtotime(date('Y-m-d 23:59:59',time()))+($v['days']*86400),
                    'starNum'=>$v['starNum'],
                    'status'=>0,
                    'classNum'=>$v['classNum'],
                    'createTime'=>time(),
                    'starRound'=>1,
                    'isEnd'=>0
                ];
            }
            $array2 = [
                'userId'=>$userId,
                'starNum'=>0,
                'createTime'=>time(),
                'starRound'=>1,
                'status'=>0,
                'starStatus'=>0,
                'endTime'=>strtotime(date('Y-m-d 23:59:59',time()))+(($this->satrTime())+30)*86400,
            ];
            // print_r($array);exit;
            Db::startTrans();
            try{
                db('star_rule_user')->insertAll($array);
                db('star_user')->insert([
                            'userId'=>$userId,
                            'shareTime'=>time(),
                            'starNum'=>0,
                            'createTime'=>time()
                        ]);
                db('star_user_record')->insert($array2);
                db('user')->where('id',$arr['id'])->update(['shareId'=>$userId]);
                if($free){
                    db('star_free')->insert($free);
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return false;
            }
        }else{
            Db::startTrans();
            try{
                db('user')->where('id',$arr['id'])->update(['shareId'=>$userId]);
                if($free){
                    db('star_free')->insert($free);
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return false;
            }
        }
        return true;
    }

    private function satrTime()
    {
        $day = db('star_rule')->where('isDelete=0')->order('level desc')->value('days');
        return $day;
    }
}