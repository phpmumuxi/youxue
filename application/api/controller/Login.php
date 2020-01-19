<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-12
 */

//  品牌课程

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Login as LoginModel;

class Login extends BaseApi
{
    protected $tokenFlag = 0;

    //1.登录
    public function appEnter()
    {
        $arr['phone'] = input('post.phone');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$arr['phone'])){
            $this->apiError('phone');
        }
        $arr['password'] = input('post.password');
        $a = strlen($arr['password']);
        if(!(6<=$a&&$a<=16)){
            $this->apiError('password');
        }
        $login = new LoginModel();
        $data = $login->appEnterData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('login');
        }
    }

    //2.注册接口
    public function register()
    {
        $arr = $this->appPassword();
        $arr['agr_no'] = $this->agrNo($arr['phone']);
        $login = new LoginModel();
        $data = $login->registerData($arr);
        if($data){
            if(is_array($data)){
                $this->apiSuccess($data);
            }else{
                $this->apiError($data);
            }
        }else{
            $this->apiError('failure');
        }
    }

    //3.忘记密码
    public function forgetPassword()
    {
        $arr = $this->appPassword();
        $login = new LoginModel();
        $data = $login->forgetPasswordData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }

    }

    //4.修改密码
    public function changePassword()
    {
        $arr['userId'] = $this->tokenCheck();
        $arr['wornPassword'] = input('post.wornPassword');
        if(!$arr['wornPassword']){
            $this->apiError('passWordError');
        }
        $arr['password'] = input('post.password');
        $a = strlen($arr['password']);
        if(!(6<=$a&&$a<=16)){
            $this->apiError('password');
        }
        if($arr['wornPassword']==$arr['password']){
            $this->apiError('passwordW');
        }
        $arr['confirm'] = input('post.confirm');
        if($arr['confirm']!=$arr['password']){
            $this->apiError('passwordT');
        }
        $login = new LoginModel();
        $data = $login->changePasswordData($arr);
        if($data){
            $data===true?$this->apiSuccess($data):$this->apiError($data);
        }else{
            $this->apiError('failure');
        }
    }

    //5.宝宝信息补充
    public function babyInfo()
    {
        $uid = $this->tokenCheck();
        $arr = [
                'birthTime' => input('post.birthTime'),
                'sex' => input('post.sex'),
                'nickname' => input('post.nickname'),
                'userId' => $uid
        ];
        $validate = new \think\Validate([
                    'birthTime'  => 'require',
                    'sex'   => 'require|in:1,2',
                    'nickname'   => 'require',
                ]);
        if(!$validate->check($arr)){
            $this->apiError('paramError');
        }
        $login = new LoginModel();
        $data = $login->babyInfoData($arr);
        if($data){
            $this->apiSuccess($data);
        }else{
            $this->apiError('failure');
        }
    }

    //验证手机号是否注册过
    public function isPhone()
    {
        $arr['phone'] = input('post.phone');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$arr['phone'])){
            $this->apiError('phone');
        }
        $login = new LoginModel();
        $data = $login->infoUser($arr);
        if($data){
            $this->apiError('phoneError');
        }else{
            $this->apiSuccess($data);
        }
    }

    private function appPassword()
    {
        $arr['phone'] = input('post.phone');
        if(!preg_match("/^1[34578]{1}\d{9}$/",$arr['phone'])){
            $this->apiError('phone');
        }
        $arr['password'] = input('post.password');
        $a = strlen($arr['password']);
        if(!(6<=$a&&$a<=16)){
            $this->apiError('password');
        }
        $arr['confirm'] = input('post.confirm');
        if($arr['confirm']!=$arr['password']){
            $this->apiError('passwordT');
        }
        $arr['code'] = input('post.code');
        if(strlen($arr['code'])!=6){
            $this->apiError('code');
        }
        $this->verifyCode($arr['phone'],$arr['code']);
        return $arr;
    }

    private function agrNo($phone){
        $str=rand('100000','999999');
        return $phone.$str;
    }
}
