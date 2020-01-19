<?php
/**
 * User: LiuTong
 * Date: 2017-09-07
 * Time: 10:46
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\ChildrenTip as ChildrenTipModel;
use app\common\controller\Upload;
use think\Validate;

class ChildrenTip extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * 育儿师列表
     * Date: 2017-09-07
     * fix Date: 2017-09-12
     * fix Content: 性别筛选
     * @return \think\response\View
     */
    public function childrenTipList()
    {
        $type = -1;
        if (input('?get.type') && input('get.type') != -1) {
            $type = input('get.type');
        }

        $sex = -1;
        if (input('?get.sex')) {
            $sex = input('get.sex');
        }

        $ChildrenTipModel = new ChildrenTipModel();
        $data = $ChildrenTipModel->getChildrenTip($type, $sex);
        $types = $ChildrenTipModel->getCTI();
        $pages = $data->render();
        $data = $data->toArray();

        $typeSex = [0 => '未知', 1 => '男', 2 => '女'];

        return view('childrenTipList',
            [
                'pages' => $pages,
                'info' => $data,
                'data' => $data['data'],
                'type' => $type,
                'typeSex' => $typeSex,
                'types' => $types,
                'sex' => $sex
            ]
        );
    }

    /**
     * 新增育儿师
     * Date: 2017-09-07
     * @return \think\response\View
     */
    public function childrenTipAdd()
    {
        $ChildrenTipModel = new ChildrenTipModel();
        $types = $ChildrenTipModel->getCTI();
        return view('childrenTipAdd', ['types' => $types]);
    }

    /**
     * 保存育儿师信息
     * Date: 2017-09-07
     */
    public function childrenTipSave()
    {
        $va = new Validate(
            [
                'title' => 'require',
                'sex' => 'require',
                'type' => 'require',
                'content' => 'require'
            ]
        );

        $data['title'] = input('post.title');
        $data['sex'] = input('post.sex');
        $data['type'] = input('post.type');
        $data['content'] = input('post.content');

        if ($va->check($data)) {
            $ChildrenTipModel = new ChildrenTipModel();
            $ret = $ChildrenTipModel->saveChildrenTip($data);
            if ($ret) {
                $this->success('操作成功!', '/admin/ChildrenTip/childrenTipList');
            } else {
                $this->error('操作失败!');
            }
        }

        $this->error('操作异常!');
    }

    /**
     * 获取育儿师内容
     * Date: 2017-09-07
     * @return json
     */
    public function childrenTipContent()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $ChildrenTipModel = new ChildrenTipModel();
            $ret = $ChildrenTipModel->getChildrenTipContent($id);
            if ($ret) {
                $res['status'] = 'ok';
                $res['data'] = htmlspecialchars_decode($ret);
            } else {
                $res['msg'] = 'No Content';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 育儿师编辑
     * Date: 2017-09-07
     * @return \think\response\View
     */
    public function childrenTipEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $ChildrenTipModel = new ChildrenTipModel();
            $data = $ChildrenTipModel->getChildrenTipAllContent($id);

            $types = $ChildrenTipModel->getCTI();
            return view('childrenTipEdit', ['data' => $data, 'types' => $types]);
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * 育儿师更新保存
     * Date: 2017-09-07
     */
    public function childrenTipUpdate()
    {
        $va = new Validate(
            [
                'title' => 'require',
                'sex' => 'require',
                'type' => 'require',
                'content' => 'require'
            ]
        );

        $data['title'] = input('post.title');
        $data['sex'] = input('post.sex');
        $data['type'] = input('post.type');
        $data['content'] = input('post.content','','htmlspecialchars');

        if ($va->check($data) && input('?post.id')) {
            $id = input('post.id');

            $ChildrenTipModel = new ChildrenTipModel();
            $ret = $ChildrenTipModel->updateChildrenTip($id, $data);
            if ($ret) {
                $this->success('操作成功!', '/admin/ChildrenTip/childrenTipList');
            } else {
                $this->error('操作失败!');
            }
        }

        $this->error('操作异常!');
    }

    /**
     * 删除育儿师
     * Date: 2017-09-07
     * @return json
     */
    public function childrenTipDelete()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $ChildrenTipModel = new ChildrenTipModel();
            $ret = $ChildrenTipModel->deleteChildrenTip($id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Delete Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-10-11
     * 育儿师类型图片列表
     * @return \think\response\View
     */
    public function childrenTipTypeImage()
    {
        $childrenTipModel = new ChildrenTipModel();
        $data = $childrenTipModel->getChildrenTipTypeImage();
        return view('CTTIList', ['data' => $data]);
    }

    /**
     * Date: 2017-10-11
     * 新增
     * @return \think\response\View
     */
    public function childrenTipTypeImageAdd()
    {
        return view('CTTIAdd');
    }

    /**
     * Date: 2017-10-11
     * 新增保存
     */
    public function CTTISave()
    {
        if (input('?post.typeName') && $_FILES['img']['name'] && input('?post.divideType')) {
            $upload = new Upload();
            $data['img'] = $upload->saveIamge('img');
            $data['typeName'] = input('post.typeName');
            $data['divideType'] = input('post.divideType');
            $childrenTipModel = new ChildrenTipModel();
            $ret = $childrenTipModel->saveCTTI($data);
            if ($ret) {
                $this->success('操作成功!', '/admin/ChildrenTip/childrenTipTypeImage');
            } else {
                $this->error('操作失败!');
            }
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-10-11
     * 修改
     * @return \think\response\View
     */
    public function childrenTipTypeImageEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $childrenTipModel = new ChildrenTipModel();
            $data = $childrenTipModel->getCTTI($id);
            return view('CTTIEdit', ['data' => $data]);
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-10-11
     * 修改保存
     */
    public function CTTIUpdate()
    {
        if (input('?get.id') && input('?post.typeName')) {
            $id = input('get.id');
            if ($_FILES['img']['name']) {
                $upload = new Upload();
                $data['img'] = $upload->saveIamge('img');
            }
            $data['typeName'] = input('post.typeName');
            $data['divideType'] = input('post.divideType');
            $childrenTipModel = new ChildrenTipModel();
            $ret = $childrenTipModel->updateCTTI($id, $data, $this->admin_id);
            if ($ret) {
                $this->success('操作成功!', '/admin/ChildrenTip/childrenTipTypeImage');
            } else {
                $this->error('操作失败!');
            }
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-10-11
     * 删除
     */
    public function childrenTipTypeImageDelete()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $childrenTipModel = new ChildrenTipModel();
            $ret = $childrenTipModel->deleteTypeImage($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Delete Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

}