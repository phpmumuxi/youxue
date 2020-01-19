<?php
namespace app\admin\model;

use \think\Model;

class FeedBack extends Model
{
	//用户提现列表数据
    public function getUserFeedBack($phone)
    {
        $where = $phone ? ['u.phone' => $phone] : '';
        $lists  =  db('feed_back')
                -> alias('f')
                -> field("f.msg,f.createTime,u.name as userName,u.phone as userPhone")
                -> where($where)
                -> join('t_user u','u.id=f.userId','LEFT')                
                -> order('f.createTime DESC')
                ->paginate(10, false, ['query' => ['phone' => $phone]]);
        return $lists;
    }

}