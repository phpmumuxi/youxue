<?php
/**
 * User: LiuTong
 * Date: 2017-08-15
 * Time: 14:10
 */

namespace app\school\controller;

use app\common\controller\AdminBase;
use app\common\controller\Jpush;
use app\common\model\SchoolCommon;
use app\school\model\Course as CourseSchoolModel;

class Course extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * 课程订单列表
     * Date: 2017-08-15
     * fix Date: 2017-09-01
     * fix Content: 订单搜索
     * fix Date: 2017-09-12
     * fix Content: 手机号搜索
     * @return \think\response\View
     */
    public function courseList()
    {
        $orderNo = '';
        if (input('?get.orderNo')) {
            $orderNo = input('get.orderNo');
        }

        $phone = '';
        if (input('?get.phone')) {
            $phone = input('get.phone');
        }

        $status = -1;
        if (input('?get.status')) {
            $status = input('get.status');
        }

        $CourseAdminModel = new CourseSchoolModel();

        $adminId = $this->admin_id;
        $SchoolCommonModel = new SchoolCommon();
        $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($adminId);
        if (!$schoolId) {
            $this->error('该管理员没有指定校区!');
        }
        $pData = $CourseAdminModel->courseList($schoolId, $orderNo, $phone, $status);

        //订单状态0未付款1已付款2退款中3退款成功4退款失败5分笔支付中
//        $orderStatus = [0 => '未付款', 1 => '已付款', 2 => '退款中', 3 => '退款成功', 4 => '退款失败', 5 => '分笔支付中', 6 => '其他'];
        $orderStatus = [1 => '已付款', 4 => '退款失败'];
        $signStatus = ['否', '是'];
        $againStatus = [0 => '否', 1 => '申请中', 2 => '已同意', 3 => '已拒绝'];
        $page = $pData->render();
        $data = $pData->toArray();
        return view('courseList',
            [
                'page' => $page,
                'data' => $data,
                'orderStatus' => $orderStatus,
                'signStatus' => $signStatus,
                'orderNo' => $orderNo,
                'againStatus' => $againStatus,
                'phone' => $phone,
                'status' => $status
            ]
        );
    }

    /**
     * 校区顾问列表
     * Date: 2017-08-15
     * @return json
     */
    public function adviserList()
    {
        $adminId = $this->admin_id;
        $SchoolCommonModel = new SchoolCommon();
        $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($adminId);

        $CourseSchoolModel = new CourseSchoolModel();
        $data = $CourseSchoolModel->getSchoolAdviserList($schoolId);
        if ($data) {
            $res['status'] = 'ok';
            $res['data'] = $data;

        } else {
            $res['status'] = 'err';
            $res['msg'] = 'no data';
        }
        echo json_encode($res);
    }

    /**
     * 分配顾问
     * Date: 2017-08-16
     * @return json
     */
    public function assignAdviser()
    {
        $res['status'] = 'false';
        if (input('?post.courseOrderId') && input('?post.adviserId')) {
            $courseOrderId = input('post.courseOrderId');
            $adviserId = input('post.adviserId');
            $CourseSchoolModel = new CourseSchoolModel();
            $ret = $CourseSchoolModel->assignCourseAdviser($courseOrderId, $adviserId);
            if ($ret) {
                $phone = $CourseSchoolModel->getAdviserPhone($adviserId);
                $res['status'] = 'ok';
                $res['phone'] = $phone;

                $Push = new Jpush();
                $m_time = 86400;
                $message = '';

                $receive['alias'] = [$phone];
                $content = '您有一个新的客户!';
                $arr = [];
                $arr['info'] = '您有一个新的客户!';
                $arr['data'] = ['type' => 'assignAdviser'];
                $type = 4;
                $ret = $Push->push($receive, $content, $arr, $type, $m_time);
                $res['Jpush'] = $ret;

            } else {
                $res['msg'] = 'Assign Adviser Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 课程订单处理
     * Date: 2017-08-16
     */
    public function courseOrderHandle()
    {
        $res['status'] = 'false';
        if (input('?post.courseOrderId')) {
            $courseOrderId = input('post.courseOrderId');
            $CourseSchoolModel = new CourseSchoolModel();
            $ret = $CourseSchoolModel->handleCourseOrder($courseOrderId);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Handle CourseOrder Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 课程签约确认
     * Date: 2017-08-16
     * fix Date: 2017-09-22
     * fix Content: 签约非续费返顾问 续费返受益人
     * @return json
     */
    public function courseOrderSign()
    {
        $res['status'] = 'false';
        if (input('?post.courseOrderId')) {
            $courseOrderId = input('post.courseOrderId');
            $CourseSchoolModel = new CourseSchoolModel();
            //$ret = $CourseSchoolModel->signCourseOrder($courseOrderId, $this->admin_id);
            $ret = $CourseSchoolModel->signCourseOrderNew($courseOrderId, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Sign CourseOrder Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }
        echo json_encode($res);
    }

    /**
     * 未处理数
     * Date: 2017-08-22
     * @return json
     */
    public function countNum()
    {
        $CourseSchoolModel = new CourseSchoolModel();
        $num = $CourseSchoolModel->countNum();
        echo json_encode(['num' => $num]);
    }

    /**
     * 续费处理
     * Date: 2017-09-01
     * @return json
     */
    public function orderAgainHandle()
    {
        $res['status'] = 'false';
        $res['status'] = 'ok';
        if (input('?post.orderId') && input('?post.isAgain')) {
            $orderId = input('post.orderId');
            $isAgain = input('post.isAgain');
            $CourseSchoolModel = new CourseSchoolModel();
            $ret = $CourseSchoolModel->handleOrderAgain($orderId, $isAgain);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'handle isAgain failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-09-21
     * 校区受益人列表
     * @return json
     */
    public function schoolBenefitPersonList()
    {
        $schoolCommonModel = new SchoolCommon();
        $schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        $courseSchoolModel = new CourseSchoolModel();
        $data = $courseSchoolModel->getSchoolBenefitPersonList($schoolId);

        echo json_encode(['status' => 'ok', 'data' => $data]);
    }

    /**
     * Date: 2017-09-22
     * 分配受益人
     * @return json
     */
    public function assignBenefitPerson()
    {
        $res['status'] = 'ok';
        if (input('?post.orderId') && input('?post.bpId')) {
            $orderId = input('post.orderId');
            $bpId = input('post.bpId');
            $courseSchoolModel = new CourseSchoolModel();
            $ret = $courseSchoolModel->assignCourseBenefitPerson($orderId, $bpId);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Assign Failure';
            }
        } else {
            $res['res'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-27
     * 万人砍课程
     * @return \think\response\View
     */
    public function courseWrk()
    {
        $orderNo = '';
        if (input('?get.orderNo')) {
            $orderNo = input('get.orderNo');
        }

        $phone = '';
        if (input('?get.phone')) {
            $phone = input('get.phone');
        }

        $schoolCommonModel = new SchoolCommon();
        $schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        $CourseSchoolModel = new CourseSchoolModel();
        $data = $CourseSchoolModel->getCourseWrk($schoolId, $orderNo, $phone);

        $pages = $data->render();
        $data = $data->toArray();
        return view('courseWrkList',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * 万人砍课程签约确认
     * Date: 2017-10-30
     * @return json
     */
    public function courseOrderSignWrk()
    {
        $res['status'] = 'false';
        if (input('?post.courseOrderId')) {
            $courseOrderId = input('post.courseOrderId');
            $CourseSchoolModel = new CourseSchoolModel();
            $ret = $CourseSchoolModel->signCourseOrderWrk($courseOrderId, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Sign CourseOrder Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-02
     * 免费课
     * @return \think\response\View
     */
    public function freeCourse()
    {
        $phone = 0;
        if (input('?get.phone')) {
            $phone = input('get.phone');
        }
        $schoolCommonModel = new SchoolCommon();
        $schoolId = $schoolCommonModel->getSchoolIdFromAdminId($this->admin_id);
        $courseSchoolModel = new CourseSchoolModel();
        $data = $courseSchoolModel->getFreeCourse($schoolId, $phone);
        $pages = $data->render();
        $data = $data->toArray();
        return view('freeCourse',
            [
                'pages' => $pages,
                'data' => $data
            ]
        );
    }

    /**
     * Date: 2017-11-02
     * 免费课确认体验
     */
    public function freeCourseSure()
    {
        $res['status'] = 'false';
        $res['status'] = 'ok';
        if (input('?post.id')) {
            $orderId = input('post.id');
            $CourseSchoolModel = new CourseSchoolModel();
            $ret = $CourseSchoolModel->sureFreeCourse($orderId, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'sureFreeCourse failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }
}