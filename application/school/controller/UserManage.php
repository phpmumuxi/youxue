<?php
/**
 * User: LiuTong
 * Date: 2017/11/13
 * Time: 16:00
 */

namespace app\school\controller;

use app\common\model\SchoolCommon;
use app\school\model\UserManage as userManageModel;
use app\common\controller\AdminBase;
use think\Validate;

class UserManage extends AdminBase
{
    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-11-13
     * 用户补录入
     * @return \think\response\View
     */
    public function userAdd()
    {
        $schoolCommon = new SchoolCommon();
        $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);
        $userManageModel = new userManageModel();
        $data = $userManageModel->getUserAdd($schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('userAdd',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-11-13
     * 录入用户信息
     */
    public function userSave()
    {
        $res['status'] = 'false';

        $validate = new Validate(
            [
                'name' => 'require',
                'phone' => 'require'
            ]
        );

        $data['name'] = input('post.name');
        $data['phone'] = input('post.phone');

        if ($validate->check($data)) {
            $data['babyNickName'] = input('post.babyName');
            $data['babyBirth'] = input('post.babyBirth');
            $data['babySex'] = input('post.babySex');

            $schoolCommon = new SchoolCommon();
            $schoolInfo = $schoolCommon->getSchoolAdminInfo($this->admin_id);
            $data['adminId'] = $this->admin_id;
            $data['schoolId'] = $schoolInfo['schoolId'];
            $data['shopId'] = $schoolInfo['shopId'];

            $userManageModel = new userManageModel();
            $retPhone = $userManageModel->checkUserPhone($data['phone']);
            if ($retPhone) {
                $res['status'] = 'errHad';
                $res['msg'] = 'Phone Exist';
            } else {
                $ret = $userManageModel->saveUser($data);
                if ($ret) {
                    $res['status'] = 'ok';
                } else {
                    $res['msg'] = 'Save Failure';
                }
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-11-13
     * 删除补录的用户信息 未提交到用户表之前可以删除
     */
    public function userDelete()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $userManageModel = new userManageModel();
            $ret = $userManageModel->deleteUser($id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Delete Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['mag'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-13
     * 获取所需修改的用户信息
     */
    public function getUserInfo()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $userManageModel = new userManageModel();
            $retData = $userManageModel->userInfoGet($id);
            if ($retData) {
                $res['status'] = 'ok';
                $res['data'] = $retData;
            } else {
                $res['msg'] = 'No Data';
            }
        } else {
            $res['status'] = 'err';
            $res['mag'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-14
     * 保存到用户表
     */
    public function doToUser()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $userManageModel = new userManageModel();
            $ret = $userManageModel->toUserDone($id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Do Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['mag'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-14
     * 更新用户录入信息
     */
    public function userUpdate()
    {
        $res['status'] = 'false';

        $validate = new Validate(
            [
                'name' => 'require',
                'phone' => 'require',
                'id' => 'require'
            ]
        );

        $data['name'] = input('post.name');
        $data['phone'] = input('post.phone');
        $data['id'] = input('post.id');

        if ($validate->check($data)) {
            $data['babyNickName'] = input('post.babyName');
            $data['babyBirth'] = input('post.babyBirth');
            $data['babySex'] = input('post.babySex');

            $schoolCommon = new SchoolCommon();
            $schoolInfo = $schoolCommon->getSchoolAdminInfo($this->admin_id);
            $data['adminId'] = $this->admin_id;
            $data['schoolId'] = $schoolInfo['schoolId'];
            $data['shopId'] = $schoolInfo['shopId'];
            $id = $data['id'];
            unset($data['id']);
            $userManageModel = new userManageModel();
            $retPhone = $userManageModel->checkUserPhone($data['phone']);
            if ($retPhone) {
                $res['status'] = 'errHad';
                $res['msg'] = 'Phone Exist';
            } else {
                $ret = $userManageModel->updateUser($id, $data);
                if ($ret) {
                    $res['status'] = 'ok';
                } else {
                    $res['msg'] = 'Update Failure';
                }
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-11-20
     * 保存用户备注信息
     */
    public function userMarkInfo()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.mark')) {
            $id = input('post.id');
            $mark = input('post.mark');
            $userManageModel = new userManageModel();
            $ret = $userManageModel->userMarkInfoSave($id, $mark, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Do Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['mag'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-20
     * 确认更新状态为老用户
     */
    public function oldCustomSure()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $userManageModel = new userManageModel();
            $ret = $userManageModel->saveOldCustom($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Do Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['mag'] = 'Param Missing';
        }
        echo json_encode($res);
    }

}