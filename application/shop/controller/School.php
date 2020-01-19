<?php
/**
 * User: LiuTong
 * Date: 2017-07-17
 * Time: 9:35
 */

namespace app\shop\controller;

use app\common\controller\AdminBase;
use app\common\controller\Upload;
use app\common\model\Area;
use app\shop\model\School as SchoolModel;

class School extends AdminBase
{

    public function index()
    {
        return true;
    }

    /**
     * 校区列表
     * Date: 2017-07-17
     * @return \think\response\View
     */
    public function schoolList()
    {
        $admin_id = $this->admin_id;
        $admin_type = $this->admin_type;

        $school = new SchoolModel();
        $data = $school->getShopSchools($admin_id);
        $pages = $data->render();
        $data = $data->toArray();
        return view('schoolList',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * 校区删除
     * Date: 2017-07-17
     * @return json
     */
    public function schoolDel()
    {
        if (input('?post.id')) {
            $id = input('post.id');

            $school = new SchoolModel();
//            $ret = $school->delSchool($id);
            // 注: 删除前先下架 下架时已减去商户和品牌课程数
            $ret = $school->delSchoolNew($id, $this->admin_id);
            if ($ret) {
                echo json_encode(['status' => 'ok', 'msg' => '操作成功!']);
            } else {
                echo json_encode(['status' => 'false', 'msg' => '操作失败!']);
            }
        } else {
            echo json_encode(['status' => 'false', 'msg' => '操作失败!']);
        }
    }

    /**
     * 校区编辑
     * Date: 2017-07-17
     * @return bool|\think\response\View
     */
    public function schoolEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            if (!is_numeric($id)) {
                return false;
            }

            $school = new SchoolModel();
            $data = $school->schoolDetail($id);

            $area = new Area();
            $citiesOpened = $area->cityOpen();

            return view('schoolEdit', ['data' => $data, 'citiesOpened' => $citiesOpened]);
        } else {
            $this->error('错误请求!', null, '', 2);
        }
    }

    /**
     *  校区修改保存
     *  Date: 2017-07-19
     */
    public function schoolEditSave()
    {
        try {
            $data['name'] = input('post.schoolName');
            $data['address'] = input('post.address');
            $data['longitude'] = input('post.longitude');
            $data['latitude'] = input('post.latitude');
            $data['userName'] = input('post.userName');
            $data['phone'] = input('post.phone');

            $upload = new Upload();
            if (isset($_FILES['logo']['name']) && !empty($_FILES['logo']['name'])) {
                $data['logo'] = $upload->saveIamge('logo');
            }
            if (isset($_FILES['img']['name']) && !empty($_FILES['img']['name'])) {
                $data['img'] = $upload->saveIamge('img');
            }

            $data['cityCode'] = input('post.cityCode');
            $data['cityName'] = input('post.cityName');

            $data['intr'] = input('post.content', '', 'htmlspecialchars');

            $schoolId = input('get.id');

        } catch (\Exception $e) {
            $this->error('操作失败!', null, '', 2);
        }

        $school = new SchoolModel();
        $ret = $school->saveEditSchool($data, $schoolId);

        if ($ret) {
            $this->success('操作成功!', '/shop/School/schoolList', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * 校区新增
     * Date: 2017-07-19
     * @return \think\response\View
     */
    public function schoolAdd()
    {
        $area = new Area();
        $citiesOpened = $area->cityOpen();

        return view('schoolAdd', ['citiesOpened' => $citiesOpened]);
    }

    /**
     *  校区新增保存
     *  Date: 2017-07-19
     */
    public function schoolAddSave()
    {
        try {
            $admin_id = $this->admin_id;

            $data['name'] = input('post.schoolName');
            $data['address'] = input('post.address');
            $data['longitude'] = input('post.longitude');
            $data['latitude'] = input('post.latitude');
            $data['userName'] = input('post.userName');
            $data['phone'] = input('post.phone');

            $upload = new Upload();
            if (isset($_FILES['logo']['name']) && !empty($_FILES['logo']['name'])) {
                $data['logo'] = $upload->saveIamge('logo');
            }
            if (isset($_FILES['img']['name']) && !empty($_FILES['img']['name'])) {
                $data['img'] = $upload->saveIamge('img');
            }

            $data['cityCode'] = input('post.cityCode');
            $data['cityName'] = input('post.cityName');

            $data['intr'] = input('post.content', '', 'htmlspecialchars');

        } catch (\Exception $e) {
            $this->error('操作失败!', null, '', 2);
        }

        $school = new SchoolModel();
        $ret = $school->saveAddSchool($data, $admin_id);
        if ($ret) {
            $this->success('操作成功!', '/shop/School/schoolList', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }

    }

    /**
     * 校区上下架
     * Date: 2017-07-20
     */
    public function changeSchoolStatus()
    {
        $res = ['status' => 'false'];
        if (input('?post.id') || input('?post.status')) {
            $schoolId = input('post.id');
            $status = input('post.status');

            $school = new SchoolModel();

            // 上架
            if ($status == 1) {
                $ret = $school->checkSchoolIfHaveClass($schoolId);
                if (!$ret) {
                    $res['status'] = 'err';
                    $res['msg'] = ['school no courses'];
                } else {
                    $ret = $school->upSchool($schoolId, $this->admin_id);
                    if ($ret) {
                        $res['status'] = 'ok';
                    }
                }
            } else {
                // 下架
//                $retStatus = $school->updateSchoolStatus($schoolId, $status);
                $retStatus = $school->downSchool($schoolId, $this->admin_id);
                if ($retStatus) {
                    $res['status'] = 'ok';
                    $res['msg'] = ['update status success!'];
                } else {
                    $res['msg'] = ['update status failure'];
                }
            }
        } else {
            $res['msg'] = ['param missing'];
        }

        echo json_encode($res);
    }

    /**
     *  省的市或直辖市的区
     *  Date: 2017-07-18
     */
    public function searchCity()
    {
        if (input('?post.provinceId')) {
            $provinceId = input('post.provinceId');

            $area = new Area();
            $cities = $area->city($provinceId);
            echo json_encode(['status' => 'ok', 'msg' => '100', 'data' => $cities]);
        } else {
            echo json_encode(['status' => 'false', 'msg' => '操作失败!']);
        }
    }

    /**
     *  市的区或县
     *  Date: 2017-07-18
     */
    public function searchCounty()
    {
        if (input('?post.cityId')) {
            $cityId = input('post.cityId');

            $area = new Area();
            $counties = $area->county($cityId);
            echo json_encode(['status' => 'ok', 'msg' => '100', 'data' => $counties]);
        } else {
            echo json_encode(['status' => 'false', 'msg' => '操作失败!']);
        }
    }

}