<?php
/**
 * User: LiuTong
 * Date: 2017-09-29
 * Time: 10:30
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\SharePerson as SharePersonModel;

class SharePerson extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-09-29
     * 分享人
     * @return \think\response\View
     */
    public function sharePersonList()
    {
        $sharePersonModel = new SharePersonModel();
        $data = $sharePersonModel->getSharePersonList();
        $pages = $data->render();
        $data = $data->toArray();
        return view('sharePersonList',
            [
                'data' => $data,
                'pages' => $pages,
            ]
        );
    }

    /**
     * Date: 2017-09-29
     * 被分享人
     * @return \think\response\View
     */
    public function sharePersonPersonList()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $sharePersonModel = new SharePersonModel();
            $data = $sharePersonModel->getSharePersonPersonList($id);
            $pages = $data->render();
            $data = $data->toArray();
            return view('sharePersonPersonList',
                [
                    'data' => $data,
                    'pages' => $pages,
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }
}