<?php
/**
 * User: LiuTong
 * Date: 2017-09-29
 * Time: 14:18
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\admin\model\ModuleContent as ModuleContentModel;

class ModuleContent extends AdminBase
{

    private $typeOld = [
        1 => '紧急寻子说明',
        2 => '客服联系方式',
        3 => '普通用户返现',
        4 => 'vip用户返现',
        5 => '妈妈约简介',
        6 => '妈妈约满足几人返豆豆（返vip）',
        7 => '免费领几天过期',
        8 => '购买会员后几天返现',
        9 => '团购简介',
        10 => 'vip会员简介',
        11 => '普通会员简介',
        12 => '万人砍',
        13 => 'vip尊享免费卡使用说明',
        14 => '注册协议',
        15 => '银行卡说明',
        16 => '购买vip页面说明',
    ];

    private $type = [
        1 => '客服联系方式',
        2 => '妈妈约简介',
        3 => '团购简介',
        4 => 'vip会员简介',
        5 => '普通会员简介',
        6 => '万人砍',
        7 => '注册协议',
        8 => '银行卡说明',
        9 => '购买vip页面说明',
        10 => '星星点灯活动说明',
        11 => '福利专区介绍'
    ];

    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-09-29
     * 模块信息列表
     * @return \think\response\View
     */
    public function moduleContentList()
    {
        $moduleContentModel = new ModuleContentModel();
        $data = $moduleContentModel->getModuleContentList();
        $pages = $data->render();
        $data = $data->toArray();
        return view('moduleContentList',
            [
                'data' => $data,
                'pages' => $pages,
                'type' => $this->type
            ]
        );
    }

    /**
     * Date: 2017-09-29
     * 获取介绍内容
     * @return json
     */
    public function getModuleContentAjax()
    {
        if (input('?post.id')) {
            $id = input('post.id');
            $moduleContentModel = new ModuleContentModel();
            $data = $moduleContentModel->getModuleContentData($id);
            $this->responseAjax(true, null, htmlspecialchars_decode($data['content']));
        } else {
            $this->responseAjax('Err', 'Param Missing');
        }
    }

    /**
     * Date: 2017-09-29
     * 新增页面
     * @return \think\response\View
     */
    public function moduleContentAdd()
    {
        $moduleContentModel = new ModuleContentModel();
        $getTypes = $moduleContentModel->getModuleType();
        $types = $this->type;
        foreach ($getTypes as $k => $v) {
            unset($types[$v]);
        }
        return view('moduleContentAdd', ['type' => $types]);
    }

    /**
     * Date: 2017-09-29
     * 新增模块说明
     */
    public function moduleContentSave()
    {
        if (input('?post.content') && input('?post.typeModule')) {
            $data['type'] = input('post.typeModule');
            if ($data['type'] == 1) {
                $data['content'] = strip_tags($_POST['content']);
            } else {
                $data['content'] = input('post.content', '', 'htmlspecialchars');
            }
            $data['name'] = $this->type[$data['type']];
            $moduleContentModel = new ModuleContentModel();
            $ret = $moduleContentModel->addModuleContent($data);
            if ($ret) {
                $this->success('操作成功!', '/admin/ModuleContent/moduleContentList');
            } else {
                $this->error('操作失败!');
            }
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-09-29
     * 修改模块介绍内容
     * @return \think\response\View
     */
    public function moduleContentUpdate()
    {
        if (input('?get.id')) {
            $id = input('get.id');
            $moduleContentModel = new ModuleContentModel();
            $data = $moduleContentModel->getModuleContentData($id);
            return view('moduleContentEdit',
                [
                    'type' => $this->type,
                    'data' => $data
                ]
            );
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-09-29
     * 模块信息更新
     */
    public function moduleContentUpdateSave()
    {
        if (input('?get.id') && input('?post.content') && input('?post.type')) {
            $id = input('get.id');
            $data['type'] = input('post.type');
            if ($data['type'] == 1) {
                $data['content'] = strip_tags($_POST['content']);
            } else {
                $data['content'] = input('post.content', '', 'htmlspecialchars');
            }
            $data['name'] = $this->type[$data['type']];
            $moduleContentModel = new ModuleContentModel();
            $ret = $moduleContentModel->updateModuleContent($id, $data, $this->admin_id);
            if ($ret) {
                $this->success('操作成功!', '/admin/ModuleContent/moduleContentList');
            } else {
                $this->error('操作失败!');
            }
        } else {
            $this->error('操作异常!');
        }
    }

    /**
     * Date: 2017-09-29
     * 删除模块介绍信息
     * @return json
     */
    public function moduleContentDelete()
    {
        if (input('?post.id')) {
            $id = input('post.id');
            $moduleContentModel = new ModuleContentModel();
            $ret = $moduleContentModel->deleteModuleContent($id, $this->admin_id);
            if ($ret) {
                $this->responseAjax(true);
            } else {
                $this->responseAjax(false, 'Delete Failure');
            }
        } else {
            $this->responseAjax('Err', 'Param Missing');
        }
    }

    private
    function responseAjax($just = null, $msg = null, $data = null)
    {
        if ($just === true) {
            $res['status'] = 'ok';
            if ($data) {
                $res['data'] = $data;
            }
        } elseif ($just === false) {
            $res['status'] = 'false';
            $res['msg'] = $msg;
        } else {
            $res['status'] = $just;
            $res['msg'] = $msg;
        }
        echo json_encode($res);
        die;
    }
}