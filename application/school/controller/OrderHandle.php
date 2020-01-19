<?php
/**
 * User: LiuTong
 * Date: 2017-09-22
 * Time: 16:50
 */

namespace app\school\controller;

use app\common\controller\AdminBase;
use app\common\model\SchoolCommon;
use app\school\model\OrderHandle as OrderHandleModel;

class OrderHandle extends AdminBase
{
    private $schoolId = 0;

    public function _initialize()
    {
        parent::_initialize();
        $schoolCommonModel = new SchoolCommon();
        $this->schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
    }

    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-09-22
     * 消息
     * @return array|mixed
     */
    public function orderHandleList()
    {
        $type = 1;
        if (input('?get.type')) {
            $type = input('get.type');
        }

        $schoolCommonModel = new  SchoolCommon();
        $schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleList($schoolId, $type);

        return view('orderHandleList',
            [
                'data' => $data,
                'num' => count($data),
                'type' => $type
            ]
        );
    }

    /**
     * Date: 2017-09-22
     * 消息数
     * @return json
     */
    public function orderHandleAjax()
    {
        $schoolCommonModel = new SchoolCommon();
        $schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        $orderHandleModel = new OrderHandleModel();
        $num = $orderHandleModel->countAZ($schoolId);
        echo json_encode($num);
    }

    /**
     * 校区顾问列表
     * Date: 2017-09-25
     * @return json
     */
    public function schoolAdviserList()
    {
        $schoolCommonModel = new SchoolCommon();
        $schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);

        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getSchoolAdviserList($schoolId);
        $res['status'] = 'false';
        if ($data) {
            $res['status'] = 'ok';
            $res['data'] = $data;
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-09-25
     * 分配顾问
     * @return json
     */
    public function assignAdviser()
    {
        $res['status'] = 'false';
        if (input('?post.type') && input('?post.id') && input('?post.adviserId')) {
            $id = input('post.id');
            $type = input('post.type');
            $adviserId = input('post.adviserId');


            $orderHandleModel = new OrderHandleModel();
            $ret = $orderHandleModel->assignTypeAdviser($type, $id, $adviserId, $this->schoolId, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Assign Type Adviser Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-09-25
     * 课程
     * @return \think\response\View
     */
    public function orderHandleCourse()
    {
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleCourse($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderHandleCourse',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-09-25
     * 团购
     * @return \think\response\View
     */
    public function orderHandleGroup()
    {
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleGroup($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderHandleGroup',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-09-25
     * 第一次参加赠送
     * @return \think\response\View
     */
    public function orderHandleFirstGive()
    {
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleFirstGive($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderHandleFirstGive',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-09-26
     * 星星灯券课程
     * @return \think\response\View
     */
    public function orderHandleStarCourse()
    {
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleStarCourse($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderHandleStarCourse',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-09-26
     * 星星灯兑换
     * @return \think\response\View
     */
    public function orderHandleStarExchange()
    {
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleStarExchange($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderHandleStarExchange',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-09-26
     * 免费体验课
     * @return \think\response\View
     */
    public function orderHandleFreeCourse()
    {
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleFreeCourse($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderHandleFreeCourse',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-09-26
     * 订单处理 顾问提醒
     * @return json
     */
    public function handleOrder()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.type')) {
            $id = input('post.id');
            $type = input('post.type');
            $orderHandleModel = new OrderHandleModel();
            $ret = $orderHandleModel->handleOrderMessage($id, $type, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Handle Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-27
     * 万人砍
     * @return \think\response\View
     */
    public function orderHandleWrkCourse()
    {
        $orderHandleModel = new OrderHandleModel();
        $data = $orderHandleModel->getOrderHandleWrkInfo($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderHandleWrkCourse',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }
}