<?php
/**
 * User: LiuTong
 * Date: 2017-10-11
 * Time: 10:49
 */

namespace app\api\model;

use think\Model;

class ServicePhone extends Model
{
    /**
     * Date: 2017-10-11
     * 获取联系电话
     * @return mixed
     */
    public function servicePhoneGet()
    {
        $where['type'] = 1;
        $where['isDelete'] = 0;
        $phone = db('informat')->where($where)->value('content');
        return $phone;
    }
}