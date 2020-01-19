<?php
/**
 * User: LiuTong
 * Date: 2017/11/16
 * Time: 10:15
 */

namespace app\school\controller;

use app\school\model\OrderSubscribe as OrderSubscribeModel;
use app\common\controller\AdminBase;
use app\common\model\SchoolCommon;
use think\Validate;

class OrderSubscribe extends AdminBase
{
    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-11-16
     * 线下预订列表
     * @return \think\response\View
     */
    public function orderSubscribeList()
    {
        $schoolCommon = new SchoolCommon();
        $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);

        $orderSubscribe = new OrderSubscribeModel();
        $data = $orderSubscribe->getOrderSubscribeList($schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderSubscribeList',
            [
                'pages' => $pages,
                'data' => $data
            ]
        );
    }

    /**
     * Date: 2017-11-16
     * 新增页面
     * @return \think\response\View
     */
    public function addOrderSubscribe()
    {
        $schoolCommon = new SchoolCommon();
        $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);
        $orderSubscribeModel = new OrderSubscribeModel();
        $adviserData = $orderSubscribeModel->schoolAdviser($schoolId);
        return view('addOrderSubscribe', ['adviserData' => $adviserData]);
    }

    /**
     * Date: 2017-11-29
     * 新增页面零元预订
     * @return \think\response\View
     */
    public function addOrderSubscribeZero()
    {
        $schoolCommon = new SchoolCommon();
        $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);
        $orderSubscribeModel = new OrderSubscribeModel();
        $adviserData = $orderSubscribeModel->schoolAdviser($schoolId);
        return view('addOrderSubscribeZero', ['adviserData' => $adviserData]);
    }
    /**
     * Date: 2017-11-16
     * 线下订单保存
     */
    public function orderSubscribeSaveZero()
    {
        $validate = new Validate(
            [
                'userId' => 'require|number',
                'mark' => 'require',
                'adviserId' => 'require',
            ]
        );

        $data['userId'] = input('post.userId');
        $data['money'] = 0;
        $data['payType'] = 4;
        $data['orderType'] = 1;
        $data['orderInfo'] = '';
        $data['mark'] = input('post.mark');
        $data['adviserId'] = input('post.adviserId');

        if ($validate->check($data)) {

            $schoolCommon = new SchoolCommon();
            $schoolInfo = $schoolCommon->getSchoolAdminInfo($this->admin_id);
            $data['schoolId'] = $schoolInfo['schoolId'];
            $data['shopId'] = $schoolInfo['shopId'];
            $data['adminId'] = $this->admin_id;

            $orderSubscribeId = input('get.id');
            $pid = 0;
            $pid = input('post.pid');

            $orderSubscribeModel = new OrderSubscribeModel();
            if ($orderSubscribeId) {
                $ret = $orderSubscribeModel->orderSubscribeUpdate($data, $orderSubscribeId);
            } else {
                $ret = $orderSubscribeModel->orderSubscribeSave($data, $pid);
            }

            if ($ret) {
                $this->success('操作成功!', '/school/OrderSubscribe/orderSubscribeList');
            } else {
                $this->error('操作失败!');
            }

        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-11-16
     * 线下订单保存
     */
    public function orderSubscribeSave()
    {
        $validate = new Validate(
            [
                'userId' => 'require|number',
                'money' => 'require|number',
                'payType' => 'require|number',
                'orderType' => 'require|number',
                'orderInfo' => 'require',
                'adviserId' => 'require',
            ]
        );

        $data['userId'] = input('post.userId');
        $data['money'] = input('post.money');
        $data['payType'] = input('post.payType');
        $data['orderType'] = input('post.orderType');
        $data['orderInfo'] = input('post.orderInfo');
        $data['mark'] = input('post.mark');
        $data['adviserId'] = input('post.adviserId');

        if ($validate->check($data)) {

            $schoolCommon = new SchoolCommon();
            $schoolInfo = $schoolCommon->getSchoolAdminInfo($this->admin_id);
            $data['schoolId'] = $schoolInfo['schoolId'];
            $data['shopId'] = $schoolInfo['shopId'];
            $data['adminId'] = $this->admin_id;

            $orderSubscribeId = input('get.id');
            $pid = 0;
            $pid = input('post.pid');

            $orderSubscribeModel = new OrderSubscribeModel();
            if ($orderSubscribeId) {
                $ret = $orderSubscribeModel->orderSubscribeUpdate($data, $orderSubscribeId);
            } else {
                $ret = $orderSubscribeModel->orderSubscribeSave($data, $pid);
            }

            if ($ret) {
                $this->success('操作成功!', '/school/OrderSubscribe/orderSubscribeList');
            } else {
                $this->error('操作失败!');
            }

        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-11-16
     * 修改线下订单信息
     * @return \think\response\View
     */
    public function editOrderSubscribe()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $orderSubscribeModel = new OrderSubscribeModel();
            $data = $orderSubscribeModel->getOrderSubscribeInfo($id);
            return view('editOrderSubscribe', ['data' => $data]);
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-11-16
     * 删除下线订单记录
     */
    public function deleteOrderSubscribe()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $orderSubscribeModel = new OrderSubscribeModel();
            $retOtherMoney = $orderSubscribeModel->checkOtherMoney($id);
            if (!$retOtherMoney) {
                $ret = $orderSubscribeModel->orderSubscribeDelete($id, $this->admin_id);
                if ($ret) {
                    $res['status'] = 'ok';
                } else {
                    $res['msg'] = 'Do Failure';
                }
            } else {
                $res['status'] = 'errHad';
                $res['msg'] = 'Had Other Money';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-11-16
     * 获取用户信息
     */
    public function getUserInfo()
    {
        $res['status'] = 'false';
        if (input('?post.phone')) {
            $schoolCommon = new SchoolCommon();
            $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);
            $phone = input('post.phone');
            $orderSubscribeModel = new OrderSubscribeModel();
            $retData = $orderSubscribeModel->userInfoGet($phone);
            if ($retData) {
                $userId = $retData['id'];
                $ret = $orderSubscribeModel->userOrderMoney($userId, $schoolId);
                if ($ret) {
                    $res['status'] = 'errMoney';
                    $res['msg'] = 'Paid Order Money';
                } else {
                    $res['status'] = 'ok';
                    $res['data'] = $retData;
                }
            } else {
                $res['msg'] = 'Do Data';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-16
     * 填写尾款信息
     * @return \think\response\View
     */
    public function addOrderSubscribeMoney()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $orderSubscribeModel = new OrderSubscribeModel();
            $data = $orderSubscribeModel->getOrderSubscribeInfo($id);
            return view('addOrderSubscribeMoney', ['data' => $data]);
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-11-16
     * 校区课程列表
     */
    public function courseList()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $schoolCommon = new SchoolCommon();
            $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);

            $orderSubscribeModel = new OrderSubscribeModel();
            $retData = $orderSubscribeModel->schoolCourseList($schoolId);
            if ($retData) {
                $res['status'] = 'ok';
                $res['data'] = $retData;
            } else {
                $res['msg'] = 'Do Data';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-16
     * 保存到用户订单
     */
    public function doneOrderSubscribe()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.courseId') && input('?post.isOld')) {
            $id = input('post.id');
            $courseId = input('post.courseId');
            $isOld = input('post.isOld');
            $orderSubscribeModel = new OrderSubscribeModel();
            $retMoney = $orderSubscribeModel->checkOrderMoney($id, $courseId, $isOld);
            if ($retMoney) {
                $ret = $orderSubscribeModel->orderSubscribeDone($id, $courseId, $isOld, $this->admin_id);
                if ($ret) {
                    $res['status'] = 'ok';
                } else {
                    $res['msg'] = 'Do Failure';
                }
            } else {
                $res['status'] = 'errMoney';
                $res['msg'] = 'Money No Equal';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

}