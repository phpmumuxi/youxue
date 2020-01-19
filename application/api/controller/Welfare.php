<?php
/**
 * User: LiuTong
 * Date: 2017-09-14
 * Time: 15:09
 */

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\Welfare as WelfareModel;

class Welfare extends BaseApi
{

    protected $tokenFlag = 0;

    /**
     * 福利专区列表
     * Date: 2017-09-14
     * @return json
     */
    public function welfareList()
    {
        $arr['page'] = input('post.page', 1);
        $arr['pageSize'] = input('post.pageSize', 10);
        $arr['type'] = input('post.type', 1);

        $WelfareModel = new WelfareModel();
        $data = $WelfareModel->getWelfareList($arr);

        $this->apiSuccess($data);
    }

    /**
     * 商品详情
     * Date: 2017-09-15
     * @return json
     */
    public function welfareGoodsDetail()
    {
        if (input('?post.id')) {
            $id = input('post.id');

            $uid = null;
            if (input('?post.token')) {
                $uid = $this->tokenCheck();
            }

            $WelfareModel = new WelfareModel();
            $data = $WelfareModel->getWelfareGoodsDetail($id, $uid);
            $this->apiSuccess($data);
        } else {
            $this->apiError('paramError');
        }
    }

    /**
     * 选择校区地址
     * Date: 2017-09-15
     * @return json
     */
    public function welfareGoodsSchoolAddress()
    {
        // latitude 纬度 longitude 经度
        if (input('?post.id') && input('?post.schoolIds') && input('?post.longitude') && input('?post.latitude')) {
            $goodsId = input('post.id');
            $schoolIds = input('post.schoolIds');
            $longitude = input('post.longitude');
            $latitude = input('post.latitude');

            $WelfareModel = new WelfareModel();
            $data = $WelfareModel->getWelfareGoodsSchoolAddress($goodsId, $schoolIds, $longitude, $latitude);
            $this->apiSuccess($data);
        } else {
            $this->apiError('paramError');
        }
    }

    /**
     * 普通会员、VIP会员领取或豆豆兑换
     * Date: 2017-09-15
     * @return json
     */
    public function welfareTake()
    {
        $this->tokenCheck();
        if (input('?post.id') && input('?post.schoolId') && input('?post.stockId') && input('?post.type')) {
            $uid = $this->uid;
            $goodsId = input('post.id');
            $schoolId = input('post.schoolId');
            $stockId = input('post.stockId');
            $type = input('post.type');
            $WelfareModel = new WelfareModel();
            $ret = $WelfareModel->takeWelfareGoods($uid, $goodsId, $schoolId, $stockId, $type);
            if ($ret) {
                $this->apiError($ret);
            } else {
                $data = $WelfareModel->getWelfareGoodsEndDaysSchoolInfo($goodsId, $uid);
                $this->apiSuccess($data);
            }
        } else {
            $this->apiError('paramError');
        }
    }

    /**
     * 我的福利
     * Date: 2017-09-15
     * @return json
     */
    public function welfareUserList()
    {
        $this->tokenCheck();

        $arr['uid'] = $this->uid;
        $arr['page'] = input('post.page', 1);
        $arr['pageSize'] = input('post.pageSize', 10);

        $WelfareModel = new WelfareModel();
        $data = $WelfareModel->getWelfareUserList($arr);

        $this->apiSuccess($data);
    }

    /**
     * 实物领取页面
     * Date: 2017-09-18
     * @return json
     */
    public function welfareUserGoodsDetail()
    {
        $this->tokenCheck();
        $arr['uid'] = $this->uid;
        if (input('?post.id')) {
            $arr['id'] = input('post.id');
            $WelfareModel = new WelfareModel();
            $data = $WelfareModel->getWelfareUserGoodsDetail($arr);

            $this->apiSuccess($data);
        } else {
            $this->apiError('paramError');
        }
    }

    /**
     * 实物领取
     * Date: 2017-09-18
     * @return json
     */
    public function welfareRealTake()
    {
        $this->tokenCheck();
        $arr['uid'] = $this->uid;
        if (input('?post.id')) {
            $arr['id'] = input('post.id');

            $WelfareModel = new WelfareModel();
            $ret = $WelfareModel->welfareTakeTime($arr['id'], $arr['uid']);
            if (!$ret) {
                $this->apiError('welfareTakeOutTime');
            }
            $ret = $WelfareModel->welfareUserRealTake($arr);
            if ($ret) {
                $this->apiSuccess($ret);
            } else {
                $this->apiError('failure');
            }
        } else {
            $this->apiError('paramError');
        }
    }

    //商品详情H5
    public function goodsHtml()
    {
        $id = input('post.id');
        $WelfareModel = new WelfareModel();
        $data = $WelfareModel->goodsHtmlDate($id);
        $this->apiSuccess($data);
    }

}