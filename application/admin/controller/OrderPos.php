<?php
/**
 * User: LiuTong
 * Date: 2017-10-11
 * Time: 15:29
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\OrderPos as OrderPosModel;

class OrderPos extends AdminBase
{
    /**
     * Date: 2017-10-11
     * pos机订单列表
     * @return \think\response\View
     */
    public function orderPosList()
    {
        $posOrderId = 0;
        if (input('?get.posId')) {
            $posOrderId = input('get.posId');
        }
        $orderPosModel = new OrderPosModel();
        $data = $orderPosModel->getOrderPosList($posOrderId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('orderPosList',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * Date: 2017-10-11
     * 启用
     */
    public function orderPosStart()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $orderPosModel = new OrderPosModel();
            $ret = $orderPosModel->startOrderPos($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-11
     * 作废
     */
    public function orderPosEnd()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $orderPosModel = new OrderPosModel();
            $ret = $orderPosModel->endOrderPos($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-11
     * 新增作废pos机单号
     */
    public function orderPosSave()
    {
        $res['status'] = 'false';
        if (input('?post.posId')) {
            $data['posOrderId'] = input('post.posId');
            $data['adminId'] = $this->admin_id;
            $orderPosModel = new OrderPosModel();
            $ret = $orderPosModel->saveOrderPos($data);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Save Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }
}