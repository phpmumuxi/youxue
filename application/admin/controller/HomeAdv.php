<?php
/**
 * User: LiuTong
 * Date: 2017-07-25
 * Time: 9:49
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\HomeAdv as HomeAdvModel;
use app\common\controller\Upload;

class HomeAdv extends AdminBase
{

    public function index()
    {
        return true;
    }

    /**
     * 轮播图列表
     * Date: 2017-07-25
     * @return \think\response\View
     */
    public function AdvList()
    {
        $HomeAdvModel = new HomeAdvModel();
        $data = $HomeAdvModel->getAdvList();
        return view('advList', ['data' => $data]);
    }

    /**
     * 轮播图详情
     * Date: 2017-07-25
     * @return json
     */
    public function AdvDetail()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $HomeAdvModel = new HomeAdvModel();
            $data = $HomeAdvModel->getAdvDetail($id);

            $res['status'] = 'ok';
            $res['data'] = $data['content'];
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 轮播图新增
     * Date: 2017-07-25
     * @return \think\response\View
     */
    public function AdvAdd()
    {
        //类型1普通广告(h5)2女王3福利4优选品牌5万人砍6团购7vip
        $type = [1 => '普通广告', 2 => '女王权杖', 3 => '福利专区', 4 => '优选品牌', 5 => '万人砍', 6 => '团购', 7 => 'VIP', 8 => '星星点灯'];
        $homeAdvModel = new HomeAdvModel();
        $data = $homeAdvModel->advHave();
        if ($data) {
            foreach ($data as $k => $v) {
                unset($type[$v['type']]);
            }
        }
        return view('advAdd', ['type' => $type]);
    }

    /**
     * 轮播图新增保存
     * Date: 2017-07-25
     */
    public function AdvAddSave()
    {
        $res = false;
        try {
            $upload = new Upload();
            $data['img'] = $upload->saveIamge('img');
            $data['type'] = input('post.type');
            $data['sort'] = input('post.sort');
            if ($data['type'] == 1) {
                $data['content'] = input('post.content', '', 'htmlspecialchars');
            }

            $HomeAdvModel = new HomeAdvModel();
            $res = $HomeAdvModel->saveAdv($data);
        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        if ($res) {
            $this->success('操作成功!', '/admin/HomeAdv/AdvList/', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }
    }

    /**
     * 轮播图修改
     * Date: 2017-07-25
     * @return \think\response\View
     */
    public function AdvEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $HomeAdvModel = new HomeAdvModel();
            $data = $HomeAdvModel->getAdvDetail($id);

            $type = [1 => '普通广告', 2 => '女王权杖', 3 => '福利专区', 4 => '优选品牌', 5 => '万人砍', 6 => '团购', 7 => 'VIP', 8 => '星星点灯'];
            return view('advEdit', ['data' => $data, 'type' => $type]);
        } else {
            $this->error('操作异常!', null, '', 2);
        }
    }

    /**
     * 轮播图修改保存
     * Date: 2017-07-25
     */
    public function AdvEditSave()
    {
        $res = false;
        try {
            $id = input('get.id');

            $data['type'] = input('post.type');
            if (isset($_FILES['img']['name']) && !empty($_FILES['img']['name'])) {
                $upload = new Upload();
                $data['img'] = $upload->saveIamge('img');
            }

            $data['sort'] = input('post.sort');

            if ($data['type'] == 1) {
                $data['content'] = input('post.content');
            }

            $HomeAdvModel = new HomeAdvModel();
            $res = $HomeAdvModel->saveEidtAdv($data, $id);
        } catch (\Exception $e) {
            $this->error('操作异常!', null, '', 2);
        }

        if ($res) {
            $this->success('操作成功!', '/admin/HomeAdv/AdvList', '', 2);
        } else {
            $this->error('操作失败!', null, '', 2);
        }

    }

    /**
     * 广告删除
     * Date: 2017-07-25
     * @return json
     */
    public function AdvDel()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');

            $HomeAdvModel = new HomeAdvModel();
            $ret = $HomeAdvModel->delAdv($id);
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