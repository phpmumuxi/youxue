<?php
/**
 * Created by PhpStorm.
 * User: Liu
 * Date: 2017-07-05
 * Time: 13:33
 */

namespace app\common\controller;

use app\common\model\User;
use think\Config;

class BaseApi
{

    protected $tokenFlag = 1;   //  token验证标识
    protected $uid = 0;

    function __construct()
    {
        $tokenFlag = $this->tokenFlag;
        if ($tokenFlag) {
            $this->uid = $this->tokenCheck();
        }
    }

    //  token验证
    protected function tokenCheck()
    {
        $tokenIf = input('?post.token');
        if ($tokenIf) {
            $token = input('post.token');
            strlen($token);
            if(!(32<=strlen($token)&&strlen($token)<=50)){
                $this->apiError('tokenFalse');
            }
            $uid = 0;
            $user = new User();
            $uid = $user->getIdFromToken($token);
            if ($uid) {
                return $this->uid = $uid['id'];
            } else {
                $this->apiError('tokenFalse');
            }
        } else {
            $this->apiError('token');
        }
    }

    //  错误提示
    protected function apiError($arg)
    {
        Config::load(APP_PATH . 'api/config/apiErrorCode.php');
        $ErrorCode = config($arg);
        $Error['code'] = $ErrorCode[0];
        $Error['msg'] = $ErrorCode[1];
        header('Access-Control-Allow-Origin:*');
        echo json_encode($Error, JSON_UNESCAPED_UNICODE);
        die;
    }

    //  成功返回
    protected function apiSuccess($arg)
    {
        $Success['code'] = 100;
        $Success['msg'] = '操作成功';
        header('Access-Control-Allow-Origin:*');
        // if (is_array($arg)) {
            $Success['data'] = $arg;
        // } else {
            // $Success['mark'] = $arg;
        // }
        echo json_encode($Success, JSON_UNESCAPED_UNICODE);
        die;
    }


    //用户信息
    protected function userInfo()
    {
        $user = new User();
        $data = $user->userMember($this->uid);
        return $data;
    }

    //验证支付密码
    protected function payPassword($password)
    {
        if(strlen($password)!=6){
            $this->apiError('safePassError');
        }
        $user = new User();
        $pwd = $user->userPayPassword($this->uid);
        if(!$pwd){
            $this->apiError('safePassError');
        }
        $password = md5($password .Config::get('validationKey'));
        if($pwd!=$password)$this->apiError('safePassError');
    }

    //验证验证码
    protected function verifyCode($phone,$code)
    {
        $user = new User();
        $data = $user->verifyCodeData($phone,$code);
        if(!$data){
            $this->apiError('code');
        }

    }
}