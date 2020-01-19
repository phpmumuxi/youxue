<?php
/**
 * User: LiuTong
 * Date: 2017-08-22
 * Time: 14:13
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\Vip as VipModel;
use app\common\controller\Upload;
use app\common\model\OperateLog;

class Vip extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * VIP等级列表
     * Date: 2017-08-22
     * @return \think\response\View
     */
    public function vipList()
    {
        $VipModel = new VipModel();
        $data = $VipModel->VipList();
        $vipLevelOne = $VipModel->vipLevelOne();
        $level = [0 => '普通会员', 1 => '钻石VIP会员', 2 => '皇冠VIP会员'];
        $useStatus = [0 => '未使用', 1 => '使用中'];
        return view('list', ['data' => $data, 'vipLevelOne' => $vipLevelOne, 'level' => $level, 'useStatus' => $useStatus]);
    }

    /**
     * 修改一级会员 免费领取权限
     * Data: 2017-08-22
     * @return json
     */
    public function changeFree()
    {
        $res['status'] = 'false';
        if (input('?post.isFree')) {
            $isFree = input('post.isFree');
            $VipModel = new VipModel();
            $ret = $VipModel->changeLevelOneFree($isFree);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Change isFree Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'missing param';
        }
        echo json_encode($res);
    }

    /**
     * 更改使用状态
     * Date: 2017-08-22
     * @return json
     */
    public function changeUseStatus()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $status = input('post.status');
            $VipModel = new VipModel();
            $ret = $VipModel->changeLevelUseStatus($id, $status);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Change Use Status Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'missing param';
        }
        echo json_encode($res);
    }

    /**
     * 删除VIP等级
     * Date: 2017-08-22
     * @return json
     */
    public function VipLevelDel()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $VipModel = new VipModel();
            $ret = $VipModel->deleteVipLevel($id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Delete Level Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'missing param';
        }
        echo json_encode($res);
    }

    /**
     * vip新增
     * Date: 2017-08-23
     * @return \think\response\View
     */
    public function vipAdd()
    {
        $vipModel = new VipModel();
        $data = $vipModel->vipRightsSelect();

        $level = [1 => '钻石VIP会员', 2 => '皇冠VIP会员'];
        return view('vipAdd', ['level' => $level, 'data' => $data]);
    }

    /**
     * vip保存
     * Date: 2017-08-23
     */
    public function vipSave()
    {
        $data = $this->request->post();
        $result = $this->validate($data,
            [
                'level' => 'require|number|gt:0',
                'title' => 'require',
                'money' => 'require|number|gt:0',
                'mMoney' => 'require|number|gt:0',
                'month' => 'require|number|gt:0',
                'prerogative' => 'require'
            ]);
        if (true !== $result) {
            $this->error("操作异常!");
        }

        if ($_FILES['img']['name']) {
            $upload = new Upload();
            $path = $upload->saveIamge('img');
            $data['img'] = $path;
        } else {
            $this->error("图片未上传!");
        }

        $data['adminId'] = $this->admin_id;
        $VipModel = new VipModel();
        $ret = $VipModel->saveVip($data);
        if ($ret) {
            $this->success('操作成功!', '/admin/Vip/vipList');
        } else {
            $this->error("操作失败!");
        }
    }

    /**
     * vip编辑
     * Date: 2017-08-23
     * @return \think\response\View
     */
    public function vipEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $vipModel = new VipModel();
            $data = $vipModel->levelInfo($id);
            $pData = $vipModel->vipRightsSelect();
            $data['prerogative'] = explode(',', $data['prerogative']);
            return view('vipEdit', ['data' => $data, 'pData' => $pData]);
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * vip编辑保存
     * Date: 2017-08-23
     */
    public function vipEditSave()
    {
        if (!input('?get.id')) {
            $this->error("操作异常!");
        }
        $id = input('get.id');

        $data = $this->request->post();
        $result = $this->validate($data,
            [
                'title' => 'require',
                'money' => 'require|number|gt:0',
                'mMoney' => 'require|number|gt:0',
                'month' => 'require|number|gt:0',
                'prerogative' => 'require'
            ]);
        if (true !== $result) {
            $this->error("操作异常!");
        }

        $data['prerogative'] = implode(',', $data['prerogative']);
        if ($_FILES['img']['name']) {
            $upload = new Upload();
            $path = $upload->saveIamge('img');
            $data['img'] = $path;
        }

        $data['adminId'] = $this->admin_id;
        $VipModel = new VipModel();
        $ret = $VipModel->saveEditVip($data, $id);
        if ($ret) {
            $this->success('操作成功!', '/admin/Vip/vipList');
        } else {
            $this->error("操作失败!");
        }
    }

    /**
     * 会员购买列表
     * Date: 2017-09-12
     * @return \think\response\View
     */
    public function vipBuyList()
    {
        $orderNo = '';
        if (input('?get.orderNo')) {
            $orderNo = input('get.orderNo');
        }
        $phone = 0;
        if (input('?get.phone')) {
            $phone = input('get.phone');
        }

        $sort = [];
        if (input('?get.value') && input('?get.sort')) {
            $sort[0] = input('get.value');
            $sort[1] = input('get.sort');
        }

        $orderStatus = -1;
        if (input('?get.orderStatus')) {
            $orderStatus = input('get.orderStatus');
        }
        $VipModel = new VipModel();
        $data = $VipModel->getVipBuyList($orderNo, $phone, $sort, $orderStatus);
        $pages = $data->render();
        $data = $data->toArray();

        $payType = [0 => '未付款', 1 => '余额', 2 => '支付宝', 3 => '银行卡', 4 => '微信'];
        $orderStatus = [0 => '未付款', 1 => '已付款', 2 => '退款中', 3 => '退款成功', 4 => '退款失败'];
        $orderType = [1 => '购买', 2 => '赠送'];


        return view('vipBuyList',
            [
                'pages' => $pages,
                'info' => $data,
                'data' => $data['data'],
                'payType' => $payType,
                'orderStatus' => $orderStatus,
                'orderType' => $orderType
            ]
        );
    }

    /**
     * Date: 2017-09-20
     * VIP权益说明
     * @return \think\response\View
     */
    public function vipRights()
    {
        $vipModel = new VipModel();
        $data = $vipModel->getVipRights();
        return view('vipRights', ['data' => $data]);
    }

    /**
     * Date: 2017-09-20
     * 新增权益说明
     * @return \think\response\View
     */
    public function vipRightsAdd()
    {
        return view('vipRightsAdd');
    }

    /**
     * Date: 2017-09-20
     * 权益新增保存
     */
    public function vipRightsSave()
    {
        $data = $this->request->post();
        $result = $this->validate($data,
            [
                'name' => 'require',
                'text' => 'require'
            ]);
        if (true !== $result) {
            $this->error("操作异常!");
        }

        if ($_FILES['img']['name']) {
            $upload = new Upload();
            $data['vipIco'] = $upload->saveIamge('img');
        } else {
            $this->error('请上传图片!');
        }

        $vipModel = new VipModel();
        $ret = $vipModel->saveVipRights($data);
        if (!$ret) {
            $this->error('操作失败!');
        }
        $this->success('操作成功!', '/admin/Vip/vipRights');
    }

    /**
     * Date: 2017-09-20
     * 修改权益说明
     * @return \think\response\View
     */
    public function vipRightsEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $vipModel = new VipModel();
            $data = $vipModel->getVipRightsDetail($id);
            return view('vipRightsEdit', ['data' => $data]);
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-09-20
     * 权益编辑保存
     */
    public function vipRightsEditSave()
    {
        if (input('?get.id')) {
            $id = input('get.id');
        } else {
            $this->error('操作异常!');
        }
        $data = $this->request->post();
        $result = $this->validate($data,
            [
                'name' => 'require',
                'text' => 'require'
            ]);
        if (true !== $result) {
            $this->error("操作异常!");
        }

        if ($_FILES['img']['name']) {
            $upload = new Upload();
            $data['vipIco'] = $upload->saveIamge('img');
        }

        $vipModel = new VipModel();
        $ret = $vipModel->saveVipRightsEdit($data, $id);
        if (!$ret) {
            $this->error('操作失败!');
        }
        $this->success('操作成功!', '/admin/Vip/vipRights');
    }

    /**
     * Date: 2017-09-20
     * 权益删除
     * @return json
     */
    public function vipRightsDelete()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $vipModel = new VipModel();
            $ret = $vipModel->deleteVipRights($id);
            if ($ret) {
                new OperateLog('删除VIP权益', 'user_member_ico', $id, $this->admin_id);
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