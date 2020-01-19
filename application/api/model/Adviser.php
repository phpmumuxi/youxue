<?php
/**
 * Created by PhpStorm.
 * User: xin
 * Date: 2017-09-15
 */

namespace app\api\model;

use think\Model;

class Adviser extends Model
{
    public function adviserNewsData($arr)
    {
        $re = $this->adviserInfo( $arr['adviserId']);
        $data = db('adviser_order a')
            ->field('a.id,a.name,a.userId,a.status,u.name as userName,u.face')
            ->join('user u','u.id=a.userId','left')
            ->where([
                'adviserId' => $re['id'],
                'a.status' => 0,
                'orderType' => ['in',[1,2,4]]
            ])
            ->page($arr['page'],$arr['pagesize'])
            ->order('a.createTime desc')
            ->select();
        return $data;
    }

    public function adviserUserData($arr)
    {
        $re = $this->adviserInfo( $arr['adviserId']);

        
        $user = db('adviser_order')
            ->field('userId')
            ->where([
                'adviserId' => $re['id']
            ])
            ->group('userId')
            ->select();
        if(!$user)return $user;
        $userIds = array_column($user,'userId');
        $userIds = implode(',', $userIds);
        $data = db('user u')
            ->field('u.id as userId,u.name as userName,u.face,u.phone,b.nickname,b.sex,b.birthTime,""as text')
            ->join('user_baby b','u.id=b.userId','left')
            ->where([
                'u.id' =>['in',$userIds]
            ])
            ->page($arr['page'],$arr['pagesize'])
            ->select();
        $uids = array_column($data,'userId');
        $arr['userIds'] = implode(',', $uids);
        $text = $this->adviserTextInfos($arr);
        if($text){
            foreach ($data as $k => $v) {
                foreach ($text as $j => $val) {
                   if($val['userId']==$v['userId']){
                    $data[$k]['text']=$val['text'];
                   }
                }
            }
        }
        return $data;
    }

    public function adviserUserInfoData($arr)
    {
      $data = db('user u')
            ->field('u.id as userId,u.name as userName,u.face,u.phone,b.nickname,b.sex,b.birthTime')
            ->join('user_baby b','u.id=b.userId','left')
            ->where([
                'u.id' =>$arr['userId']
            ])
            ->find();
        $data['text'] = '';
        $data['remarkId'] = '';
        $text = $this->adviserTextInfo($arr);
        if($text){
            $data['text'] = $text['text'];
            $data['remarkId'] = $text['id'];
            $data['dateTime']=$text['changeTime']?$text['changeTime']:$text['createTime'];
        }
        $data['class'] = $this->adviserOrder($arr);
        return $data;

    }

    public function adviserRemarkData($arr)
    {
        $re = $this -> adviserTextInfo($arr);
        if($re)return false;
        $re = $this->adviserInfo($arr['adviserId']);
        $arr['adviserId']=$re['id'];
        $arr['createTime'] = time();
        $a = db('adviser_user')->insert($arr);
        return $a?true:false;
    }

    public function adviserAlterRemarkData($arr)
    {
        $data = db('adviser_user')
                ->where('id',$arr['remarkId'])
                ->find();
        if(!$data)return false;
        $a = db('adviser_user')->update([
                'id' => $arr['remarkId'],
                'text' => $arr['text'],
                'changeTime'=>time()
            ]);
        return $a?true:false;
    }

    public function adviserDelRemarkData($arr)
    {
        $data = db('adviser_user')
                ->where('id',$arr['remarkId'])
                ->find();
        if(!$data)return false;
        $a = db('adviser_user')->delete($arr['remarkId']);
        return $a?true:false;
    }


    private function adviserTextInfos($arr)
    {
        $re = $this->adviserInfo( $arr['adviserId']);
        $data = db('adviser_user')
                ->where([
                        'adviserId' => $re['id'],
                        'userId' => ['in',$arr['userIds']]
                    ])
                ->select();
        return $data;
    }

     private function adviserTextInfo($arr)
    {
        $re = $this->adviserInfo( $arr['adviserId']);
        $data = db('adviser_user')
                ->where([
                        'adviserId' => $re['id'],
                        'userId' => $arr['userId']
                    ])
                ->find();
        return $data;
    }

    private function adviserOrder($arr)
    {
        $re = $this->adviserInfo( $arr['adviserId']);
        $data = db('adviser_order')
            ->field('name,status')
            ->where([
                'adviserId' => $re['id'],
                'userId' => $arr['userId'],
                // 'isDelete'=>0
            ])
            ->select();
        return $data;
    }

    private function adviserInfo($uid)
    {
        $data = db('adviser_name')->where(['userId'=>$uid])->find();
        return $data;
    }
}