<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-08-30
 */

namespace app\api\model;

use think\Db;
use think\Model;

class MomDateActivity extends Model
{

    //活动列表
    public function momDateListData($arr)
    {
        $age = $arr['minAge']&&$arr['maxAge']? ' (m.maxAge >='.$arr['maxAge'].' and m.minAge <='.$arr['minAge'].') or (m.maxAge <='.$arr['maxAge'].' and m.minAge >='.$arr['minAge'].')':'';
        $data = $arr['minTime']&&$arr['maxTime']?['dateTime'=>['between',[$arr['minTime'],$arr['maxTime']]]]:"";
        $status = $arr['status']?['m.status' =>($arr['status']-1)]:['m.status' =>['in',[0,1,2]]];
        $data = db('mom_date m')
                ->field('m.id as momId,m.name,winNum,dateTime,m.address,peopleNum,minAge,maxAge,m.status,u.name as userName,face,memberLevel,memberEndTime,userId,isFictitious')
                ->join('user u','u.id=userId','left')
                ->where($status)
                ->where('m.isDelete',0)
                ->where($age)
                ->where($data)
                ->page($arr['page'],$arr['pagesize'])
                ->order('dateTime desc')
                ->select();
        if(!$data)return $data;
        if(isset($arr['userId'])&&$arr['userId']){
            $res = $this->myMom($arr['userId']);
            foreach ($data as $k => $v) {
                $data[$k]['myStatus']=5;
                if($v['userId']==$arr['userId']){
                    $data[$k]['momStatus']=1;
                }else{
                    $data[$k]['momStatus'] = 0;
                    if($res){
                        foreach ($res as $j => $val) {
                            if($val['momId']==$v['momId']){
                                $data[$k]['myStatus']=$val['status'];
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    private function myMom($uid)
    {
        $data = db('mom_date_enrol')->field('momId,status')->where(['status'=>['in',[0,1,3]],'userId'=>$uid])->select();
        return $data;
    }

    //发布活动
    public function organizedMomDateData($arr)
    {
        $data = $this->myInfo($arr['userId']);
        $arr['createTime'] = time();
        $arr['status'] = 0;
        $arr['isDelete'] = 0;
        $arr['isSponsor'] = 1;
        $arr['sponsorType'] = 4;
        $arr['phone'] = $data['phone'];
        $arr['winNum'] = 0;
        $a = db('mom_date')->insert($arr);
        if(!$a)return false;
        return [
            'userName' => $data['name'],
            'face' => $data['face'],
            'isCmbc' => $data['isCmbc'],
            'level' => $data['level'],
            'momName' => $arr['name'],
            'dateTime' => $arr['dateTime'],
            'address' => $arr['address'],
            'bobyAge' => $arr['maxAge']?$arr['minAge'].'~'.$arr['maxAge']:0,
            'peopleNum' => $arr['peopleNum'],
            'contact' => $arr['contact']
        ];

    }

    //查询个人信息
    private function myInfo($uid)
    {
        $data = db('user')
            ->field('name,face,isCmbc,memberLevel,memberEndTime,phone,isFictitious')
            ->where('id',$uid)
            ->find();
        if($data['memberEndTime']<time()){
             $data['level'] = 0;
            $a = new \app\common\model\UserMember();
            $r = $a->userMemberInfo($uid);
            if($r)$data['level']=$r['memberLevel'];
        }else{
            $data['level']=$data['memberLevel'];
        }
        return $data;
    }

    //活动详情
    public function momDateInfoData($arr)
    {
        $data = db('mom_date')
            ->field('id as momId,userId,name,dateTime,address,maxAge,minAge,peopleNum,contact,introduct,status,img,winNum')
            ->where(['id'=>$arr['momId']])
            ->find();

        if($data['status']===0){
            if($data['dateTime']<=(time()-300)){
                db('mom_date')->update(['id'=>$data['momId'],'status'=>1]);
                $data['status'] = 1;
            }
        }
        if($data['status']==1){
            if(($data['dateTime']+21600)<=time()){
                db('mom_date')->update(['id'=>$data['momId'],'status'=>2]);
                $data['status'] = 2;
            }
        }
        $res = $this->myInfo($data['userId']);
        $data['userName'] = $res['name'];
        $data['face'] = $res['face'];
        $data['isCmbc'] = $res['isCmbc'];
        $data['level'] = $res['level'];
        $data['nickname'] = '';
        $data['birth'] = '';
        $data['sex'] = '';
        $data['isFictitious'] = $res['isFictitious'];
        $data['myStatus'] = 0;
        $data['momStatus'] = 0;
        $data['isShare']=0;
        $data['share']=[];
        $re = $this->userBaby($data['userId']);
        if($re){
            $data['nickname'] = $re['nickname'];
            $data['birth'] = $re['birth'];
            $data['sex'] = $re['sex'];
        }

        $data['momUser'] = [];
        if(!$arr['userId']){
            $data['contact'] = '';
        }else if($arr['userId']){
            if($arr['userId']!=$data['userId']){
                $res = $this->userMom(['momId'=>$data['momId'],'userId'=>$arr['userId']]);
                if(!$res){
                    $data['contact'] = '';
                }else{
                    $data['myStatus'] = $res['status']+1;
                    $data['momUser'][] = $res;
                }
            }else{
                $data['momStatus'] = 1;
                if($data['status']!=2){
                    $data['momUser'] = $this->momEnrol($data['momId']);
                }
            }
            if($data['status']==2){
                $data['share']=$this->shareMomInfo($data['momId']);
                $data['isShare']=$this->isShare(['momId'=>$data['momId'],'userId'=>$arr['userId']])?1:0;
            }
        }
        return $data;
    }

    //是否分享
    private function isShare($arr)
    {
        $arr['isDelete']=0;
        $data = db('mom_date_text')->where($arr)->value('id');
        return $data;
    }

    //获得分享信息
    private function shareMomInfo($id)
    {
        $data = db('mom_date_text m')
                ->field('m.id as shareId,text,m.createTime,u.name,memberLevel,memberEndTime,isCmbc,userId,u.face')
                ->join('user u','u.id=m.userId','left')
                ->where('m.momId='.$id.' and m.isDelete=0')
                ->select();
        if(!$data)return $data;
        $img = db('mom_date_img')->field('id as imgId,userId,img,smallImg')->where('momId='.$id.' and isDelete=0')->select();
        if(!$img)return $data;
        foreach ($data as $k => $v) {
            foreach ($img as $j => $val) {
                if($val['userId']==$v['userId'])
                    $data[$k]['img'][]=$img[$j];
            }
        }
        return $data;

    }

    //发起者获取用户报名信息
    public function sponsorMonDateData($arr)
    {
        $data = $this->momInfo($arr['momId']);
        if($arr['userId']!=$data['userId'])return false;
        $re = $this->momEnrol($arr['momId']);
        return $re;
    }

    //宝宝信息
    private function userBaby($uid)
    {
        $res = db('user_baby')->field('nickname,birth,sex')->where('userId',$uid)->find();
        return $res;
    }
    //妈妈约报名信息
    private function userMom($arr)
    {
        $res = db('mom_date_enrol')->where([
                'momId' => $arr['momId'],
                'userId' => $arr['userId'],
                'isDelete' => 0,
                'status'=>['in',[0,1,3]]
            ])->find();
        return $res;
    }
    //用户报名信息
    private function momEnrol($momId)
    {
        $res = db('mom_date_enrol m')
             ->field('m.id as enrolId ,m.status,m.remark,babyNum,parentNum,m.phone,u.name,face,isCmbc,memberLevel,memberEndTime')
             ->join('user u','u.id=m.userId','left')
             ->where(['m.status'=>['in',[0,1]],'m.momId'=>$momId])
             ->select();
        return $res;

    }

    //报名活动
    public function momDateApplyData($arr)
    {
        $a = $this->userMom($arr);
        if($a)return 'signDateExit';
        $a = $this->momInfo($arr['momId']);
        if($a['status'])return 'momDateStatus';
        if(($a['applyNum']+$arr['babyNum']+$arr['parentNum'])>$a['peopleNum'])return 'dataMax';
        $arr['phone'] = 0;
        if($arr['isPhone']){
            $arr['phone'] = db('user')->where('id',$arr['userId'])->value('phone');
        }
        unset($arr['isPhone']);
        $arr['createTime'] = time();
        $arr['isDelete'] = 0;
        $arr['status'] = 0;
        $applyNum = $a['applyNum']+$arr['babyNum']+$arr['parentNum'];
        Db::startTrans();
        try{
            db('mom_date_enrol')->insert($arr);
            db('mom_date')->update([
                                'applyNum' => $applyNum,
                                'id' => $arr['momId']
                            ]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        $data = $this->successMom(['momId'=>$arr['momId']]);
        return $data;
    }

    private function momInfo($id)
    {
        $data = db('mom_date')->field('id,name,userId,dateTime,address,minAge,maxAge,peopleNum,contact,winNum,applyNum,status')->where('id',$id)->find();
        return $data;
    }

    //成功返回数据
    private function successMom($arr)
    {
        $data = [];
        $info = $this->momInfo($arr['momId']);
        $user = $this->myInfo($info['userId']);
            $data['hostInfo'] = [
                'hostName' => $user['name'],
                'hostLevel' =>$user['level'],
                'hostIsCmbc' =>$user['isCmbc']
            ];
            $data['momInfo'] = [
                'name' => $info['name'],
                'dateTime' => $info['dateTime'],
                'address' => $info['address'],
                'badyAge' => $info['maxAge']? $info['minAge']."~". $info['maxAge']:0,
                'peopleNum' => $info['peopleNum'],
                'contact' => $info['contact']
            ];
        return $data;
    }

    //取消报名
    public function momDateCancelApplyData($arr)
    {
        $data = $this->userMom($arr);
        if($data['status']!=0)return false;
        $a = $this->momInfo($arr['momId']);
        $applyNum = $a['applyNum']-$data['babyNum']-$data['parentNum'];
        Db::startTrans();
        try{
            db('mom_date_enrol')->where([
                'userId' => $arr['userId'],
                'momId' => $arr['momId']
            ])->update([
                'isDelete' => 1,
                'status' => 2
            ]);
            db('mom_date')->update([
                                'applyNum' => $applyNum,
                                'id' => $arr['momId']
                            ]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        $data = $this->successMom(['momId'=>$arr['momId']]);
        return $data;
    }

    //活动审核活动
    public function momDateAuditData($arr)
    {
        $a = $this->momInfo($arr['momId']);
        if(!$a)return false;
        if($a['userId']!=$arr['userId'])return false;
        $re = db('mom_date_enrol')->where('id='.$arr['enrolId'].' and momId='.$arr['momId'])->find();
        if(!$re)return false;
        if($re['status']!=0)return false;
         $winNum = $a['winNum']+$re['babyNum']+$re['parentNum'];
        $status = $arr['status']==1?1:3;
        Db::startTrans();
        try{
            db('mom_date_enrol')->update([
                'id'=>$arr['enrolId'],
                'status'=>$status
                ]);
            db('mom_date')->update([
                                'winNum' => $winNum,
                                'id' => $arr['momId']
                            ]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //取消活动
    public function cancelMomDateData($arr)
    {
        $data = $this->momInfo($arr['momId']);
        if(!$data)return false;
        if($arr['userId']!=$data['userId']||$data['winNum']||$data['status'])return false;
        $a = db('mom_date')->update([
                'id' =>$arr['momId'],
                'status' => 3
            ]);
        if(!$a)return false;
        $data = $this->successMom(['momId'=>$arr['momId']]);
        return $data;
    }

    //分享活动验证
    public function shareMomDateTest($arr)
    {
        $re = db('mom_date')->where([
                'id' => $arr['momId'],
                'status' => 2
            ])->find();
        if(!$re)return 'dateMiss';
        $res = db('mom_date_enrol')->where([
                'momId' => $arr['momId'],
                'userId' => $arr['userId'],
                'status' => 1
            ])->value('id');

        if(!$res&&$re['userId']!=$arr['userId'])return 'momDateNoJoin';
        $data = db('mom_date_text')->where([
                'userId' => $arr['userId'],
                'momId' => $arr['momId'],
                'isDelete' => 0
            ])->value('id');
        $a = 'momDateShare';
        if($arr['shareId']){
            if(!$data) return 'momDateShareNo';
            if($data!=$arr['shareId']){
                return 'momDateShareNo';
            }
            return false;
        }
        return $data?'momDateShare':false;
    }
    //分享
    public function shareMomDateData($arr,$array)
    {
        $arr['createTime'] = time();
        $arr['isDelete'] = 0;
        // print_r($arr);
        // print_r($array);
        // exit;
        Db::startTrans();
        try{
            db('mom_date_text')->insert($arr);
            if($array){
                db('mom_date_img')->insertAll($array);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }
    //修改分享
    public function shareMomDateUpdate($arr,$array)
    {
        Db::startTrans();
        try{
            db('mom_date_text')->update([
                'id'=>$arr['shareId'],
                'text' => $arr['text']
                ]);
            db('mom_date_img')->where([
                    'userId'=>$arr['userId'],
                    'momId' => $arr['momId'],
                    'isDelete'=>0
                ])->update(['isDelete'=>1]);
            if($array){
                db('mom_date_img')->insertAll($array);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //编辑分享
    public function compileMomDateData($arr)
    {
        $arr['isDelete']=0;
        $data = db('mom_date_text')->field('id as shareId,text,createTime')->where($arr)->find();
        if(!$data)return $data;
        $data['img'] = db('mom_date_img')->field('id as imgId,img,smallImg')->where($arr)->select();
        return $data;
    }

    //删除分享
    public function delShareMomData($arr)
    {
        $data = db('mom_date_text')->where(['id'=>$arr['shareId'],'userId'=>$arr['userId']])->find();
        if(!$data||$data['isDelete']==1)return false;
        Db::startTrans();
        try{
            db('mom_date_text')->update([
                'id'=>$arr['shareId'],
                'isDelete' => 1
                ]);
            db('mom_date_img')->where([
                    'userId'=>$arr['userId'],
                    'momId' => $data['momId'],
                    'isDelete'=>0
                ])->update(['isDelete'=>1]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //删除分享图片
    public function delShareMomImgData($arr)
    {
        $data = db('mom_date_img')->where(['id'=>$arr['imgId'],'userId'=>$arr['userId']])->find();
        if(!$data||$data['isDelete']==1)return false;
        $a = db('mom_date_img')->update([
                'id' => $arr['imgId'],
                'isDelete' => 1
            ]);
        return $a?true:false;
    }

    //我发起的活动
    public function myMomDateData($arr)
    {
        $data = db('mom_date m')
            ->field('m.id as momId,m.name,m.winNum,m.dateTime,m.address,m.peopleNum,minAge,maxAge,m.status,u.name as userName,u.face,memberLevel,memberEndTime')
            ->join('user u','u.id=m.userId','left')
            ->where(['m.isDelete'=>0,'m.userId'=>$arr['userId'],'m.status'=>['in',[0,1,2]]])
            ->page($arr['page'],$arr['pagesize'])
            ->order('dateTime desc')
            ->select();
        return $data;
    }

    //我参与的活动
    public function myJoinMomDateData($arr)
    {
        $data = db('mom_date m')
            ->field('m.id as momId,m.name,m.winNum,m.dateTime,m.address,m.peopleNum,minAge,maxAge,m.status,e.status as myStatus,u.name as userName,u.face,memberLevel,memberEndTime')
            ->join('mom_date_enrol e','e.momId=m.id','left')
            ->join('user u','u.id=m.userId','left')
            ->where(['e.isDelete'=>0,'e.userId'=>$arr['userId'],'e.status'=>['in',[0,1,3]]])
            ->page($arr['page'],$arr['pagesize'])
            ->order('dateTime desc')
            ->select();
        return $data;
    }

    //活动结束
    public function momDateEndData($arr)
    {
        $data = db('mom_date')->field('status')->where([
                'id' => $arr['momId'],
                'userId' => $arr['userId'],
                'isDelete' => 0
            ])->find();
        if(!$data||$data['status']!=1)return false;
        $a = db('mom_date')->update([
                'id'=>$arr['momId'],
                'status'=>2
            ]);
        return $a?true:false;
    }
}