<?php
/**
 * User: LiuTong
 * Date: 2017-07-25
 * Time: 16:54
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Course as CourseAdminModel;
use app\common\model\CourseCommon;
use think\Validate;

class Course extends AdminBase
{

    public function index()
    {
        return true;
    }

    /**
     * 课程类别列表
     * Date: 2017-07-25
     * @return \think\response\View
     */
    public function courseTypeList()
    {
        $CourseAdminModel = new CourseAdminModel();
        $data = $CourseAdminModel->getCourseTypeList();
        return view('courseTypeList', ['data' => $data]);
    }

    /**
     * 课程新增
     * Date: 2017-07-25
     * @return \think\response\View
     */
    public function courseTypeAdd()
    {
        $Course = new CourseCommon();
        $data = $Course->CourseTypesLevel1();
        return view('courseTypeAdd', ['data' => $data]);
    }

    /**
     * 课程新增保存
     * Date: 2017-07-25
     */
    public function courseTypeAddSave()
    {
        try {
            $data['name'] = input('post.name');
            $levelOne = input('post.levelOne');
            if ($levelOne == 0) {
                $data['typeId'] = 0;
                $data['level'] = 1;
            } else {
                $data['typeId'] = $levelOne;
                $data['level'] = 2;
            }
        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        $CourseAdminModel = new CourseAdminModel();
        $res = $CourseAdminModel->saveCourseType($data);
        if ($res) {
            $this->error('操作成功!', '/admin/Course/courseTypeList', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * 课程类别修改
     * Date: 2017-07-26
     * @return json
     */
    public function courseTypeEdit()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.name')) {
            $id = input('post.id');
            $name = input('post.name');

            $CourseAdminModel = new CourseAdminModel();
            $ret = $CourseAdminModel->saveCourseTypeEdit($id, $name, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'update failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 课程类别删除
     * Date: 2017-07-26
     * @return json
     */
    public function courseTypeDel()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.level')) {
            $id = input('post.id');
            $level = input('post.level');

            $CourseAdminModel = new CourseAdminModel();
            if ($level == 1) {
                $ret = $CourseAdminModel->ifHaveSubType($id);
                if ($ret) {
                    $res['status'] = 'errHave';
                    $res['msg'] = 'already have sub type';
                    echo json_encode($res);
                    die;
                }
            }

            $retDel = $CourseAdminModel->delCourseType($id, $this->admin_id);
            if ($retDel) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'delete failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 课程审核
     * Date: 2017-07-26
     * fix Date: 2017-08-31
     * fix Content: 分页
     * @return \think\response\View
     */
    public function courseVerify()
    {
        $schoolId = -1;
        if (input('?get.schoolId')) {
            $schoolId = input('get.schoolId');
        }
        $status = -1;
        if (input('?get.status')) {
            $status = input('get.status');
        }
        $CourseAdminModel = new CourseAdminModel();
        $data = $CourseAdminModel->courseVerifyList($status,$schoolId);
        $schoolLists = $CourseAdminModel->schoolList();

        $pages = $data->render();
        $data = $data->toArray();

        $verifyStatus = [0 => '未审核', 2 => '审核通过', 4 => '拒绝'];
        return view('courseVerifyList',
            [
                'info' => $data,
                'data' => $data['data'],
                'pages' => $pages,
                'verifyStatus' => $verifyStatus,
                'status' => $status,
                'schoolLists' => $schoolLists,
                'schoolId'=>$schoolId
            ]
        );
    }

    /**
     * 课程审核拒绝
     * Date: 2017-07-26
     * @return json
     */
    public function courseRefuse()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');

            $adminId = $this->admin_id;
            $CourseAdminModel = new CourseAdminModel();
            $ret = $CourseAdminModel->refuseCourse($id, $adminId);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'refuse failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 查询课程分成返现
     * Date: 2017-07-26
     * @return \think\response\View
     */
    public function courseRule()
    {
        if (input('?get.id')) {
            $classSchoolId = input('get.id');

            $CourseAdminModel = new CourseAdminModel();
            $ruleData = $CourseAdminModel->getCourseRuleNew($classSchoolId);
            $courseData = $CourseAdminModel->getCourseDetail($classSchoolId);

            $template = 'courseRuleNew';
            if ($courseData['isOldCustom'] == 1) {
                $template = 'oldCourseRuleNew';
            }
            return view($template, ['ruleData' => $ruleData, 'courseData' => $courseData]);
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * 课程规则保存
     * Date: 2017-07-26
     * fix Date: 2017-08-31
     * fix Content: 课程续费金额
     */
    public function courseRuleSave()
    {
        $id = 0;
        if (input('?get.id')) {
            $id = input('get.id');
        }
        try {
            $data['name'] = input('post.name');
            $data['classSchoolId'] = input('post.classSchoolId');
            $data['money'] = input('post.money');
            $data['againMoney'] = input('post.againMoney');
            // 普通会员
            $data['lZero'] = input('post.lZero');
            $data['lOne'] = input('post.lOne');
            $data['lTwo'] = input('post.lTwo');
            $data['lThree'] = input('post.lThree');
            $data['lFour'] = input('post.lFour');
            // 顾问
            $data['adviser'] = input('post.adviser');
            // 受益人
            $data['benefitMoney'] = input('post.benefitMoney');
            // 推荐人
            $data['referrer'] = input('post.referrer');
            // 商户结算价格
            $data['shopOne'] = input('post.shopOne');
            // 商户续费结算价格
            $data['shopTwo'] = input('post.shopTwo');
            $data['adminId'] = $this->admin_id;

        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        $CourseAdminModel = new CourseAdminModel();
        $ret = $CourseAdminModel->saveCourseRule($data, $id, $this->admin_id);
        if ($ret) {
            $this->error('操作成功!', '/admin/Course/courseVerify', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * Date: 2017-11-10
     * 课程审核规则
     */
    public function courseRuleSaveNew()
    {
        $validate = new Validate(
            [
                'classSchoolId' => 'require',
                'againMoneyTwo' => 'require',
                'shopMoneyOne' => 'require',
                'shopMoneyTwo' => 'require',
                'benefitMoneyOne' => 'require',
                'benefitMoneyTwo' => 'require',
                'referrerMoneyOne' => 'require',
                'referrerMoneyTwo' => 'require',
                'lZeroOne' => 'require',
                'lZeroTwo' => 'require',
                'lOneOne' => 'require',
                'lOneTwo' => 'require',
                'lTwoOne' => 'require',
                'lTwoTwo' => 'require'
            ]
        );

        $data['classSchoolId'] = input('post.classSchoolId');
        $data['againMoneyTwo'] = input('post.againMoneyTwo');
        $data['shopMoneyOne'] = input('post.shopMoneyOne');
        $data['shopMoneyTwo'] = input('post.shopMoneyTwo');
        $data['benefitMoneyOne'] = input('post.benefitMoneyOne');
        $data['benefitMoneyTwo'] = input('post.benefitMoneyTwo');
        $data['referrerMoneyOne'] = input('post.referrerMoneyOne');
        $data['referrerMoneyTwo'] = input('post.referrerMoneyTwo');
        $data['lZeroOne'] = input('post.lZeroOne');
        $data['lZeroTwo'] = input('post.lZeroTwo');
        $data['lOneOne'] = input('post.lOneOne');
        $data['lOneTwo'] = input('post.lOneTwo');
        $data['lTwoOne'] = input('post.lTwoOne');
        $data['lTwoTwo'] = input('post.lTwoTwo');

        if ($validate->check($data)) {
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->saveCourseRuleNew($data, $this->admin_id);
            if ($ret) {
                $this->success('操作成功!', '/admin/Course/courseVerify');
            } else {
                $this->error('操作失败!');
            }
        } else {
            //dump($validate->getError());die;
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-11-10
     * 老用户续费课程审核规则
     */
    public function oldCourseRuleSaveNew()
    {
        $validate = new Validate(
            [
                'classSchoolId' => 'require',
                'shopMoney' => 'require',
                'benefitMoney' => 'require',
                'referrerMoney' => 'require',
                'lZero' => 'require',
                'lOne' => 'require',
                'lTwo' => 'require',
            ]
        );

        $data['classSchoolId'] = input('post.classSchoolId');
        $data['shopMoney'] = input('post.shopMoney');
        $data['benefitMoney'] = input('post.benefitMoney');
        $data['referrerMoney'] = input('post.referrerMoney');
        $data['lZero'] = input('post.lZero');
        $data['lOne'] = input('post.lOne');
        $data['lTwo'] = input('post.lTwo');

        if ($validate->check($data)) {
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->saveOldCourseRuleNew($data, $this->admin_id);
            if ($ret) {
                $this->success('操作成功!', '/admin/Course/courseVerify');
            } else {
                $this->error('操作失败!');
            }
        } else {
            //dump($validate->getError());die;
            $this->error('操作异常!');
        }
    }

    /**
     * 课程订单列表
     * Date: 2017-08-10
     * fix Date: 2017-08-31
     * fix Content: 分页
     * fix Date: 2017-09-13
     * fix Content: 状态筛选、手机号搜索
     * @return \think\response\View
     */
    public function courseOrder()
    {
        $orderNo = '';
        if (input('?get.orderNo')) {
            $orderNo = input('get.orderNo');
        }

        $status = -1;
        if (input('?get.status')) {
            $status = input('get.status');
        }

        $phone = 0;
        if (input('?get.phone')) {
            $phone = input('get.phone');
        }
        $CourseAdminModel = new CourseAdminModel();
        $pData = $CourseAdminModel->courseOrderList($orderNo, $status, $phone);

        //订单状态0未付款1已付款2退款中3退款成功4退款失败5分笔支付中
        $orderStatus = [0 => '未付款', 1 => '已付款', 2 => '退款中', 3 => '退款成功', 4 => '退款失败', 5 => '分笔支付中', 6 => '其他'];
        $signStatus = [0 => '否', 1 => '是'];

        $page = $pData->render();
        $data = $pData->toArray();
        return view('courseOrderList',
            [
                'page' => $page,
                'data' => $data,
                'orderStatus' => $orderStatus,
                'signStatus' => $signStatus,
                'orderNo' => $orderNo,
                'status' => $status,
                'phone' => $phone
            ]
        );
    }

    /**
     * Date: 2017-09-26
     * 星星课兑换课程
     * @return \think\response\View
     */
    public function starCourse()
    {
        $courseAdminModel = new CourseAdminModel();
        $data = $courseAdminModel->getStarCourse();
        $pages = $data->render();
        $data = $data->toArray();
        return view('starCourse',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-09-26
     * 修改兑换星星数量
     * @return json
     */
    public function starCourseChange()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.starNum') && input('?post.exchangeNum')) {
            $id = input('post.id');
            $data['starNum'] = input('post.starNum');
            $data['exchangeNum'] = input('post.exchangeNum');
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->changeStarCourse($id, $data, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Change Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-09-27
     * 去除星星课程
     * @return json
     */
    public function removeStarCourse()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->starCourseRemove($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Remove Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-09-27
     * 设置课程
     * @return json
     */
    public function starCourseAdd()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.starNum') && input('?post.exchangeNum') && input('?post.isStar')) {
            $id = input('post.id');
            $data['starNum'] = input('post.starNum');
            $data['exchangeNum'] = input('post.exchangeNum');
            $data['isStar'] = input('post.isStar');
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->addStarCourse($id, $data, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Add Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-24
     * 获取当前课程星星数
     */
    public function getCourseStar()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseAdminModel = new CourseAdminModel();
            $data = $courseAdminModel->courseStarGet($id);
            if ($data) {
                $res['status'] = 'ok';
                $res['data'] = $data;
            } else {
                $res['msg'] = 'No Data';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * 万人砍课程审核
     * Date: 2017-10-30
     * @return \think\response\View
     */
    public function courseVerifyWrk()
    {
        $status = -1;
        if (input('?get.status')) {
            $status = input('get.status');
        }
        $CourseAdminModel = new CourseAdminModel();
        $data = $CourseAdminModel->courseVerifyListWrk($status);

        $pages = $data->render();
        $data = $data->toArray();

        $verifyStatus = [0 => '未审核', 1 => '审核中', 2 => '审核通过', 3 => '下架', 4 => '拒绝'];
        return view('courseVerifyListWrk',
            [
                'info' => $data,
                'data' => $data['data'],
                'pages' => $pages,
                'verifyStatus' => $verifyStatus,
                'status' => $status
            ]
        );
    }

    /**
     * Date: 2017-10-30
     * 获取当前万人砍课程星星数
     */
    public function getCourseStarWrk()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseAdminModel = new CourseAdminModel();
            $data = $courseAdminModel->courseStarGetWrk($id);
            if ($data) {
                $res['status'] = 'ok';
                $res['data'] = $data;
            } else {
                $res['msg'] = 'No Data';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-30
     * 设置课程
     * @return json
     */
    public function starCourseAddWrk()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.starNum') && input('?post.isStar')) {
            $id = input('post.id');
            $data['starNum'] = input('post.starNum');
            $data['isStar'] = input('post.isStar');
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->addStarCourseWrk($id, $data, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Add Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * 万人砍课程审核拒绝
     * Date: 2017-10-31
     * @return json
     */
    public function courseRefuseWrk()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');

            $adminId = $this->admin_id;
            $CourseAdminModel = new CourseAdminModel();
            $ret = $CourseAdminModel->refuseCourseWrk($id, $adminId);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'refuse failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-10-31
     * 万人砍课程金额
     * @return \think\response\View
     */
    public function courseRuleWrk()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $courseAdminModel = new CourseAdminModel();
            $data = $courseAdminModel->getCourseRuleWrk($id);
            $data['miniMoney'] = $data['money'] - $data['shopMoney'] - $data['adviserMoney'] - $data['referrerMoney'];
            return view('courseRuleWrk', ['data' => $data]);
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-10-31
     * 审核规则
     */
    public function courseRuleSaveWrk()
    {
        if (input('?post.classId') && input('?post.shopMoney') && input('?post.adviserMoney') && input('?post.referrerMoney')) {
            $id = input('post.classId');
            $data['shopMoney'] = input('post.shopMoney');
            $data['adviserMoney'] = input('post.adviserMoney');
            $data['referrerMoney'] = input('post.referrerMoney');
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->saveCourseRuleWrk($id, $this->admin_id, $data);
            if ($ret) {
                $this->success('操作成功!', '/admin/Course/courseVerifyWrk');
            } else {
                $this->error('操作失败!');
            }
        } else {
            $this->error("操作异常!");
        }
    }

    /**
     * Date: 2017-10-31
     * 万人砍课程订单
     * @return \think\response\View
     */
    public function courseOrderWrk()
    {
        $orderNo = 0;
        if (input('?get.orderNo')) {
            $orderNo = input('get.orderNo');
        }

        $status = 0;
        if (input('?get.status')) {
            $status = input('get.status');
        }

        $phone = 0;
        if (input('?get.phone')) {
            $phone = input('get.phone');
        }

        $courseAdminModel = new CourseAdminModel();
        $data = $courseAdminModel->getCourseOrderWrk($orderNo, $status, $phone);
        $pages = $data->render();
        $data = $data->toArray();
        $orderStatus = [1 => '已支付', 2 => '退款中', 3 => '退款成功', 4 => '退款失败', 5 => '分笔支付中'];
        return view('courseOrderListWrk',
            [
                'data' => $data,
                'pages' => $pages,
                'orderStatus' => $orderStatus,
                'status' => $status,
                'phone' => $phone,
            ]
        );
    }

    /**
     * Date: 2017-10-31
     * 万人砍课程是否有已签约
     */
    public function courseOrderInfoWrkCheck()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->courseBackMoneyStatusWrk($id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Wrk Course Had Signed';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-31
     * 万人砍课程详情
     */
    public function courseOrderInfoWrk()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseAdminModel = new CourseAdminModel();
            $data = $courseAdminModel->getCourseOrderDetailWrk($id);
            $res['status'] = 'ok';
            $res['data'] = $data;
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-06
     * 获取退款内容
     */
    public function getCourseBackMoney()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseAdminModel = new CourseAdminModel();
            $data = $courseAdminModel->courseBackMoneyGet($id);
            $res['status'] = 'ok';
            $res['data'] = $data;
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-06
     * 退款申请
     */
    public function courseBackApply()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('post.backMoney') && input('post.backReason')) {
            $orderId = input('post.id');
            $data['actualMoney'] = input('post.backMoney');
            $data['reason'] = input('post.backReason');
            $data['adminId'] = $this->admin_id;
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->applyCourseBackMoney($orderId, $data);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Apply Back Money Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-06
     * 万人砍获取退款内容
     */
    public function getCourseBackMoneyWrk()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseAdminModel = new CourseAdminModel();
            $data = $courseAdminModel->courseBackMoneyGetWrk($id);
            $res['status'] = 'ok';
            $res['data'] = $data;
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-06
     * 万人砍退款申请
     */
    public function courseBackApplyWrk()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('post.backMoney') && input('post.backReason')) {
            $orderId = input('post.id');
            $data['actualMoney'] = input('post.backMoney');
            $data['reason'] = input('post.backReason');
            $data['adminId'] = $this->admin_id;
            $courseAdminModel = new CourseAdminModel();
            $ret = $courseAdminModel->applyCourseBackMoneyWrk($orderId, $data);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Apply Back Money Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }
}