<?php
/**
 * User: LiuTong
 * Date: 2017-09-28
 * Time: 11:39
 */

namespace app\school\controller;

use app\common\controller\AdminBase;
use app\common\model\SchoolCommon;
use \app\school\model\StarEvent as StarEventModel;

class StarEvent extends AdminBase
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
     * Date: 2017-09-28
     * 星星活动会员首次赠送课程
     * @return \think\response\View
     */
    public function starOrderGive()
    {
        $starEventModel = new StarEventModel();
        $data = $starEventModel->getStarOrderGive($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('starOrderGive', ['data' => $data, 'pages' => $pages]);
    }

    /**
     * Date: 2017-09-28
     * 星星灯券课程
     * @return \think\response\View
     */
    public function starOrderTicket()
    {
        $starEventModel = new StarEventModel();
        $data = $starEventModel->getStarOrderTicket($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('starOrderTicket', ['data' => $data, 'pages' => $pages]);
    }

    /**
     * Date: 2017-09-28
     * 星星兑换课程
     * @return \think\response\View
     */
    public function starOrderExchange()
    {
        $starEventModel = new StarEventModel();
        $data = $starEventModel->getStarOrderExchange($this->schoolId);
        $pages = $data->render();
        $data = $data->toArray();
        return view('starOrderExchange', ['data' => $data, 'pages' => $pages]);
    }

    /**
     * Date: 2017-09-28
     * 确认赠送券已使用
     * @return json
     */
    public function starOrderGiveSure()
    {
        $res['status'] = 'ok';
        if (input('?post.id')) {
            $id = input('post.id');
            $starEventModel = new StarEventModel();
            $ret = $starEventModel->makeGiveSure($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Make Sure Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-09-28
     * 确认星星灯赠送券已使用
     * @return json
     */
    public function starOrderTicketSure()
    {
        $res['status'] = 'ok';
        if (input('?post.id')) {
            $id = input('post.id');
            $starEventModel = new StarEventModel();
            $ret = $starEventModel->makeTicketSure($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Make Sure Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-09-28
     * 确认星星兑换已上课
     * @return json
     */
    public function starOrderExchangeSure()
    {
        $res['status'] = 'ok';
        if (input('?post.id')) {
            $id = input('post.id');
            $starEventModel = new StarEventModel();
            $ret = $starEventModel->makeExchangeSure($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Make Sure Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

}