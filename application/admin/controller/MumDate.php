<?php
/**
 * User: LiuTong
 * Date: 2017-09-04
 * Time: 11:29
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;

use app\admin\model\MumDate as MumDateModel;

class MumDate extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * 妈妈约活动列表
     * Date: 2017-09-04
     * @return \think\response\View
     */
    public function mumDateList()
    {
        $phone = input('get.phone');
        $MumDateModel = new MumDateModel();
        $data = $MumDateModel->getMumDateList($phone);
        $pages = $data->render();
        $data = $data->toArray();

        $status = [0 => '报名中', 1 => '活动中', 2 => '活动结束', 3 => '活动取消'];
        return view('mumDateList',
            [
                'pages' => $pages,
                'info' => $data,
                'status' => $status,
                'data' => $data['data'],
                'phone' => $phone
            ]
        );
    }

    /**
     * Date: 2017-09-04
     * 更新人数上限
     * @return json
     */
    public function peopleNumEdit()
    {
        if (input('?post.id') && input('?post.num')) {
            $id = input('post.id');
            $num = input('post.num');
            $MumDateModel = new MumDateModel();
            $ret = $MumDateModel->editPeopleNum($id, $num);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'edit failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-09-04
     * 删除妈妈约活动
     * @return json
     */
    public function mumDateDel()
    {
        if (input('?post.id') && input('?post.note')) {
            $id = input('post.id');
            $note = input('post.note');
            $MumDateModel = new MumDateModel();
            $ret = $MumDateModel->delPeopleNum($id, $note);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'edit failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 活动详情
     * Date: 2017-09-05
     * @return \think\response\View
     */
    public function mumDateDetail()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $MumDateModel = new MumDateModel();
            $data = $MumDateModel->getMumDateDetail($id);

            $status = [0 => '报名中', 1 => '活动中', 2 => '活动结束', 3 => '活动取消'];
            $supportType = [0 => '不赞助', 1 => '人', 2 => '物', 3 => '钱', 4 => '不限'];
            return view('mumDateDetail',
                [
                    'data' => $data,
                    'status' => $status,
                    'supportType' => $supportType
                ]
            );
        } else {
            $this->success('操作异常!');
        }
    }

    /**
     * 妈妈约赞助统计
     * Date: 2017-09-06
     * @return \think\response\View
     */
    public function mumDateSupport()
    {
        $MumDateModel = new MumDateModel();

        $timeA = '';
        $timeB = '';
        if (input('?get.aTime') && input('?get.bTime')) {
            $aTime = input('get.aTime');
            $timeA = strtotime($aTime);

            $bTime = input('get.bTime');
            $timeB = strtotime($bTime);
        } else {
            $sevenDay = $MumDateModel->sevenDay();
            $aTime = $sevenDay['aTime'];
            $aTime = date('Y-m-d', $aTime);

            $bTime = $sevenDay['bTime'];
            $bTime = date('Y-m-d', $bTime);
        }

        $data = $MumDateModel->getMumDateSupport($timeA, $timeB);

        $pages = $data->render();
        $data = $data->toArray();

        return view('mumDateSupport',
            [
                'info' => $data,
                'data' => $data['data'],
                'pages' => $pages,
                'aTime' => $aTime,
                'bTime' => $bTime,
                'cTime' => date('Y-m-d')
            ]
        );
    }

}