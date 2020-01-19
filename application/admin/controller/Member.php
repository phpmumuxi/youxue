<?php
/**
 * User: LiuTong
 * Date: 2017-08-23
 * Time: 13:34
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Member as MemberModel;
use app\common\model\Order as OrderCommonModel;

class Member extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * 会员列表
     * Date: 2017-08-23
     * @return \think\response\View
     */
    public function memberList()
    {
        if (input('?get.phone')) {
            $phone = input('get.phone');
        } else {
            $phone = '';
        }
        $MemberModel = new MemberModel();
        $data = $MemberModel->getMemberList($phone);
        $pages = $data->render();
        $data = $data->toArray();
        $ifNot = [0 => '否', 1 => '是', 2 => '申请失败'];
        $mLevel = [0 => '普通会员', 1 => '钻石VIP会员', 2 => '皇冠VIP会员'];
        return view('memberList',
            [
                'info' => $data,
                'data' => $data['data'],
                'pages' => $pages,
                'ifNot' => $ifNot,
                'mLevel' => $mLevel,
                'phone' => $phone
            ]
        );
    }

    /**
     * 会员详细信息
     * Date: 2017-08-23
     * @return \think\response\View
     */
    public function memberDetail()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $MemberModel = new MemberModel();
            $data = $MemberModel->getMemberInfo($id);
            $ifNot = [0 => '否', 1 => '是', 2 => '申请失败'];
            $mLevel = [0 => '普通会员', 1 => '钻石VIP会员', 2 => '皇冠VIP会员'];
            $sex = [0 => '未知', 1 => '男', 2 => '女'];
            return view('memberDetail',
                [
                    'data' => $data,
                    'ifNot' => $ifNot,
                    'mLevel' => $mLevel,
                    'sex' => $sex
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * 会员个人账单详细
     * Date: 2017-08-24
     * @return \think\response\View
     */
    public function memberMoney()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            if (input('?get.orderNo')) {
                $orderNo = input('get.orderNo');
            } else {
                $orderNo = '';
            }

            $MemberModel = new MemberModel();
            $data = $MemberModel->getMemberMoneyRecord($id, $orderNo);
            $type = [
                1 => '团购',
                2 => '课程',
                3 => '购买会员',
                4 => '万人砍',
                5 => '返现',
                6 => '提现',
                7 => '会员奖励',
                8 => '推荐人收入',
                9 => '顾问奖励',
                10 => '再次购买返现',
                11 => '开会员返现',
                12 => '提现失败',
                13 => '退款',
                14 => '课程受益人奖励'
            ];
            $pages = $data->render();
            $data = $data->toArray();
            return view('memberMoney',
                [
                    'info' => $data,
                    'data' => $data['data'],
                    'type' => $type,
                    'pages' => $pages,
                    'orderNo' => $orderNo
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * 会员课程订单列表
     * Date: 2017-08-28
     * @return \think\response\View
     */
    public function memberCourseOrder()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            if (input('?get.orderNo')) {
                $orderNo = input('get.orderNo');
            } else {
                $orderNo = '';
            }

            $MemberModel = new MemberModel();
            $data = $MemberModel->getMemberCourseOrder($id, $orderNo);
            $status = [
                0 => '未付款',
                1 => '已付款',
                2 => '退款中',
                3 => '退款成功',
                4 => '退款失败',
                5 => '分笔支付中'
            ];
            $signStatus = [0 => '否', 1 => '是'];
            $pages = $data->render();
            $data = $data->toArray();
            return view('memberCourseOrder',
                [
                    'info' => $data,
                    'data' => $data['data'],
                    'status' => $status,
                    'pages' => $pages,
                    'orderNo' => $orderNo,
                    'signStatus' => $signStatus
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * 会员订单详情
     * Date: 2017-08-28
     * @return \think\response\View
     */
    public function memberCourseOrderDetail()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $MemberModel = new MemberModel();
            $data = $MemberModel->getMemberCourseOrderDetail($id);
            $status = [
                0 => '未付款',
                1 => '已付款',
                2 => '退款中',
                3 => '退款成功',
                4 => '退款失败',
                5 => '分笔支付中'
            ];
            $payType = [
                1 => '余额',
                2 => '支付宝',
                3 => '银行卡',
                4 => '微信'
            ];
            $signStatus = [0 => '否', 1 => '是'];
            $againStatus = [0 => '否', 1 => '是'];
            $payStatus = [0 => '未支付', 1 => '已支付'];

            return view('memberCourseOrderDetail',
                [
                    'data' => $data,
                    'status' => $status,
                    'signStatus' => $signStatus,
                    'payType' => $payType,
                    'payStatus' => $payStatus,
                    'againStatus' => $againStatus
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * 获取订单退款金额
     * Date: 2017-08-29
     * @return json
     */
    public function getBackMoney()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $OrderCommonModel = new OrderCommonModel();
            $ret = $OrderCommonModel->getOrderBackMoney($id);
            if ($ret) {
                $res['status'] = 'ok';
                $res['data'] = $ret;
            } else {
                $res['msg'] = 'No Data';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 退款申请
     * Date: 2017-08-29
     * @return json
     */
    public function payBackMoney()
    {
        $res['status'] = 'false';
        if (input('?post.id') && input('?post.realBackMoney') && input('?post.backInfo')) {
            $orderId = input('post.id');
            $realBackMoney = input('?post.realBackMoney');
            $backInfo = input('?post.backInfo');

            $OrderCommonModel = new OrderCommonModel();
            $adminId = $this->admin_id;
            $ret = $OrderCommonModel->payBackMoney($orderId, $realBackMoney, $backInfo, $adminId);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'pay back false';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 团购订单列表
     * Date: 2017-08-29
     * @return \think\response\View
     */
    public function memberGroupBuy()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            if (input('?get.orderNo')) {
                $orderNo = input('get.orderNo');
            } else {
                $orderNo = '';
            }

            $MemberModel = new MemberModel();
            $data = $MemberModel->getMemberGroupBuyCourseOrder($id, $orderNo);

            $disposeStatus = [0 => '否', 1 => '是'];
            $payStatus = [0 => '未支付', 1 => '已支付未使用', 2 => '已使用'];

            $pages = $data->render();
            $data = $data->toArray();
            return view('memberGroupBuyOrder',
                [
                    'info' => $data,
                    'data' => $data['data'],
                    'pages' => $pages,
                    'disposeStatus' => $disposeStatus,
                    'payStatus' => $payStatus,
                    'orderNo' => $orderNo
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Vip免费体验课
     * Date: 2017-08-31
     * @return \think\response\View
     */
    public function memberVipFreeCourse()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $MemberModel = new MemberModel();
            $data = $MemberModel->getMemberVipFreeCourse($id);

            $status = [0 => '未使用', 1 => '已使用'];

            $pages = $data->render();
            $data = $data->toArray();
            return view('memberVipFreeCourse',
                [
                    'info' => $data,
                    'data' => $data['data'],
                    'pages' => $pages,
                    'status' => $status
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

}