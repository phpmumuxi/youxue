<?php
/**
 * User: LiuTong
 * Date: 2017-08-29
 * Time: 16:57
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\PayBack as PackBackModel;

class PayBack extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * 退款列表
     * Date: 2017-08-30
     * @return \think\response\View
     */
    public function payBackList()
    {
        $orderNo = '';
        if (input('?get.orderNo')) {
            $orderNo = input('get.orderNo');
        }
        $PackBackModel = new PackBackModel();
        $data = $PackBackModel->getPayBackList($orderNo);
        $pages = $data->render();
        $data = $data->toArray();

        return view('payBackList', ['info' => $data, 'data' => $data['data'], 'pages' => $pages, 'orderNo' => $orderNo]);
    }

    /**
     * 退款
     * Date: 2017-08-30
     * @return json
     */
    public function payBackReview()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $PackBackModel = new PackBackModel();
            $adminId = $this->admin_id;
            $ret = $PackBackModel->updatePayBack($id, $adminId);
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
     * 退款申请拒绝
     */
    public function payBackReviewCancel()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $PackBackModel = new PackBackModel();
            $adminId = $this->admin_id;
            $ret = $PackBackModel->cancelPayBack($id, $adminId);
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
}