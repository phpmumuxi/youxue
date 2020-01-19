<?php
/**
 * User: LiuTong
 * Date: 2017/11/15
 * Time: 10:57
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\WrkActivityMoney as WrkActivityMoneyModel;

class WrkActivityMoney extends AdminBase
{
    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-11-14
     * 万人砍活动订单列表
     * @return \think\response\View
     */
    public function wrkList()
    {
        $wrkActivityMoneyModel = new WrkActivityMoneyModel();
        $data = $wrkActivityMoneyModel->getWrkActivityOrderList();
        $pages = $data->render();
        $data = $data->toArray();
        return view('wrkOrderList',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-11-14
     * 分配业绩顾问
     */
    public function assignAdviser()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $wrkActivityMoneyModel = new WrkActivityMoneyModel();
            $retAdviserId = $wrkActivityMoneyModel->getWrkOrderAdviserId($id);
            if (!$retAdviserId) {
                $retInfo = $wrkActivityMoneyModel->getWrkOrderInfoAdviserId($id);
                if ($retInfo) {
                    $ret = $wrkActivityMoneyModel->adviserAssign($id, $this->admin_id);
                    if ($ret) {
                        $res['status'] = 'ok';
                    } else {
                        $res['msg'] = 'Done Failure';
                    }
                } else {
                    $res['status'] = 'errAdviser';
                    $res['msg'] = 'wrk Info No Adviser';
                }
            } else {
                $res['status'] = 'errHad';
                $res['msg'] = 'Adviser Had';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-15
     * 获取万人砍子单信息
     */
    public function getWrkOrderInfo()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $wrkActivityMoneyModel = new WrkActivityMoneyModel();
            $data = $wrkActivityMoneyModel->wrkOrderInfoGet($id);
            if (empty($data)) {
                $res['msg'] = 'No Data';
            } else {
                $res['status'] = 'ok';
                $res['data'] = $data;
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-11-15
     * 重新分配业绩顾问
     */
    public function reAssignAdviser()
    {
        $res['status'] = 'false';
        if (input('?post.wrkInfoId') && input('?post.wrkOrderId')) {
            $wrkInfoId = input('post.wrkInfoId');
            $wrkOrderId = input('post.wrkOrderId');
            $wrkActivityMoneyModel = new WrkActivityMoneyModel();
            $ret = $wrkActivityMoneyModel->adviserReAssign($wrkOrderId, $wrkInfoId, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Do Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }

        echo json_encode($res);
    }
}