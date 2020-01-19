<?php
/**
 * User: LiuTong
 * Date: 2017-08-18
 * Time: 14:09
 */

namespace app\school\controller;

use app\common\controller\AdminBase;

use app\common\controller\Jpush;
use app\common\model\SchoolCommon;
use app\school\model\GroupBuy as GroupBuyModel;

class GroupBuy extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * 团购订单
     * Date: 2017-08-18
     * fix Date: 2017-09-01
     * fixContent: 订单搜索 状态筛选
     * @return \think\response\View
     */
    public function groupBuyOrderList()
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

        $GroupBuyModel = new GroupBuyModel();
        $data = $GroupBuyModel->getGroupBuyOrderList($orderNo, $phone, $status);
        $pages = $data->render();
        $data = $data->toArray();
//        $orderStatus = [0 => '未付款', 1 => '未使用', 2 => '已使用'];
        $orderStatus = [1 => '未使用', 2 => '已使用'];
        $disposeStatus = [0 => '否', 1 => '是'];
        if ($data['data']) {
            foreach ($data['data'] as $k => $v) {
                $r = date('Y-m-d H:i:s', $v['term']);
                $r2 = date('Y-m-d H:i:s', strtotime($r . "+30 day"));
                if ($r2 > time()) {
                    $r3 = 1;
                    $r4 = '是';
                } else {
                    $r3 = 2;
                    $r4 = '否';
                }
                $data['data'][$k]['ifOver'] = $r3;
                $data['data'][$k]['ifOverStatus'] = $r4;
            }
        }
        return view('groupbuyorderlist',
            [
                'pages' => $pages,
                'data' => $data,
                'orderStatus' => $orderStatus,
                'disposeStatus' => $disposeStatus,
                'orderNo' => $orderNo,
                'phone' => $phone,
                'status' => $status
            ]
        );
    }

    /**
     *  订单处理
     * Date: 2017-08-18
     * @return json
     */
    public function orderHandle()
    {
        $res['status'] = 'false';
        if (input('?post.orderId')) {
            $orderId = input('post.orderId');
            $GroupBuyModel = new GroupBuyModel();
            $ret = $GroupBuyModel->handleOrder($orderId);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Handle Order Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'missing param';
        }
        echo json_encode($res);
    }

    /**
     * 校区顾问列表
     * Date: 2017-08-21
     * @return json
     */
    public function adviserList()
    {
        $adminId = $this->admin_id;
        $SchoolCommonModel = new SchoolCommon();
        $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($adminId);

        $GroupBuyModel = new GroupBuyModel();
        $data = $GroupBuyModel->getSchoolAdviserList($schoolId);
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
     * Date: 2017-08-21
     * @return json
     */
    public function assignAdviser()
    {
        $res['status'] = 'false';
        if (input('?post.orderId') && input('?post.adviserId')) {
            $orderId = input('post.orderId');
            $adviserId = input('post.adviserId');
            $GroupBuyModel = new GroupBuyModel();
            $ret = $GroupBuyModel->assignGroupBuyAdviser($orderId, $adviserId);
            if ($ret) {
                $res['status'] = 'ok';

                $phone = $GroupBuyModel->getAdviserPhone($adviserId);
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
     * 未处理数
     * Date: 2017-08-22
     * @return json
     */
    public function countNum()
    {
        $GroupBuyModel = new GroupBuyModel();
        $num = $GroupBuyModel->countNum();
        echo json_encode(['num' => $num]);
    }

}