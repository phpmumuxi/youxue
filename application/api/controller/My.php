<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-08-14
 */

//  品牌课程

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\My as MyModel;

class My extends BaseApi
{
    //1.个人中心
    public function myCenter()
    {
        $my = new MyModel();
        $data = $my -> myCenterData($this->uid);
        $this->apiSuccess($data);
    }

    //2.会员奖励
    public function userAward()
    {
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
            'uid' => $this->uid
        ];
        $my = new MyModel();
        $data = $my -> userAwardData($arr);
        $this->apiSuccess($data);
    }

    //3.充值提现
    public function drawMoney()
    {
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
            'uid' => $this->uid
        ];
        $my = new MyModel();
        $data = $my -> drawMoneyData($arr);
        $this->apiSuccess($data);
    }

    //4.推荐人奖励
    public function referrerMoney()
    {
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
            'uid' => $this->uid
        ];
        $my = new MyModel();
        $data = $my -> referrerMoneyData($arr);
        $this->apiSuccess($data);
    }

    //5.顾问奖励
    public function adviserMoney()
    {
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
            'uid' => $this->uid
        ];
        $my = new MyModel();
        $data = $my -> adviserMoneyData($arr);
        $this->apiSuccess($data);
    }

    //5-1.受益人奖励
    public function benefitMoney()
    {
        $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',20),
            'uid' => $this->uid
        ];
        $my = new MyModel();
        $data = $my -> benefitMoneyData($arr);
        $this->apiSuccess($data);
    }

    //6.银行卡管理
    public function bankCard()
    {
        $my = new MyModel();
        $data = $my -> bankCardData($this->uid);
        $this->apiSuccess($data);
    }

    //7.银行卡解绑
    public function bankCardUnbind()
    {
        $arr = [
            'uid' => $this->uid
        ];
        if(input('post.bankId/d')){
            $arr['bankId'] = input('post.bankId/d');
        }else{
            $this->apiError('paramError');
        }
        if(input('post.password')){
            $arr['password'] = input('post.password');
        }else{
            $this->apiError('paramError');
        }
        $this->payPassword($arr['password']);
        $my = new MyModel();
        $data = $my -> bankCardUnbindData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('bankCardMiss');
        }
    }

    //8.银行卡绑定
    public function bankCardBind()
    {
        $arr = [
            'uid' => $this->uid
        ];
        if(!input('post.bankCard/d')||!input('post.name')||!input('post.phone')){
            $this->apiError('paramError');
        }else{
            $arr['bankCard'] = input('post.bankCard');
            $arr['name'] = input('post.name');
            $arr['phone'] = input('post.phone');
        }
        $my = new MyModel();
        $data = $my -> bankCardBindData($arr);
        if($data===3)$this->apiError('cardHad');
        if($data===2)$this->apiError('bankCardError');
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //9.个人余额接口
    public function myInfoData()
    {
        $data = $this->userInfo();
        unset($data['isFictitious']);
        unset($data['isGive']);
        unset($data['referrerId']);
        $this->apiSuccess($data);
    }

    //10.修改个人信息
    public function myInfoAlter()
    {
        $arr = input('post.');
        unset($arr['token']);
        if(count($arr)!=1){
            $this->apiError('paramError');
        }
        $key = array_keys($arr);
        $key = implode(',',$key);
        $keys = ['name','face'];
        if(!in_array($key, $keys)){
            $this->apiError('paramError');
        }
        $v = array_values($arr);
        if(!$v){
            $this->apiError('paramError');
        }
        if(isset($arr['name'])){
            $a = strlen($arr['name']);
            if($a>10){
                $arr['name'] = mb_substr($arr['name'],0,10,'utf-8');
            }
        }
        $arr['id'] = $this->uid;
        $my = new MyModel();
        $data = $my -> myInfoAlterData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //11.修改宝宝信息
    public function myBabyInfoAlter()
    {
        $arr = input('post.');
        unset($arr['token']);
        if(count($arr)!=1){
            $this->apiError('paramError');
        }
        $key = array_keys($arr);
        $key = implode(',',$key);
        $keys = ['sex','nickname','birthTime'];
        if(!in_array($key, $keys)){
            $this->apiError('paramError');
        }
        $v = array_values($arr);
        if(!$v){
            $this->apiError('paramError');
        }
        if(isset($arr['nickname'])){
            $a = strlen($arr['nickname']);
            if($a>10){
                $arr['nickname'] = mb_substr($arr['nickname'],0,10,'utf-8');
            }
        }
        $arr['userId'] = $this->uid;
        $my = new MyModel();
        $data = $my -> myBabyInfoAlterData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //12-1.设置支付密码页面
    public function PayPasswordPage()
    {
        $my = new MyModel();
        $data = $my -> PayPasswordPageData($this->uid);
        $phone = 0;
        if($data){
            $a = substr($data,0,3);
            $b = substr($data,-4);
            $phone = $a."****".$b;
        }
        $this->apiSuccess($phone);
    }

    //12-2.设置支付密码
    public function createPayPassword()
    {
        $arr=[
            'payPassword'=>input('post.payPassword'),
            'code'=>input('post.code')
        ];
        if(!$arr['payPassword']||strlen($arr['payPassword'])!=6)$this->apiError('paramError');
        if(strlen($arr['code'])!=6){
            $this->apiError('code');
        }
        $my = new MyModel();
        $this->verifyCode($my->PayPasswordPageData($this->uid),$arr['code']);

        $arr['id']=$this->uid;
        // $my = new MyModel();
        $data = $my -> createPayPasswordData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //12-3.修改支付密码
    public function alterPayPassword()
    {
        $arr=[
            'wornPayPassword'=>input('post.wornPayPassword'),
            'payPassword'=>input('post.payPassword'),
            'confirm'=>input('post.confirm')
        ];
        if(strlen($arr['wornPayPassword'])!=6||strlen($arr['payPassword'])!=6){
            $this->apiError('paramError');
        }
        if($arr['wornPayPassword']==$arr['payPassword']) $this->apiError('paramError');
        $this->payPassword($arr['wornPayPassword']);
        if($arr['confirm']!=$arr['payPassword']){
            $this->apiError('passwordT');
        }
        $arr['id']=$this->uid;
        $my = new MyModel();
        $data = $my -> alterPayPasswordData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //12-4.忘记支付密码
    public function findPayPassword()
    {
        $arr=[
            'payPassword'=>input('post.payPassword'),
            'code'=>input('post.code')
        ];
        if(!$arr['payPassword']||strlen($arr['payPassword'])!=6)$this->apiError('paramError');
        if(strlen($arr['code'])!=6){
            $this->apiError('code');
        }
        $my = new MyModel();
        $this->verifyCode($my->PayPasswordPageData($this->uid),$arr['code']);

        $arr['id']=$this->uid;
        // $my = new MyModel();
        $data = $my -> createPayPasswordData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }
    }

    //13.验证是否有支付密码
    public function isPayPassword()
    {
        $my = new MyModel();
        $data = $my -> isPayPasswordData($this->uid);
        $this->apiSuccess($data);
    }
    //14.成为招商用户
    public function cmbcCode()
    {
        $arr['cmbcCode']=input('post.cmbcCode');
        if(strlen($arr['cmbcCode'])!=6){
            $this->apiError('cmbcCode');
        }
        $arr['userId']=$this->uid;
        $my = new MyModel();
        $data = $my -> cmbcCodeData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }
    }

    //15.更换手机号
    public function changePhone()
    {
        $arr = [
            'userId'=>$this->uid,
            'phone'=>input('post.phone'),
            'code'=>input('post.code')
        ];
        if(!preg_match("/^1[34578]{1}\d{9}$/",$arr['phone'])){
            $this->apiError('phone');
        }
        if(strlen($arr['code'])!=6){
            $this->apiError('code');
        }
        $this->verifyCode($arr['phone'],$arr['code']);
        $my = new MyModel();
        $data = $my -> changePhoneData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }
    }

    //16.提现
    public function userWithdraw()
    {
        $arr = [
            'userId'=>$this->uid,
            'bankId'=>input('post.bankId/d'),
            'money'=>input('post.money/f'),
        ];
        if(!$arr['bankId'])$this->apiError('paramError');
        if($arr['money']<100){
            $this->apiError('moneyMin');
        }
        $my = new MyModel();
        $data = $my->userWithdrawData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }
    }

    //17.意见反馈
    public function feedback()
    {
        $arr = [
            'userId'=>$this->uid,
            'text'=>input('post.text'),
        ];
        if(!$arr['text']||strlen($arr['text'])>300)$this->apiError('paramError');
        $my = new MyModel();
        $data = $my->feedbackData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //18.添加推荐人
    public function myReferrer()
    {
        $arr = [
            'userId'=>$this->uid,
            'phone'=>input('post.phone'),
        ];
        if(!preg_match("/^1[34578]{1}\d{9}$/",$arr['phone'])){
            $this->apiError('phone');
        }
        $my = new MyModel();
        $data = $my->myReferrerData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }
    }

    //19.消息页面
    public function getMessage()
    {
         $arr = [
            'page'=>input('post.page/d',1),
            'pagesize'=>input('post.pagesize/d',10),
            'uid' => $this->uid
        ];
        $my = new MyModel();
        $data = $my->getMessageData($arr);
        $this->apiSuccess($data);
    }

    //20. 阅读消息
    public function readMessage()
    {
        $arr = [
            'userId'=>$this->uid,
            'id'=>input('post.id/d'),
        ];
        if(!$arr['id'])$this->apiError('paramError');
        $my = new MyModel();
        $data = $my->readMessageData($arr);
        $this->apiSuccess($data);
    }

    //21.是否有未读的消息
    public function isReadMessage()
    {
        $my = new MyModel();
        $data = $my->isReadMessageData($this->uid);
        $this->apiSuccess($data);
    }
}