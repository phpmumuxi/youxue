<?php
/**
 * User: LiuTong
 * Date: 2017-10-10
 * Time: 10:49
 */

namespace app\api\model;

use think\Model;

class ChildrenTips extends Model
{
    /**
     * Date: 2017-10-10
     * 育儿师按类别或按内容搜索列表
     * @param $type
     * @param $title
     * @return mixed
     */
    public function getChildrenTipsList($type, $title)
    {
        $where['p.isDelete'] = 0;
        if ($title) {
            $where['p.title'] = ['like', '%' . $title . '%'];
        }
        if ($type != -1) {
            $where['p.type'] = $type;
        }
        $data = db('parent p')
            ->field('p.id,p.type,p.sex,p.title,pti.typeName')
            ->join('parent_type_img pti','pti.type=p.type')
            ->where($where)
            ->select();
        return $data;
    }

    /**
     * Date: 2017-10-10
     * 获取育儿师详细内容
     * @param $id
     * @return mixed
     */
    public function getChildrenTipsContent($id)
    {
        $where['id'] = $id;
        $data = db('parent')
            ->where($where)
            ->value('content');
        return $data;
    }

    /**
     * Date: 2017-10-11
     * 育儿师类型图片列表
     * @return mixed
     */
    public function getChildrenTisTypeImages()
    {
        $where['isDelete'] = 0;
        $data = db('parent_type_img')
            ->field('type,img,divideType,typeName')
            ->where($where)
            ->order('divideType asc,type asc')
            ->select();
        return $data;
    }


    public function parentsHtmlDate($id)
    {
        $data = db('parent')->field('content')->find($id);
        if ($data) {
            $data['content'] = htmlspecialchars_decode($data['content']);
        }
        return $data;
    }
}