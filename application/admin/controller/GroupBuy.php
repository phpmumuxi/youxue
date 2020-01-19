<?php
/**
 * User: LiuTong
 * Date: 2017-08-17
 * Time: 10:03
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\GroupBuy as GroupBuyModel;
use app\common\controller\Upload;
use think\Validate;

class GroupBuy extends AdminBase
{

    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-08-17
     * fix Date: 2017-09-04
     * fix Content: 增加搜索
     * @return \think\response\View
     */
    public function GroupBuyOrderList()
    {
        $orderNo = '';
        if (input('?get.orderNo')) {
            $orderNo = input('get.orderNo');
        }
        $GroupBuyModel = new GroupBuyModel();
        $data = $GroupBuyModel->getGroupBuyOrderList($orderNo);
        $pages = $data->render();
        $data = $data->toArray();

        $orderStatus = [0 => '未支付', 1 => '未使用', 2 => '已使用'];
        $disposeStatus = ['否', '是'];
        return view('groupbuyorderlist',
            [
                'pages' => $pages,
                'data' => $data,
                'orderStatus' => $orderStatus,
                'disposeStatus' => $disposeStatus,
                'orderNo' => $orderNo
            ]
        );
    }

    /**
     * Date: 2017-10-16
     * 团购列表
     * @return \think\response\View
     */
    public function groupBuyList()
    {
        $name = null;
        if (input('?get.name')) {
            $name = input('get.name');
        }
        $GroupBuyModel = new GroupBuyModel();
        $data = $GroupBuyModel->getGroupBuyList($name);
        $pages = $data->render();
        $data = $data->toArray();

        return view('groupBuyList',
            [
                'pages' => $pages,
                'data' => $data,
                'name' => $name
            ]
        );
    }

    /**
     * Date: 2017-10-16
     * 团购上架
     */
    public function groupBuyListUp()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $GroupBuyModel = new GroupBuyModel();
            $ret = $GroupBuyModel->upGroupBuy($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Up Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-16
     * 团购下架
     */
    public function groupBuyListDown()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $GroupBuyModel = new GroupBuyModel();
            $ret = $GroupBuyModel->downGroupBuy($id, $this->admin_id);
            if ($ret) {
                $res['status'] = 'ok';
            } else {
                $res['msg'] = 'Down Failure';
            }
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param Missing';
        }
        echo json_encode($res);
    }

    /**
     * Date: 2017-10-16
     * 团购删除
     */
    public function groupBuyListDelete()
    {
        $res['status'] = 'false';
        if (input('?post.id')) {
            $id = input('post.id');
            $GroupBuyModel = new GroupBuyModel();
            $ret = $GroupBuyModel->deleteGroupBuy($id, $this->admin_id);
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

    /**
     * Date: 2017-10-16
     * 团购修改
     * @return \think\response\View
     */
    public function groupBuyEdit()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $GroupBuyModel = new GroupBuyModel();
            $data = $GroupBuyModel->getGroupBuyOne($id);
            $brands = $GroupBuyModel->getBrands();
            $selectedBrandsTemp = $GroupBuyModel->getSelectedBrands($id);
            $selectedBrands = [];
            if ($selectedBrandsTemp) {
                foreach ($selectedBrandsTemp as $k => $v) {
                    array_push($selectedBrands, $v['brandId']);
                }
            }
            return view('groupBuyEdit',
                [
                    'data' => $data,
                    'brands' => $brands,
                    'selectedBrands' => $selectedBrands
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-10-17
     * 团购更新
     */
    public function groupBuyUpdate()
    {
        if (input('?get.id')) {
            $id = input('get.id');
        } else {
            $this->error('操作异常!');
        }
        $post = $this->request->post();
        $validate = new Validate(
            [
                'name' => 'require',
                'title' => 'require',
                'explains' => 'require',
                'term' => 'require|number',
                'money' => 'require|number',
                'brand' => 'require',
                'brandNum' => 'require',
                'isCmbc' => 'require',
            ]
        );
        $ret = $validate->check($post);
        if (!$ret) {
            $this->error('操作异常');
        }

        $upload = new Upload();
        if ($_FILES['smallImg']['name']) {
            $post['smallImg'] = $upload->saveIamge('smallImg');
        }
        if ($_FILES['bigImg']['name']) {
            $post['bigImg'] = $upload->saveIamge('bigImg');
        }
        if (input('?post.content')) {
            $post['introduct'] = input('post.content', '', 'htmlspecialchars');
            unset($post['content']);
        } else {
            $this->error('操作异常!');
        }

        $GroupBuyModel = new GroupBuyModel();
        $ret = $GroupBuyModel->updateGroupBuy($post, $id, $this->admin_id);
        if ($ret) {
            $this->success('操作成功!', '/admin/GroupBuy/groupBuyList');
        } else {
            $this->error('操作失败!');
        }
    }

    /**
     * Date: 2017-10-17
     * 团购新增
     * @return \think\response\View
     */
    public function groupBuyAdd()
    {
        $GroupBuyModel = new GroupBuyModel();
        $brands = $GroupBuyModel->getBrands();
        return view('groupBuyAdd',
            [
                'brands' => $brands
            ]
        );
    }

    /**
     * Date: 2017-10-17
     * 团购保存
     */
    public function groupBuySave()
    {
        $post = $this->request->post();
        $validate = new Validate(
            [
                'name' => 'require',
                'title' => 'require',
                'explains' => 'require',
                'term' => 'require|number',
                'money' => 'require|number',
                'brand' => 'require',
                'brandNum' => 'require',
                'isCmbc' => 'require',
            ]
        );
        $ret = $validate->check($post);
        if (!$ret) {
            $this->error('操作异常');
        }

        $upload = new Upload();
        if ($_FILES['smallImg']['name'] && $_FILES['bigImg']['name']) {
            $post['smallImg'] = $upload->saveIamge('smallImg');
            $post['bigImg'] = $upload->saveIamge('bigImg');
        } else {
            $this->error('操作异常!');
        }
        if (input('?post.content')) {
            $post['introduct'] = input('post.content', '', 'htmlspecialchars');
            unset($post['content']);
        } else {
            $this->error('操作异常!');
        }

        $GroupBuyModel = new GroupBuyModel();
        $ret = $GroupBuyModel->saveGroupBuy($post, $this->admin_id);
        if ($ret) {
            $this->success('操作成功!', '/admin/GroupBuy/groupBuyList');
        } else {
            $this->error('操作失败!');
        }
    }

}