<?php
/**
 * User: LiuTong
 * Date: 2017-10-11
 * Time: 10:40
 */

namespace app\api\controller;

use app\common\controller\BaseApi;
use app\api\model\ServicePhone as ServicePhoneModel;

class ServicePhone extends BaseApi
{
    protected $tokenFlag = 0;
    /**
     * Date: 2017-10-11
     * 客服电话
     */
    public function getServicePhone()
    {
        $servicePhoneModel = new ServicePhoneModel();
        $phone = $servicePhoneModel->servicePhoneGet();
        $this->apiSuccess($phone);
    }

}