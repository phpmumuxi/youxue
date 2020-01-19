<?php
/**
 * User: LiuTong
 * Date: 2017/11/14
 * Time: 14:20
 */

namespace app\school\controller;

use app\common\controller\AdminBase;
use app\common\model\SchoolCommon;
use app\school\model\WrkActivityMoney as WrkActivityMoneyModel;

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
        $schoolCommon = new SchoolCommon();
        $schoolId = $schoolCommon->getSchoolIdFromAdminId($this->admin_id);
        $wrkActivityMoneyModel = new WrkActivityMoneyModel();
        $data = $wrkActivityMoneyModel->getWrkActivityOrderList($schoolId);
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
}