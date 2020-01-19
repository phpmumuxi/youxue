<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 菜单数组层级缩进转换
 * @param array $array 源数组
 * @param int $pid 父级id
 * @return array
 */
function array2level($array, $pid = 0)
{
    static $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $list[] = $v;
            array2level($array, $v['id']);
        }
    }

    return $list;
}

/**
 * 菜单数组转换成树形结构
 * @param array $array 源数组
 * @param int $pid 父级id
 * @return array
 */
function array2tree($array, $pid = 0)
{
    $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $childs = array2tree($array, $v['id']);
            $v['childs'] = $childs;
            $list[] = $v;
        }
    }
    return $list;
}

/**
 * 订单好生成
 * @param string $str 订单首字母
 * @return string
 */
function orderId($str = 'M')
{
    return $str . strtoupper(substr(md5(time()), mt_rand(0, 9), 5)) . time() . mt_rand(111111, 999999);
}

function token()
{
    return md5(time() . mt_rand(111111, 999999));
}

/**
 * Date: 2017-09-20
 * 后台操作记录
 * @param $content  操作内容说明
 * @param $table    涉及的表名
 * @param $id       涉及的记录id
 * @param $adminId  操作人id
 */
function operateLog($content, $table, $id, $adminId)
{
    new \app\common\model\OperateLog($content, $table, $id, $adminId);
}

/**
 * Date: 2017-10-19
 * 推送消息记录
 * @param $userId
 * @param $adminId
 * @param $title
 * @param $content
 * @param $type
 * @param bool $tag
 * @return bool
 */
function pushToUser($userId, $adminId, $title, $content, $type, $tag = false)
{
    if ($tag && $type == 5) {
        $user = new \app\common\model\User();
        $phone = $user->getPhoneFromId($userId);
        jPush($phone, $content, $type, $mTime = 86400, ['title' => $title, 'content' => $content]);
    }
    $pushToUser = new \app\common\model\PushToUser();
    return $pushToUser->InsertPushUser($userId, $adminId, $title, $content, $type);
}

/**
 * Date: 2017-10-19
 * 推送
 * @param $phone
 * @param $content
 * @param $type
 * @param int $mTime
 * @param null $extraInfo
 * @return bool|mixed
 */
function jPush($phone, $content, $type, $mTime = 86400, $extraInfo = null)
{
    $push = new \app\common\controller\Jpush();
    $message = '';

    $receive['alias'] = [$phone];

    $arr['info'] = $content;
    $arr['data'] = $extraInfo;

    $ret = $push->push($receive, $content, $arr, $type, $mTime);
    return $ret ? $ret : false;
}
