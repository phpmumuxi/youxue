<?php
/**
 * User: LiuTong
 * Date: 2017-07-20
 * Time: 11:26
 */

namespace app\shop\controller;

use app\common\controller\AdminBase;
use app\common\controller\Upload;
use app\common\model\CourseCommon;
use app\common\model\ShopCommon;
use app\shop\model\Course as CourseModel;
use think\Validate;

class Course extends AdminBase
{

    public function index()
    {
        return true;
    }

    /**
     * 课程列表
     * Date: 2017-07-20
     * @return \think\response\View
     */
    public function courseList()
    {
        $adminId = $this->admin_id;

        $course = new CourseModel();
        $data = $course->getCourseList($adminId);

        $pages = $data->render();
        $data = $data->toArray();

        return view('courseList',
            [
                'data' => $data,
                'pages' => $pages
            ]
        );
    }

    /**
     * 新增课程模板
     * Date: 2017-07-20
     * @return \think\response\View
     */
    public function courseAdd()
    {
        $courseCommon = new CourseCommon();
        $typeLevel1 = $courseCommon->CourseTypesLevel1();

        return view('courseAdd', ['typeLevel1' => $typeLevel1]);
    }

    /**
     * 新增课程保存
     * Date: 2017-07-21
     */
    public function courseAddSave()
    {
        $ret = false;
        try {
            $data['name'] = input('post.name');
            $data['classTime'] = input('post.classTime');
            $data['money'] = input('post.money');
            $data['typeId'] = input('post.typelevel2');

            $upload = new Upload();
            $fileName = $upload->saveIamge('img');
            $data['img'] = $fileName;
            $data['content'] = input('post.content', '', 'htmlspecialchars');

            $adminId = $this->admin_id;

            $course = new CourseModel();
            $ret = $course->shopCourseAdd($adminId, $data);

        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        if ($ret) {
//            $this->success('操作成功!', '/shop/Course/courseList', '', 2);
            $this->redirect('/shop/Course/courseAssignSchool?id=' . $ret);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * 修改课程模板
     * Date: 2017-07-21
     * @return \think\response\View
     */
    public function courseEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $course = new CourseModel();
            $data = $course->courseDetail($id);

            $courseCommon = new CourseCommon();
            $typeLevel1 = $courseCommon->CourseTypesLevel1();
            $realTypeLevel1Id = $courseCommon->courseTypeLevel1FromLevel2($data['typeId']);
            $data['realTypeLevel1Id'] = $realTypeLevel1Id;
            $typeLevel2 = $courseCommon->CourseTypesLevel2($realTypeLevel1Id);

            return view('courseEdit', ['typeLevel1' => $typeLevel1, 'typeLevel2' => $typeLevel2, 'data' => $data]);
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * 修改课程保存
     * Date: 2017-07-21
     */
    public function courseEditSave()
    {
        $ret = false;
        try {

            $classId = input('get.id');
            $data['name'] = input('post.name');
            $data['classTime'] = input('post.classTime');
            $data['money'] = input('post.money');
            $data['typeId'] = input('post.typelevel2');

            if (isset($_FILES['img']['name']) && !empty($_FILES['img']['name'])) {
                $upload = new Upload();
                $fileName = $upload->saveIamge('img');
                $data['img'] = $fileName;
            }

            $data['content'] = input('post.content', '', 'htmlspecialchars');

            $course = new CourseModel();
            $ret = $course->shopCourseEdit($classId, $data);

        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        if ($ret) {
            $this->success('操作成功!', '/shop/Course/courseList', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * 课程详情
     * Date: 2017-07-21
     * @return \think\response\View
     */
    public function courseDetail()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $course = new CourseModel();
            $data = $course->courseDetail($id);

            return view('courseDetail', ['data' => $data]);
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * 模板课程校区使用列表
     * Date: 2017-07-21
     * @return \think\response\View
     */
    public function courseSchoolList()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $course = new CourseModel();
            $data = $course->getCourseSchoolList($id);

            $pages = $data->render();
            $data = $data->toArray();
            $types = [0 => '未审核', 1 => '审核中', 2 => '上架', 3 => '下架', 4 => '拒绝'];
            return view('courseSchoolList',
                [
                    'data' => $data,
                    'pages' => $pages,
                    'types' => $types,
                ]
            );
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * 模板课程分配校区
     * Date: 2017-07-21
     * @return \think\response\View
     */
    public function courseAssignSchool()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $course = new CourseModel();
            $courseTemplate = $course->courseDetail($id);

            $adminId = $this->admin_id;
            $ShopCommon = new ShopCommon();
            $shopId = $ShopCommon->getShopIdFromAdminId($adminId);
            $schoolList = $ShopCommon->schoolListFromShopId($shopId);

            return view('courseAssignSchool', ['courseTemplate' => $courseTemplate, 'schoolList' => $schoolList]);
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * 校区课程保存
     * Date: 2017-07-24
     */
    public function courseSchoolAddSave()
    {
        $ret = false;
        try {
            $data['classId'] = input('post.classId');
            $data['typeId'] = input('post.typeId');
            $data['schoolId'] = input('post.schoolId');
            $data['classTime'] = input('post.classTime');

            $data['name'] = input('post.name');
            $data['money'] = input('post.money');
            $data['startAge'] = input('post.startAge');
            $data['endAge'] = input('post.endAge');
            $adminId = $this->admin_id;

            $upload = new Upload();
            $data['listImg'] = $upload->saveIamge('listImg');
            $data['topImg'] = $upload->saveIamge('topImg');

            $data['isOldCustom'] = input('post.isOldCustom');
            $data['content'] = input('post.content', '', 'htmlspecialchars');

            $CourseModel = new CourseModel();
            $ret = $CourseModel->schoolCourseSave($data, $adminId);

        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        if ($ret) {
//            $this->success('操作成功!', '/shop/Course/courseList', '', 2);
            $this->success('操作成功!', '/shop/Course/courseSchoolList?id=' . $data['classId'], '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * 查询二级分类
     * Date: 2017-07-20
     * @return json
     */
    public function courseTypeLevel2()
    {
        $res['status'] = 'false';
        if (input('?post.typeId')) {
            $typeId = input('post.typeId');

            $courseCommon = new CourseCommon();
            $data = $courseCommon->CourseTypesLevel2($typeId);

            $res['status'] = 'ok';
            $res['data'] = $data;
        } else {
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 删除课程模板
     * Date: 2017-07-20
     * @return json
     */
    public function courseDel()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');

            $course = new CourseModel();
            $retStatus = $course->schoolIfStillUseThisCourse($id);
            if ($retStatus) {
                $ret = $course->shopCourseDel($id);
                if ($ret) {
                    $res['status'] = 'ok';
                    $res['msg'] = 'delete course template success';
                }
            } else {
                $res['status'] = 'err';
                $res['msg'] = 'this course template,one of schools still use';
            }
        } else {
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 课程详情
     * Date: 2017-10-24
     * @return \think\response\View
     */
    public function schoolCourseDetail()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $course = new CourseModel();
            $data = $course->getSchoolCourseDetail($id);
            return view('schoolCourseDetail', ['data' => $data]);
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * Date: 2017-11-01
     * 课程下架
     */
    public function schoolCourseDown()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseModel = new CourseModel();
            $ret = $courseModel->downSchoolCourse($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Down Course Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-01
     * 课程删除
     */
    public function schoolCourseDelete()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseModel = new CourseModel();
            $ret = $courseModel->deleteSchoolCourse($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Down Course Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-11-01
     * 校区课程编辑
     * @return \think\response\View
     */
    public function schoolCourseEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');

            $course = new CourseModel();
            $data = $course->getSchoolCourseDetail($id);
            $ShopCommon = new ShopCommon();
            $shopId = $ShopCommon->getShopIdFromAdminId($this->admin_id);
            $schoolList = $ShopCommon->schoolListFromShopId($shopId);
            return view('courseAssignSchoolEdit',
                [
                    'data' => $data,
                    'schoolList' => $schoolList
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-11-09
     * 校区课程修改保存
     */
    public function courseSchoolEditSave()
    {
        $validate = new Validate(
            [
                'name' => 'require',
                'startAge' => 'require',
                'endAge' => 'require',
                'schoolId' => 'require',
                'classId' => 'require',
                'content' => 'require',
                'classTime' => 'require',
                'isOldCustom' => 'require',
            ]
        );

        $data['classId'] = input('post.classId');
        $data['name'] = input('post.name');
        $data['startAge'] = input('post.startAge');
        $data['classTime'] = input('post.classTime');
        $data['endAge'] = input('post.endAge');
        $data['schoolId'] = input('post.schoolId');
        $data['isOldCustom'] = input('post.isOldCustom');
        $data['content'] = input('post.content', '', 'htmlspecialchars');

        $upload = new Upload();
        if ($_FILES['listImg']['name']) {
            $data['listImg'] = $upload->saveIamge('listImg');
        }
        if ($_FILES['topImg']['name']) {
            $data['topImg'] = $upload->saveIamge('topImg');
        }
        if ($validate->check($data)) {
            $classId = $data['classId'];
            unset($data['classId']);
            $courseModel = new CourseModel();
            $ret = $courseModel->saveCourseSchoolEdit($data, $classId, $this->admin_id);
            if ($ret) {
                $shopClassId = input('post.shopClassId');
                $this->success('操作成功!', '/shop/Course/courseSchoolList?id=' . $shopClassId);
            } else {
                $this->error('操作失败!');
            }
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-11-09
     * 校区课程重新提交上架审核
     */
    public function schoolCourseUp()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $courseModel = new CourseModel();
            $ret = $courseModel->reUpSchoolCourse($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'reUp School Course Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

}