<?php
/**
 * User: LiuTong
 * Date: 2017-10-10
 * Time: 10:45
 */

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\ChildrenTips as ChildrenTipsModel;

class ChildrenTips extends BaseApi
{

    protected $tokenFlag = 0;

    public function index()
    {
        return false;
    }

    /**
     * Date: 2017-10-10
     * 育儿师类型
     * fix Date: 2017-10-11
     * fix Content: 增加列表图片
     */
    public function childrenTipsType()
    {
        $childrenTipsModel = new ChildrenTipsModel();
        $data = $childrenTipsModel->getChildrenTisTypeImages();
        $pData = [];
        $ppData = [];
        $types = [];
        if ($data) {
            foreach ($data as $k => $v) {
                array_push($types, $v['typeName']);
                $pData[$v['divideType']][] = [
                    'type' => $v['type'],
                    'typeName' => $v['typeName'],
                    'img' => $v['img']
                ];
            }
            foreach ($pData as $k => $v) {
                $ppData[] = $v;
            }
        }
        $this->apiSuccess(
            [
                'types' => $types,
                'list' => $ppData
            ]
        );
    }

    /**
     * Date: 2017-10-10
     * 搜索
     */
    public function childrenTipsSearch()
    {
        if (input('?post.type')) {
            $type = input('post.type');
            $searchContent = input('post.title');
            $childrenTipsModel = new ChildrenTipsModel();
            $data = $childrenTipsModel->getChildrenTipsList($type, $searchContent);
            $this->apiSuccess($data);
        } else {
            $this->apiError('paramMiss');
        }
    }

    /**
     * Date: 2017-10-10
     * 育儿师内容H5
     */
    public function childrenTipsContentGet()
    {
        if (input('?post.id')) {
            $id = input('post.id');
            $childrenTipsModel = new ChildrenTipsModel();
            $data = $childrenTipsModel->getChildrenTipsContent($id);
            $this->apiSuccess($data);
        } else {
            $this->apiError('paramMiss');
        }
    }

    //育儿师内容H5
    public function parentsHtml()
    {
        $id = input('post.id');
        $childrenTipsModel = new ChildrenTipsModel();
        $data = $childrenTipsModel->parentsHtmlDate($id);
        $this->apiSuccess($data);
    }

}