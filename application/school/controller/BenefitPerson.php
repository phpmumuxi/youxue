<?php
/**
 * User: LiuTong
 * Date: 2017-09-21
 * Time: 13:57
 */

namespace app\school\controller;

use app\common\controller\AdminBase;
use app\common\model\SchoolCommon;
use app\school\model\BenefitPerson as BenefitPersonModel;

class BenefitPerson extends AdminBase
{

    public function index()
    {
        return true;
    }

    /**
     * Date: 2017-09-21
     * 受益人列表
     * @return \think\response\View
     */
    public function benefitPersonList()
    {
        $phone = '';
        if (input('?get.phone')) {
            $phone = input('get.phone');
        }
        $benefitPersonModel = new BenefitPersonModel();
        $schoolCommonModel = new SchoolCommon();
        $schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        $data = $benefitPersonModel->getBenefitPersonList($schoolId, $phone);

        return view('benefitPersonList', ['data' => $data, 'phone' => $phone]);
    }

    /**
     * Date: 2017-09-21
     * 新增受益人
     */
    public function benefitPersonAdd()
    {
        $res['status'] = 'false';
        if (input('?post.phone') && input('?post.name')) {
            $phone = input('post.phone');
            $name = input('post.name');
            $schoolCommon = new SchoolCommon();
            $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);
            $benefitPersonModel = new BenefitPersonModel();
            $ifHad = $benefitPersonModel->checkIfHadPerson($phone, $schoolId);
            if ($ifHad) {
                $res['status'] = 'errHad';
                $res['msg'] = 'Phone Exist';
            } else {
                $userId = $benefitPersonModel->searchBenefitPerson($phone);

                if ($userId) {
                    $data['phone'] = $phone;
                    $data['name'] = $name;
                    $data['userId'] = $userId;
                    $ret = $benefitPersonModel->benefitPersonAdd($data, $this->admin_id);
                    if ($ret) {
                        $res['status'] = 'ok';
                    } else {
                        $res['msg'] = 'Add Failure';
                    }
                } else {
                    $res['status'] = 'errNoUser';
                    $res['msg'] = 'User No Sign Up';
                }
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-09-21
     * 检测当前手机号是否已注册成为会员
     * @return json
     */
    public function checkBenefitPerson()
    {
        $res['status'] = 'false';
        if (input('?post.phone')) {
            $phone = input('post.phone');
            $benefitPersonModel = new BenefitPersonModel();
            $ret = $benefitPersonModel->searchBenefitPerson($phone);

            $res['msg'] = 'User No Sign Up';
            if ($ret) {
                $res['status'] = 'ok';
                $res['msg'] = 'User Is Already Member';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-09-21
     * 修改受益人信息
     * @return json
     */
    public function benefitPersonEdit()
    {
        $res['status'] = 'false';
        if (input('?post.phone') && input('?post.name') && input('?post.id')) {
            $phone = input('post.phone');
            $name = input('post.name');
            $id = input('post.id');
            $benefitPersonModel = new BenefitPersonModel();
            $userId = $benefitPersonModel->searchBenefitPerson($phone);
            if (!$userId) {
                $res['status'] = 'errNoUser';
                $res['msg'] = 'No User';
            } else {
                $check = $benefitPersonModel->checkIfSamePerson($userId, $id);
                if ($check) {
                    $ret = $benefitPersonModel->updateBenefitPerson($phone, $name, $id, $this->admin_id);
                    if ($ret) {
                        $res['status'] = 'ok';
                    } else {
                        $res['msg'] = 'Update Failure';
                    }
                } else {
                    $res['status'] = 'errNoSameUser';
                    $res['msg'] = 'No Same User';
                }
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-09-21
     * 删除受益人
     * @return json
     */
    public function benefitPersonDel()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $benefitPersonModel = new BenefitPersonModel();
            $ret = $benefitPersonModel->deleteBenefitPerson($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Delete Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }
}