<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-13
 */

namespace app\api\model;

use think\Db;
use think\Model;

//登录注册
class Login extends Model
{

    protected $table = 'user';
    //登录
    public function appEnterData($arr)
    {
        $arr['password'] = md5($arr['password']);
        // print_r($arr);exit;
        $data = db('user')
            ->field('id,isAdviser,isReferrer,memberLevel,memberEndTime,name,face,phone,isCmbc')
            ->where($arr)
            ->find();
        if(!$data)return false;
        $data['level']=$data['memberLevel'];
        if($data['memberLevel']&&$data['memberEndTime']<time()){
            $data['level']=0;
        }
        $token = $data['id'] . '|' . token();
        db('user')->update([
                'id'=>$data['id'],
                'token' =>$token
            ]);
        $data['token'] = $token;
        $data['isAdviserWork']=$data['isAdviser']?$this->isAdviserWork($data['id']):0;
        $data['isBaby'] = $this->userBaby($data['id']);
        return $data;
    }

    //判断是否录入宝宝信息
    private function userBaby($uid)
    {
        $data = db('user_baby')->where('isDelete=0 and userId='.$uid)->value('id');
        return $data?1:0;
    }

    //是否是在职顾问
    private function isAdviserWork($uid)
    {
        $data = db('adviser_name')->where(['userId'=>$uid,'isDelete'=>0])->value('id');
        return $data?1:0;
    }

    //注册
    public function registerData($arr)
    {
        $data = $this->infoUser($arr);
        if($data&&$data['password']){
            return 'phoneError';
        }
        $array=[
            'phone' => $arr['phone'],
            'password' => md5($arr['password']),
            'createTime' => time(),
            'agr_no'=> $arr['agr_no'],
            'status'=>0,
            'isCmbc'=>0,
            'memberLevel'=>0,
            'face'=> '',
            'name'=>'用户_'.time(),
            'token' => 0
        ];
        if($data){
            $array['id']=$data['id'];
        }
        $user = model('user');
        $a = $user->save($array);
        if(!$a)return false;
        $id = $user->id;
        $token = $id . '|' . token();
        $a = db('user')->update(['id'=>$id,'token'=>$token]);
        if(!$a)return false;
        return [
            'token' => $token,
            'isAdviser' => isset($data['isAdviser'])?$data['isAdviser']:0,
            'isReferrer' => 0,
            'memberLevel' => 0
        ];
    }
    //找回密码
    public function forgetPasswordData($arr)
    {
        $data = $this->infoUser($arr);
        if(!$data)return 'usermiss';
        $array = [
            'id' =>$data['id'],
            'password' =>md5($arr['password']),
            'token' => $data['id'] . '|' . token()
        ];
        $a = db('user')->update($array);
        return $a?true:false;
    }

    //修改密码
    public function changePasswordData($arr)
    {
        $data = db('user')->where('id',$arr['userId'])->value('password');
        if(!$data)return $data;
        if($data!=md5($arr['wornPassword']))return 'passWordError';
        $array = [
            'id' => $arr['userId'],
            'password' =>md5($arr['password']),
            'token' => $arr['userId'] . '|' . token()
        ];
        $a = db('user')->update($array);
        return $a?true:false;
    }

    //补充宝宝信息
    public function babyInfoData($arr)
    {
        $data = db('user_baby')->where('isDelete=0 and userId='.$arr['userId'])->find();
        if($data)return false;
        $arr['birth'] = date('Y-m-d',$arr['birthTime']);
        $arr['createTime'] = time();
        $arr['isDelete'] = 0;
        $a = db('user_baby')->insert($arr);
        return $a?true:false;
    }


    public function infoUser($arr)
    {
        $data = db('user')
            ->field('id,password,isAdviser,isReferrer,memberLevel')
            ->where('phone',$arr['phone'])
            ->find();
        return $data;
    }

}