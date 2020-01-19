<?php
/**
 * Created by PhpStorm.
 * User: Xin
 * Date: 2017-09-22
 */

namespace app\api\model;

use think\Db;
use think\Model;
use app\api\model\AddShare;
use app\common\libs\DistenceTool as distance;

class Star extends Model
{

    //页面数据1
    public function starHomeData($uid)
    {
        $isShare = 0;
        if($uid){
            $data = $this->myShareInfo($uid);
            if($data){
                $isShare = 1;
            }
        }
        if(!$isShare){
            $re['starRule'] = $this->NoShare();
            $re['starNum']=0;
        }else{
            $re['starRule'] = $this->shareStarRule($uid);
            $re['starNum']=$this->myStarRecord($uid);
        }

        return $re;
    }

    //页面信息2
    public function starHomeDataTwo($uid)
    {
        $re = [];
        $re['info']['starTicke']=0;
        $data = $this->myShareInfo($uid);
        if($data){
            $this->checkStatus($uid);
            $statusStar = $this->statusStar($uid);
            // print_r($statusStar);exit;
            if($statusStar){
                $re['starInfo']=[
                    'starName'=>$statusStar['name'],
                    'needStar'=>$statusStar['starNum']-($this->myStarRecord($uid)),
                    'classNum'=>$statusStar['classNum'],
                    'status' => 0
                ];
            }else{
                $s = db('star_rule_user')->where(['userId'=>$uid,'isEnd'=>0])->order('level desc')->value('status');
                $re['starInfo']['status']=$s?1:0;

            }
            $re['starInfo']['starNum']=$data['starNum'];
            $s = $this->starTicke($uid);
            $re['info']['starTicke']=$s;
        }
        $res = $this->starFreeData($uid);
        if($res){
            $re['info']['starFree']=$res['endTime']>time()?$res['remainNum']:0;
        }
        $re['info']['shareId'] = $this->isShareId($uid);
        return $re;
    }

    // 未登录或不是分享人
    private function NoShare()
    {
        $data = db('star_rule')->field('name,img,level,starNum,classNum,days')->where('isDelete=0')->select();
        return $data;
    }

    //查询分享人活动规则
    private function shareStarRule($uid)
    {
        $data = db('star_rule_user')
                ->field('id as starId,name,img,level,days,endTime,starNum,status,classNum,createTime')
                ->where(['userId'=>$uid,'isEnd'=>0])
                ->select();
        $arr=[];
        foreach ($data as $k => $v) {
            if(($v['endTime']<time()&&$v['status']==0)||(($v['endTime']+2592000)<time()&&$v['status']==1)){
                $arr[]=$v['starId'];
            }
        }
        $ids = implode(',', $arr);
       if($ids){
            db('star_rule_user')->where('id','in',$ids)->update(['status'=>3]);
        }
        return $data;
    }

    //获取最近一个灯未亮的星星灯信息
    private function statusStar($uid)
    {
        $data = db('star_rule_user')
                ->field('id,name,img,level,days,endTime,starNum,status,classNum,createTime')
                ->where(['userId'=>$uid,'isEnd'=>0,'status'=>0])
                ->order('level asc')
                ->find();
        return $data;
    }



    //查询个人信息
    private function myShareInfo($uid)
    {
        $data = db('star_user')->where('userId',$uid)->find();
        return $data;
    }

    private function myStarRecord($uid)
    {
        $data = db('star_user_record')->where('status=0 and userId='.$uid)->value('starNum');
        return $data;
    }
    //免费券的情况
    private function starFreeData($uid)
    {
        $data = db('star_free')->where(['userId'=>$uid])->find();
        return $data;
    }

    //星星券的信息
    private function starTicke($uid)
    {
        $data = db('star_ticket')
                // ->field('sum(remainNum) as sum')
                ->where([
                'userId'=>$uid,
                'endTime'=>['gt',time()]
                ])->sum('remainNum');
        return $data;
    }

    //判断是否有分享人
    private function isShareId($uid)
    {
        $data = db('user')->where('id',$uid)->value('shareId');
        return $data;
    }

    //获取用户的信息
    private function isShareInfo($uid)
    {
        $data = db('user')->field('id,shareId,memberLevel')->where('id',$uid)->find();
        return $data;
    }

    //检测灯的状态及一周期活动结束
    public function checkStatus($uid)
    {

        $data = db('star_rule_user')
                ->field('name,img,level,days,endTime,starNum,status,classNum,createTime,starRound')
                ->where(['userId'=>$uid,'isEnd'=>0])
                // ->where('level=12')
                ->order('level desc')
                ->find();
        // print_r($data);exit;
        $re = $this->statusStar($uid);
        $res = db('star_user_record')->field('id,endTime,starNum')->where('status=1 and starStatus=0 and userId='.$uid)->find();

        if($data&&$data['endTime']<time()){
            $data1 = db('star_rule')->where('isDelete=0')->select();
            $array = [];
            foreach ($data1 as $k => $v) {
                $array[]=[
                    'userId'=>$uid,
                    'starId'=>$v['id'],
                    'img'=>$v['img'],
                    'name'=>$v['name'],
                    'level'=>$v['level'],
                    'days'=>$v['days'],
                    'endTime'=>strtotime(date('Y-m-d 23:59:59',time()))+$v['days']*86400,
                    'starNum'=>$v['starNum'],
                    'status'=>0,
                    'classNum'=>$v['classNum'],
                    'createTime'=>time(),
                    'starRound'=>$data['starRound']+1,
                    'isEnd'=>0
                ];
            }
            $array2 = [
                'userId'=>$uid,
                'starNum'=>0,
                'createTime'=>time(),
                'starRound'=>$data['starRound']+1,
                'status'=>0,
                'starStatus'=>0,
                'endTime'=>strtotime(date('Y-m-d 23:59:59',time()))+(($this->satrTime())+30)*86400,
            ];

            Db::startTrans();
            try{
                db('star_rule_user')->where(['userId'=>$uid,'starRound'=>$data['starRound']])->update(['isEnd'=>1]);
                db('star_user_record')->where(['userId'=>$uid,'starRound'=>$data['starRound']])->update(['status'=>1]);
                db('star_rule_user')->insertAll($array);
                db('star_user_record')->insert($array2);
                // 提交事务
                // Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return false;
            }
        }
        if($re&&($re['endTime']<time()&&$re['status']==0)){
            db('star_rule_user')
            ->update([
                    'id'=>$re['id'],
                    'status'=>3
                ]);
        }
        Db::commit();
        if($res){
            if($res['endTime']<time()){
                if($res['starNum']>0){
                    $a = new \app\common\model\UserStar();
                    $a->changeStar($uid,$res['starNum'],$res['id'],3);
                }
                db('star_user_record')->update([
                        'id'=>$res['id'],
                        'starStatus'=>1
                    ]);
            }
        }
        return true;
    }

     private function satrTime()
    {
        $day = db('star_rule')->where('isDelete=0')->order('level desc')->value('days');
        return $day;
    }

    //2.添加分享人
    public function insertShareData($arr)
    {
        $r = $this->isShareInfo($arr['userId']);
        if($r['shareId'])return 'shareYes';
        $re = db('user')->where('phone',$arr['phone'])->value('id');
        if(!$re)return 'userNo';
        $a = new \app\api\model\AddShare();
        $data = $a->addShareDate($r,$re);
        return $data;
    }

    //分享人信息
    public function shareInfoData($uid)
    {
        $data = null;
        $shareId = $this->isShareId($uid);
        if($shareId){
            $data['shareInfo'] = db('user')->field('name,phone')->where('id',$shareId)->find();
        }
        $free = $this->starFreeData($uid);
        if($free){
            $data['free']=[
                'freeId' =>$free['id'],
                'freeNum' =>$free['remainNum'],
                'endTime'=>$free['endTime'],
            ];
            if($free['remainNum']<$free['num']){
                $data['free']['useFree'] = db('star_free_use f')
                    ->field('b.name,s.phone,s.address,f.status,f.createTime,f.useTime')
                    ->join('brand b','b.id=f.brandId','left')
                    ->join('school s','s.id=f.schoolId','left')
                    ->where('f.freeId='.$free['id'])
                    ->where('f.userId='.$uid)
                    ->select();
            }
        }
        return $data;
    }

    //我的星座券
    public function myStarticketData($uid)
    {
        $data = db('star_ticket')
                ->field('id as ticketId,name,remainNum,endTime')
                ->where([
                'userId'=>$uid,
                'endTime'=>['gt',time()],
                'remainNum'=>['gt',0]
                ])->select();
        return $data;
    }

    //收集星星详情
    public function myStarInfoData($arr)
    {
        switch ($arr['type']) {
            case 1:
                $data = $this->starInfoData($arr);
                break;
            case 2:
                $data = $this->starTicketData($arr);
                break;
            case 3:
                $data = $this->starUseData($arr);
                break;
            default:
                $data = null;
                break;
        }
        return $data;
    }

    //星星详情
    private function starInfoData($arr)
    {
        $data = db('star_change')
            ->field('starNum,createTime')
            ->where(['userId'=>$arr['userId'],'type'=>1])
            ->order('id desc')
            ->page($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }
    //领取记录
    private function starTicketData($arr)
    {
        $data = db('star_ticket_use s')
            ->field('className,s.classNum,s.createTime,b.name as brandName')
            ->join('brand b','b.id=s.brandId','left')
            ->where(['s.userId'=>$arr['userId']])
            ->order('s.id desc')
            ->page($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }

    //兑换记录
    private function starUseData($arr)
    {
        $data = db('star_use s')
            ->field('starNum,className,s.classNum,s.createTime,b.name as brandName')
            ->join('brand b','b.id=s.brandId','left')
            ->where(['s.userId'=>$arr['userId']])
            ->order('s.id desc')
            ->page($arr['page'],$arr['pagesize'])
            ->select();
        return $data;
    }

    //兑换星星页面
    public function starConvertPageData($uid)
    {
        $data = db('star_user_record')->field('starNum,endTime,status')->where(['userId'=>$uid,'starStatus'=>0])->select();
        return $data;
    }

    //可选品牌
    public function starBrandData($arr)
    {
        $data =$this->schooldCity($arr['cityCode']);
        $ids = array_column($data,'brandId');
        $where = ['id' =>['in',$ids]];

        $data = $this->brandCity($where);
        if($arr['type']){
            $free = $this->freeUse($arr['userId']);
            foreach ($data as $k => $v) {
                $data[$k]['isUse']=0;
                foreach ($free as $j => $val) {
                if($v['brandId']==$val['brandId']){
                    $data[$k]['isUse']=1;
                    }
                }
            }
        }
        
        return $data;
    }
    //免费券使用信息
    private function freeUse($uid)
    {
        $data = db('star_free_use')->field('brandId')->where('userId',$uid)->select();
        return $data;
    }

    //品牌校区
   public function starBranSchoolDate($cityCode)
   {
        $data =$this->schooldCity($cityCode);
        $ids = array_column($data,'brandId');
        $arr = ['id' =>['in',$ids]];
        $brand = $this->brandCity($arr);
        foreach ($brand as $k => $v) {
            foreach ($data as $j => $val) {
                if($v['brandId']==$val['brandId']){
                    $brand[$k]['schoold'][]=$data[$j];
                }
            }
        }
        return $brand;
   }
   private function brandCity($arr)
   {
        $data = db('brand')->field('id as brandId,name,smallImg')->where('isDelete=0 and isStar=1')->where($arr)->select();
        return $data;
   }
   private function schooldCity($cityCode)
   {
        $data = Db::name('school')
            ->field('id as schoolId,brandId,name as schoolName')
            ->where('cityCode',$cityCode)
            ->where('status','1')
            ->select();
        return $data;
   }

   //类别
   public function starCourseTypeDate()
   {
        $data = Db::name('class_type')
            ->field('id,typeId,name')
            ->select();
        $arr=[];
        if($data){
            foreach ($data as $key => $value) {
                if($value['typeId']){
                    $key=$value['typeId'];
                    unset($value['typeId']);
                    $arr[$key]['type'][]=$value;
                }else{
                    unset($value['typeId']);
                    $arr[$value['id']]=$value;
                }
            }
        }

        $arr = array_values($arr);
        return $arr;
   }

   //条件查询
   public function starClassData($arr)
   {
        $age = $arr['endAge']&&$arr['startAge']? ' (c.endAge >='.$arr['endAge'].' and c.startAge <='.$arr['startAge'].') or (c.endAge <='.$arr['endAge'].' and c.startAge >='.$arr['startAge'].')':'';
        $where = [];
        $whereOr = '';
        if(!$arr['schoolId']&&$arr['lat']&&$arr['lng']&&$arr['distance']){
            $raidus = $arr['distance']?$arr['distance']:5000;
            $a = new distance();
            $array = $a->getLocation( $arr['lat'], $arr['lng'],$raidus);

            $where['longitude'] = ['between',[$array['min_lng'],$array['max_lng']]];

            $where['latitude'] = ['between',[$array['min_lat'],$array['max_lat']]];
        }
        if($arr['brandId'])$where['c.brandId']=$arr['brandId'];
        if($arr['schoolId'])$where['c.schoolId']=$arr['schoolId'];
        if($arr['classType'])$where['c.typeId']=$arr['classType'];
        if($arr['endAge']&&$arr['startAge'])$whereOr = $age;

        $data = $this->starClassInfo($arr,$where,$whereOr);
        return $data;
   }

   //查询课程信息
   private function starClassInfo($arr,$where,$whereOr='')
   {
        $field = -1;
        if($arr['lat']&&$arr['lng']){
          $field = 'ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $arr['lat'] . '*PI()/180-latitude*PI()/180)/2),2)+COS(' . $arr['lat'] . '*PI()/180)*COS(latitude*PI()/180)*POW(SIN((' . $arr['lng'] . '*PI()/180-longitude*PI()/180)/2),2)))*1000)';
        }

        $data = Db::name('class_school')
            ->alias('c')
            ->join('school s','s.id=c.schoolId','left')
            ->join('brand b','b.id=c.brandId','left')
            ->field('c.id as classId,c.name as className,c.listImg,c.money,c.brandId,c.starNum,b.name as brandName,longitude,latitude,'.$field.' AS distance')
            ->where('c.status=2 and c.isDelete=0 and c.isStar=1')
            ->where('s.cityCode='.$arr['cityCode'])
            ->where($where)
            ->where($whereOr)
            ->page($arr['page'],$arr['pagesize'])
            ->order('distance')
            // ->fetchsql(1)
            ->select();
        return $data;
   }

   //兑换课程页面
   public function convertClassPageData($arr)
   {
        $re = $this->myShareInfo($arr['userId']);
        $data = $this->StarCourseOneDate($arr);
        if($arr['type']==1){
            $data['myStarticket']=$this->myStarticketOne($arr);
        }else{
            $re = $this->myShareInfo($arr['userId']);
            $data['myStarNum']=$re['starNum'];
        }
        return $data;
   }

   //课程详情
   public function StarCourseOneDate($arr)
   {
        $data = Db::name('class_school')
            ->alias('c')
            ->field('c.id as classId,c.name,topImg,c.starNum,c.startAge,c.endAge,c.classTime,s.address,s.phone,s.longitude,s.latitude,c.brandId,b.name as brandName,c.starNum')
            ->join('school s','s.id = c.schoolId','left')
            ->join('brand b','b.id=c.brandId','left')
            ->where(['c.id'=>$arr['classId']])
            ->find();
        return $data;
   }

   //星座卷
   private function myStarticketOne($arr)
   {
        $data = db('star_ticket')->where(['id'=>$arr['ticketId'],'endTime'=>['gt',time()]])->value('remainNum');
        return $data;
   }

   //兑换课程
   public function convertClassData($arr)
   {
        $data = db('class_school c')
            ->field('c.id,c.name,topImg,c.starNum,c.exchangeNum,c.startAge,c.endAge,c.classTime,s.address,s.longitude,s.latitude,c.brandId,b.name as brandName,c.shopId,c.schoolId,s.phone')
            ->join('school s','s.id = c.schoolId','left')
            ->join('brand b','b.id=c.brandId','left')
            ->where('c.id',$arr['classId'])
            ->find();
        $adviser=$this->myAdviserInfo($arr['userId'],$data['schoolId']);
        $array = [
            'userId'=>$arr['userId'],
            'classSchoolId'=>$data['id'],
            'classNum'=>$arr['classNum'],
            'className'=>$data['name'],
            'brandId'=>$data['brandId'],
            'shopId'=>$data['shopId'],
            'schoolId'=>$data['schoolId'],
            'adviserId'=>$adviser?$adviser['adviserId']:0,
            'status'=>0,
            'createTime'=>time(),
            'isDispose'=>0
        ];
        if($arr['type']==1){
            $ticketNum=$this->myStarticketOne($arr);
            if($ticketNum<$arr['classNum'])return false;
            $sum = $ticketNum-$arr['classNum'];
            $array['ticketId']=$arr['ticketId'];
            Db::startTrans();
            try{
                db('star_ticket_use')->insert($array);
                db('star_ticket')->update(['id'=>$arr['ticketId'],'remainNum'=>$sum]);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return false;
            }
        }else{
            $re = $this->myShareInfo($arr['userId']);
            $starNum=$re['starNum'];
            $num = $data['exchangeNum']*$arr['classNum'];
            if($num>$starNum)return false;
            $array['starNum']=$num;
            $r = db('star_use')->insertGetId($array);
            $a = new \app\common\model\UserStar();
            $a->changeStar($arr['userId'],$num,$r,2);
            if($r)$this->myStarUser(['userId'=>$arr['userId'],'starNum'=>$num]);
            $sum = $starNum-$num;
        }

        return [
                    'className'=>$data['name'],
                    'classNum'=>$arr['classNum'],
                    'brandName'=>$data['brandName'],
                    'address'=>$data['address'],
                    'phone'=>$data['phone'],
                    'num'=>$sum
                ];

   }

   //我的星星活动记录表
   private function myStarUser($arr)
   {
        $data = db('star_user_record')->where(['userId'=>$arr['userId'],'status'=>1,'starStatus'=>0])->find();
        $data1 = db('star_user_record')->where(['userId'=>$arr['userId'],'status'=>0,'starStatus'=>0])->find();
            if($data){
                $num = $data['starNum'] - $arr['starNum'];
                if($num<0){
                    $starNum = $data1['starNum']+$data['starNum']-$arr['starNum'];
                    Db::startTrans();
                    try{
                        db('star_user_record')->update(['id'=>$data['id'],'starNum'=>0,'starStatus'=>1]);
                        db('star_user_record')->update(['id'=>$data1['id'],'starNum'=>$starNum]);
                     Db::commit();
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();
                        return false;
                    }
                }else{
                        db('star_user_record')->update(['id'=>$data['id'],'starNum'=>$num]);
                    }
                }else{
                    $num = $data1['starNum'] - $arr['starNum'];
                     db('star_user_record')->update(['id'=>$data1['id'],'starNum'=>$num]);
                }

        return true;
   }

    //查询该用户在该校区是否有顾问
    public function myAdviserInfo($uid,$schoolId)
    {
        $data = db('adviser_user_school')->where([
                'userId'=>$uid,
                'schoolId'=>$schoolId,
                'isDelete'=>0
              ])->find();
        return $data;
    }

    //获得品牌下校区
    public function starFreeSchoolData($arr)
    {
        $field = -1;
        if($arr['lat']&&$arr['lng']){
          $field = 'ROUND(6378.138*2*ASIN(SQRT(POW(SIN((' . $arr['lat'] . '*PI()/180-latitude*PI()/180)/2),2)+COS(' . $arr['lat'] . '*PI()/180)*COS(latitude*PI()/180)*POW(SIN((' . $arr['lng'] . '*PI()/180-longitude*PI()/180)/2),2)))*1000)';
        }

        $data = db('school s')
                ->field('s.id as schoolId,s.name,s.address,b.name as brandName,'.$field.' AS distance')
                ->join('brand b','b.id=s.brandId','left')
                ->where(['brandId'=>$arr['brandId'],'cityCode'=>$arr['cityCode']])
                ->select();
        return $data;
    }

    //免费券兑换课程
    public function starFreeClassData($arr)
    {
        $data = db('star_free')->where('id',$arr['freeId'])->find();
        if(!$data||!$data['remainNum']||$data['endTime']<time())return false;
        $re = db('school s')
                ->field('s.id,s.name,s.address,b.name as brandName,s.shopId,s.brandId,s.phone')
                ->join('brand b','b.id=s.brandId','left')
                ->where('s.id',$arr['schoolId'])
                ->find();
        $r = db('star_free_use')->where(['freeId'=>$arr['freeId'],'brandId'=>$re['brandId']])->value('id');
        if($r)return 'classReceive';
        $adviser=$this->myAdviserInfo($arr['userId'],$re['id']);
        $array = [
            'userId'=>$arr['userId'],
            'freeId'=>$arr['freeId'],
            'brandId'=>$re['brandId'],
            'shopId'=>$re['shopId'],
            'schoolId'=>$re['id'],
            'adviserId'=>$adviser?$adviser['adviserId']:0,
            'status'=>0,
            'createTime'=>time(),
            'isDispose'=>0
        ];
        $num = $data['remainNum']-1;
        Db::startTrans();
            try{
                db('star_free_use')->insert($array);
                db('star_free')->update(['id'=>$data['id'],'remainNum'=>$num]);
            Db::commit();
            } catch (\Exception $e){
                // 回滚事务
                Db::rollback();
                return false;
            }
        return [
                'classNum'=>1,
                'brandName'=>$re['brandName'],
                'address'=>$re['address'],
                'phone'=>$re['phone']
                ];
    }

    //领券
    public function getStarTicketData($arr)
    {
        $data = db('star_rule_user')->where('id',$arr['starId'])->find();

        if(!$data||$data['status']!=1||$data['isEnd'])return false;
        if(($data['endTime']+2592000)<time())return false;
        $array = [
            'userId'=>$arr['userId'],
            'starId'=>$data['id'],
            'name'=>$data['name'],
            'num'=>$data['classNum'],
            'remainNum'=>$data['classNum'],
            'endTime'=>$data['endTime']+2592000,
            'createTime'=>time()
        ];
        Db::startTrans();
            try{
                db('star_ticket')->insert($array);
                db('star_rule_user')->update(['id'=>$data['id'],'status'=>2]);
            Db::commit();
            } catch (\Exception $e){
                // 回滚事务
                Db::rollback();
                return false;
            }
        return true;
    }

    //是否是分享人
    public function isShareUserData($uid)
    {
        $data = $this->myShareInfo($uid);
        if($data){
            $isShare = 1;
        }else{
            $isShare = 0;
        }
        return ['isShare'=>$isShare];
    }

    public function startStarData($uid)
    {
        $re = db('star_rule_user')->where(['userId'=>$uid,'isEnd'=>0,'status'=>1])->find();
        if($re)return 'starTicke';
        $res = db('star_rule_user')->where(['userId'=>$uid,'isEnd'=>0])->order('level desc')->find();
        if(!$res['status'])return false;
        $this->starRuleDate(['starRound'=>$res['starRound'],'userId'=>$uid]);
        return true;
    }

    private function starRuleDate($arr)
    {
        $data = db('star_rule')->where('isDelete=0')->select();
            $array = [];
            foreach ($data as $k => $v) {
                $array[]=[
                    'userId'=>$arr['userId'],
                    'starId'=>$v['id'],
                    'img'=>$v['img'],
                    'name'=>$v['name'],
                    'level'=>$v['level'],
                    'days'=>$v['days'],
                    'endTime'=>strtotime(date('Y-m-d 23:59:59',time()))+$v['days']*86400,
                    'starNum'=>$v['starNum'],
                    'status'=>0,
                    'classNum'=>$v['classNum'],
                    'createTime'=>time(),
                    'starRound'=>$arr['starRound']+1,
                    'isEnd'=>0
                ];
            }
            $array2 = [
                'userId'=>$arr['userId'],
                'starNum'=>0,
                'createTime'=>time(),
                'starRound'=>$arr['starRound']+1,
                'status'=>0,
                'starStatus'=>0,
                'endTime'=>strtotime(date('Y-m-d 23:59:59',time()))+(($this->satrTime())+30)*86400,
            ];

            Db::startTrans();
            try{
                db('star_rule_user')->where(['userId'=>$arr['userId'],'starRound'=>$arr['starRound']])->update(['isEnd'=>1]);
                db('star_user_record')->where(['userId'=>$arr['userId'],'starRound'=>$arr['starRound']])->update(['status'=>1]);
                db('star_rule_user')->insertAll($array);
                db('star_user_record')->insert($array2);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return false;
            }
            return true;
    }

}