<?php
/**
 * User: LiuTong
 * Date: 2017-11-02
 * Time: 13:06
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\School as SchoolAdminModel;

class School extends AdminBase
{
    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-11-02
     * 校区列表
     * @return \think\response\View
     */
    public function schoolList()
    {

        $shopId = -1;
        if (input('?get.shopId')) {
            $shopId = input('get.shopId');
        }

        $schoolName = '';
        if (input('?get.schoolName')) {
            $schoolName = input('get.schoolName');
        }

        $schoolAdminModel = new SchoolAdminModel();
        $data = $schoolAdminModel->getSchoolList($shopId,$schoolName);
        $shopLists = $schoolAdminModel->shopLists();
        $pages = $data->render();
        $data = $data->toArray();
        return view('schoolList',
            [
                'data' => $data,
                'pages' => $pages,
                'shopLists' => $shopLists,
                'shopId' => $shopId,
                'schoolName' => $schoolName
            ]
        );
    }

    /**
     * Date: 2017-11-02
     * 校区上架
     */
    public function schoolUp()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $schoolAdminModel = new SchoolAdminModel();
            $ifHaveCourse = $schoolAdminModel->ifSchoolHaveCourse($id);
            if ($ifHaveCourse) {
                $ret = $schoolAdminModel->upSchool($id, $this->admin_id);
                if ($ret) {
                    $res['status'] = 'ok';
                } else {
                    $res['msg'] = 'Up School Failure';
                }
            } else {
                $res['status'] = 'errHaveCourse';
                $res['msg'] = 'No Course';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-02
     * 校区下架
     */
    public function schoolDown()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $schoolAdminModel = new SchoolAdminModel();
            $ret = $schoolAdminModel->downSchool($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Down School Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }
}