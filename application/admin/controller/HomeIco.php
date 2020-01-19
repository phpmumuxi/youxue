<?php
/**
 * User: LiuTong
 * Date: 2017-07-27
 * Time: 14:09
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\HomeIco as HomeIcoModel;
use app\common\controller\Upload;

class HomeIco extends AdminBase
{
    public function index()
    {
        return true;
    }

    /**
     * 图标列表
     * Date: 2017-07-27
     * @return \think\response\View
     */
    public function icoList()
    {
        $HomeIcoModel = new HomeIcoModel();
        $data = $HomeIcoModel->getHomeIcoList();
        return view('icoList', ['data' => $data]);
    }

    /**
     * 图标新增
     * Date: 2017-07-27
     * @return \think\response\View
     */
    public function icoAdd()
    {
        $type = [1 => '品牌课程', 2 => '福利专区', 3 => '妈妈约', 4 => '团购', 5 => '万人砍', 6 => 'Vip', 7 => '女王权杖', 8 => '顾问入口', 9 => '星星点灯'];
        return view('icoAdd', ['type' => $type]);
    }

    /**
     * 图标修改
     * Date: 2017-07-27
     * @return \think\response\View
     */
    public function icoEdit()
    {
        if (input('?get.id')) {
            $type = [1 => '品牌课程', 2 => '福利专区', 3 => '妈妈约', 4 => '团购', 5 => '万人砍', 6 => 'Vip', 7 => '女王权杖', 8 => '顾问入口'];
            $id = input('get.id');
            $HomeIcoModel = new HomeIcoModel();
            $data = $HomeIcoModel->getIcoDetail($id);

            return view('icoEdit', ['type' => $type, 'data' => $data]);
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * 图标新增保存
     * Date: 2017-07-27
     */
    public function icoSave()
    {
        try {
            $data['name'] = input('post.name');
            $data['type'] = input('post.type');
            $data['sort'] = input('post.sort');
            $upload = new Upload();
            $data['img'] = $upload->saveIamge('img');
        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        $HomeIcoModel = new  HomeIcoModel();
        $ret = $HomeIcoModel->saveIco($data);
        if ($ret) {
            $this->success('操作成功!', '/admin/HomeIco/icoList', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * 图标修改保存
     * Date: 2017-07-27
     */
    public function icoEditSave()
    {

        if (input('?get.id') && input('?post.name') && input('?post.type') && input('?post.sort')) {
            $id = input('get.id');
            $data['name'] = input('post.name');
            $data['type'] = input('post.type');
            $data['sort'] = input('post.sort');
            if (isset($_FILES['img']['name']) && !empty($_FILES['img']['name'])) {
                $upload = new Upload();
                $rr = $upload->saveIamge('img');
                if (is_array($rr)) {
                    $this->error($rr['msg']);
                }
                $data['img'] = $rr;
            }

            $HomeIcoModel = new  HomeIcoModel();
            $ret = $HomeIcoModel->saveIcoEdit($data, $id);
            if ($ret) {
                $this->success('操作成功!', '/admin/HomeIco/icoList', '', 2);
            } else {
                $this->error('操作失败!', null, '', 2);
            }
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * 图标启用
     * Date: 2017-07-27
     */
    public function icoStart()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');

            $HomeIcoModel = new HomeIcoModel();
            $ret = $HomeIcoModel->ifIcoHave($id);
            if ($ret) {
                $res['status'] = 'errHave';
            } else {
                $ret = $HomeIcoModel->icoStart($id);
                if ($ret) {
                    $res['status'] = 'ok';
                } else {
                    $res['msg'] = 'start failure';
                }
            }
        } else {
            $res['msg'] = 'param missing';
        }
        echo json_encode($res);
    }

    /**
     * 图标停用
     * Date: 2017-07-27
     */
    public function icoStop()
    {
        $res['status'] = 'false';

        if (input('?post.id')) {
            $id = input('post.id');

            $HomeIcoModel = new HomeIcoModel();
            $ret = $HomeIcoModel->icoStop($id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'stop failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }
        echo json_encode($res);
    }

    /**
     * 图标删除
     * Date: 2017-07-27
     */
    public function icoDel()
    {
        $res['status'] = 'false';

        if (input('?post.id')) {
            $id = input('post.id');

            $HomeIcoModel = new HomeIcoModel();
            $ret = $HomeIcoModel->icoDel($id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'delete failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }
        echo json_encode($res);
    }

}