<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-08-14
 */

namespace app\api\model;

use think\Db;
use think\Model;
use app\common\libs\BankInfo as info;
use think\Config;

class My extends Model 
{
    //1.个人中心
    public function myCenterData($uid)
    {
        $data = db('user')
            ->alias('u')
            ->field('u.phone,isAdviser,isReferrer,isBenefit,face,u.name,balance,doudou,referrerId,memberLevel,memberEndTime,isCmbc,b.sex as babySex,b.nickname as babyNickname,b.birth as babyBirth')
            ->join('user_baby b','u.id=b.userId','left')
            ->where('u.id',$uid)
            ->find();
        $data['level'] = $data['memberLevel'];
        if($data['memberLevel']){
            if($data['memberEndTime']<time()){
                $data['level'] = 0;
            }
        }
        $phone1 = substr($data['phone'],0,3);
        $phone2 = substr($data['phone'],-4);
        $data['phone'] = $phone1."****".$phone2;
        $data['vipFree'] = $this->vipFree($uid);
        $data['mybuy'] = $this->mybuy($uid);
        $data['mygoods'] = $this->mygoods($uid);
        $data['myclass'] = $this->myclass($uid);
        $data['referrerPhone'] = '';
        if($data['referrerId']){
            $data['referrerPhone'] = $this->referrerPhone($data['referrerId']);
        }
        $data['isAdviserWork']=$data['isAdviser']?$this->isAdviserWork($uid):0;
        unset($data['referrerId']);
        return $data;
    }
    //推荐人号码
    private function referrerPhone($id)
    {
        $phone = db('user')->where('id',$id)->value('phone');
        return $phone;
    }
    //是否是在职顾问
    private function isAdviserWork($uid)
    {
        $data = db('adviser_name')->where(['userId'=>$uid,'isDelete'=>0])->value('id');
        return $data?1:0;
    }
    //我的女王权杖
    private function vipFree($uid)
    {
        $vipFree = db('vip_use')->where(['userId'=>$uid,'status'=>0])->count();
        return $vipFree;
    }
    //我的团购券
    private function mybuy($uid)
    {
        $mybuy = db('order_buy')->where(['userId'=>$uid,'status'=>1])->count();
        return $mybuy;
    }
    //我的福利商品
    private function mygoods($uid)
    {
        $mygoods = db('goods_free_get')->where(['uid'=>$uid,'status'=>0])->count();
        return $mygoods;
    }
    //我的体验课
    private function myclass($uid)
    {
        $myclass = db('user_class')->where(['userId'=>$uid,'status'=>0])->count();
        return $myclass;
    }

    //2.会员奖励
    public function userAwardData($arr)
    {
        $data = db('record_money')
            ->alias('r')
            ->field('r.money,r.type,o.name,o.payDate')
            ->join('order_class o','r.orderId = o.id','left')
            ->where(['r.type'=>7,'r.userId'=>$arr['uid']])
            ->order('o.payDate desc')
            ->page($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }

    //3.充值提现
    public function drawMoneyData($arr)
    {
        //$arr['uid'] = 30;
        $data = db('money_bank')
            ->alias('m')
            ->field('m.money,m.status,m.createTime,bankName,bankLast,m.remarks')
            ->join('bank_user b','b.id=m.bankId','left')
            ->where(['isDelete'=>0,'m.userId'=>$arr['uid']])
            ->order('m.createTime desc')
            ->page($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }

    //4.推荐人奖励
    public function referrerMoneyData($arr)
    {
        $data = $this->awardMoney($arr,8);
        return $data;
    }

    //5.顾问奖励
    public function adviserMoneyData($arr)
    {
        $data = $this->awardMoney($arr,9);
        return $data;
    }

    //5-1.受益人奖励
    public function benefitMoneyData($arr)
    {
        $data = $this->awardMoney($arr,14);
        return $data;
    }

    private function awardMoney($arr,$type)
    {
        $data = db('record_money')->field('money,createTime')
                    ->where(['userId'=>$arr['uid'],'type'=>$type])
                    ->where('money','gt',0)
                    ->order('createTime desc')
                    ->page($arr['page'],$arr['pagesize'])
                    ->select();
        return $data;
    }

    //6.银行卡管理
    public function bankCardData($uid)
    {
        //$uid = 4;
        $data = db('bank_user')->field('id as bankId,bankName,bankLogo,bankLast,bankType')->where(['userId'=>$uid,'isDelete'=>0])->select();
        return $data;
    }

    //7.解绑银行卡
    public function bankCardUnbindData($arr)
    {
        //$arr['uid'] = 4;
        $data = db('bank_user')->where(['id'=>$arr['bankId'],'userId'=>$arr['uid'],'isDelete'=>0])->update(['isDelete'=>1]);
        return $data;
    }

    //8.银行卡绑定
    public function bankCardBindData($arr)
    {
        //$arr['uid'] = 4;
        $res = db('bank_user')
                ->where([
                    'bankCard' => $arr['bankCard'],
                    'userId' =>$arr['uid']
                    ])->find();
        if($res){
            if($res['isDelete']==0)return 3;
            if($res['isDelete']==1){
                $a = db('bank_user')->update([
                        'isDelete' => 0,
                        'id' => $res['id']
                    ]);
                return $a;
            }
        }
        $info = new info();
        $re = $info -> bankCard($arr['bankCard']);
        if($re===1)return 2;
        $code = substr($arr['bankCard'] , 0 , 6);
        $res = db('bank_info_new')->where(['bankBin' => $code])->find();
        $data = [
            'userId' => $arr['uid'],
            'bankCard' => $arr['bankCard'],
            'bankLast' => substr($arr['bankCard'], -4),
            'bankPhone' => $arr['phone'],
            'bankUserName' => $arr['name'],
            'createTime' => time(),
            'isDelete' => 0
        ];
        if($res){
            $data['bankName'] = $res['bankName'];
            $data['bankLogo'] = $res['bankLogo'];
            $data['bankType'] = $res['bankType'];
        }else{
            if(is_array($re)){
                $res = db('bank_name')->where('bankCode',$re['bankCode'])->find();
                $array = [
                    'bankType' => $re['bankType'],
                    'bankCode' => $re['bankCode'],
                    'bankName' => $res?$res['bankName']:"银行",
                    'bankLogo' => $res?$res['bankLogo']:'',
                    'bankTypeCode' => $re['bankTypeCode'],
                    'bankBin' => $code,
                    'createTime' => time(),
                ];
                db('bank_info_new')->insert($array);
                $data['bankName'] = $array['bankName'];
                $data['bankLogo'] = $array['bankLogo'];
                $data['bankType'] = $array['bankType'];
            }else{
                $data['bankName'] = '银行';
                $data['bankLogo'] = "";
                $data['bankType'] = "储蓄卡";
            }
        }

        $ret = db('bank_user')->insert($data);
        return $ret;
    }

    //修改个人信息
    public function myInfoAlterData($arr)
    {
        $a = db('user')->update($arr);
        return $a?true:false;
    }

    //修改宝宝信息
    public function myBabyInfoAlterData($arr)
    {
        if(isset($arr['birthTime'])){
            $arr['birth']=date('Y-m-d',$arr['birthTime']);
        }
        $data = $arr;
        unset($data['userId']);
        $a = db('user_baby')->where('isDelete=0 and userId='.$arr['userId'])->update($data);
        return $a?true:false;
    }

    //设置支付密码页面
    public function PayPasswordPageData($uid)
    {
        $data = db('user')->where('id',$uid)->value('phone');
        return $data;
    }

    //设置/找回支付密码
    public function createPayPasswordData($arr)
    {
        $data = db('user')->field('payPassword')->where('id',$arr['id'])->find();
        if(!$data)return false;
        $payPassword = md5($arr['payPassword'].Config::get('validationKey'));
        if($payPassword==$data['payPassword']){return 'passwordW';}
        $a = db('user')->update([
                'id'=>$arr['id'],
                'payPassword' => $payPassword
            ]);
        return $a?true:false;
    }

    //修改支付密码
    public function alterPayPasswordData($arr)
    {
        $arr['wornPayPassword'] = md5($arr['wornPayPassword'] .Config::get('validationKey'));
        $data = db('user')->where('id',$arr['id'])->value('payPassword');
        if(!$data||$data!=$arr['wornPayPassword'])return false;
        $payPassword = md5($arr['payPassword'] .Config::get('validationKey'));
        $a = db('user')->update([
                'id'=>$arr['id'],
                'payPassword'=>$payPassword
            ]);
        return $a?true:false;
    }

    //验证是否有支付密码
    public function isPayPasswordData($uid)
    {
        $data = db('user')->where('id',$uid)->value('payPassword');
        return $data?['status'=>1]:['status'=>0];
    }

    //判断招商
    public function cmbcCodeData($arr)
    {
        $res = db('cmbc_code')->where([
                'cmbcCode'=>$arr['cmbcCode'],
                'isUse'=>0
            ])->find();
        if(!$res)return 'cmbcCode';
        if(($res['createTime']+$res['term']*86400)<time())return 'cmbcCodeTime';
        $re = db('user')->where('id',$arr['userId'])->value('isCmbc');
        if($re)return 'cmbcUser';
        $arr['isUse']=1;
        $arr['useTime']=time();
        $arr['id']=$res['id'];
        Db::startTrans();
        try{
            db('cmbc_code')->update($arr);
            db('user')->update(['id'=>$arr['userId'],'isCmbc'=>1]);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
        return true;
    }

    //更换手机号
    public function changePhoneData($arr)
    {
        $data = db('user')->where('phone',$arr['phone'])->value('id');
        if($data)return 'phoneError';
        $a = db('user')->update([
                'id'=>$arr['userId'],
                'phone'=>$arr['phone']
            ]);
       return $a?true:false;
    }

    //提现
    public function userWithdrawData($arr)
    {
        $money = db('user')->where('id',$arr['userId'])->value('balance');
        if($arr['money']>$money){return 'payNoMoney';}
        $bank = db('bank_user')->where('id',$arr['bankId'])->find();
        if($bank['userId']!=$arr['userId']||$bank['isDelete']==1)
        {
            return 'bankCardError';
        }
        $data = [
            'userId'=>$arr['userId'],
            'bankId'=>$arr['bankId'],
            'money'=>$arr['money'],
            'status'=>0,
            'createTime'=>time(),
            'orderNo'=>orderId('K'),
            'exportStatus'=>0
        ];
        $id = db('money_bank')->insert($data);
        if(!$id)return false;
        $a = new \app\common\model\UserMoney();
        $a->reduceUserMoney($arr['money'],6,$id,$data['orderNo'],$arr['userId']);
        return true;

    }

    //意见反馈
    public function feedbackData($arr)
    {
        $time1 = strtotime(date('Y-m-d',time()));
        $time2 = strtotime(date('Y-m-d 23:59:59',time()));
        $data = db('feed_back')->where('createTime','between',$time1.",".$time2)->count('id');
        if($data>=3)return false;
        $data = [
            'userId'=>$arr['userId'],
            'msg'=>$arr['text'],
            'createTime'=>time()
        ];
        $data['contact'] = db('user')->where('id',$arr['userId'])->value('phone');
        $a = db('feed_back')->insert($data);
        return $a?true:false;
    }

    //推荐人
    public function myReferrerData($arr)
    {
        $referrerId = db('user')->where('id',$arr['userId'])->value('referrerId');
        if($referrerId)return 'recom';
        $isReferrer = db('user')->field('id,isReferrer')->where('phone',$arr['phone'])->find();
        if(!$isReferrer)return 'userNo';
        if(!$isReferrer['isReferrer']){
            db('user')->update([
                'id' => $isReferrer['id'],
                'isReferrer'=>1
            ]);
        }
        $a = db('user')->update([
                'id' => $arr['userId'],
                'referrerId'=>$isReferrer['id']
            ]);
        return $a?true:false;
    }

    //获取消息
    public function getMessageData($arr)
    {
        $data = db('push_user')->field('id,createTime,title,content,type')->where([
                'userId'=>$arr['uid'],
                'isRead'=>0
            ])->page($arr['page'],$arr['pagesize'])->select();
        return $data;
    }

    //修改消息状态
    public function readMessageData($arr)
    {
        $data = db('push_user')->find($arr['id']);
        if(!$data)return false;
        db('push_user')->update([
                'id'=>$arr['id'],
                'isRead'=>1,
                'readTime'=>time()
            ]);
        return true;
    }

    //是否有未读消息
    public function isReadMessageData($uid)
    {
        $data = db('push_user')->where([
                'userId'=>$uid,
                'isRead'=>0
            ])->count();
        return $data;
    }


}