<?php
/**
 * User: LiuTong
 * Date: 2017-09-07
 * Time: 16:37
 */

namespace app\school\controller;

use app\common\controller\AdminBase;
use app\school\model\Analysis as AnalysisModel;
use app\common\model\SchoolCommon as SchoolCommonModel;

class Analysis extends AdminBase
{
    private $schoolId = 0;

    /**
     * 获取校区id
     */
    public function _initialize()
    {
        parent::_initialize();
        $adminId = $this->admin_id;
        $SchoolCommonModel = new SchoolCommonModel();
        $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($adminId);
        $this->schoolId = $schoolId;
    }

    public function index()
    {
        return false;
    }

    /**
     * 签约统计
     * Date: 2017-09-08
     * @return \think\response\View
     */
    public function signCount()
    {
        $adminId = $this->admin_id;
        $SchoolCommonModel = new SchoolCommonModel();
        $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($adminId);

        $time = $this->sevenDay();
        $aTime = $time['aTime'];
        $bTime = $time['bTime'] + 86399;

        $AnalysisModel = new AnalysisModel();
        $data = $AnalysisModel->getSignCount($schoolId, $aTime, $bTime);

        $sevenEachDay = $this->sevenEachDay();
        $data = $this->makeData($data, $sevenEachDay);
        return view('signCount',
            [
                'data' => $data,
                'sevenEachDay' => $this->makeDateString($sevenEachDay),
                'aTime' => $this->makeDate($aTime),
                'bTime' => $this->makeDate($bTime),
                'cTime' => date('Y-m-d')
            ]
        );
    }

    /**
     * 按天查询
     * Date: 2017-09-08
     * @return json
     */
    public function signCountAjax()
    {
        $res['status'] = 'false';
        if (input('?post.aTime') && input('?post.bTime')) {
            $aTime = $this->makeTime(input('post.aTime'));
            $bTime = $this->makeTime(input('post.bTime'), 1);
            $timeB = $bTime + 86399;

            $adminId = $this->admin_id;
            $SchoolCommonModel = new SchoolCommonModel();
            $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($adminId);

            $AnalysisModel = new AnalysisModel();
            $retData = $AnalysisModel->getSignCount($schoolId, $aTime, $timeB);
            $date = $this->makeEachDate($aTime, $bTime);
            $data = [];
            if ($retData) {
                foreach ($date as $k => $v) {
                    foreach ($retData as $ke => $va) {
                        $data[$k] = 0;
                        if ($va['date'] == $v) {
                            $data[$k] = $va['num'];
                        }
                    }
                }
            } else {
                foreach ($date as $k => $v) {
                    $data[] = 0;
                }
            }

            $res['date'] = $date;
            $res['data'] = $data;
            $res['status'] = 'ok';
        } else {
            $res['status'] = 'err';
            $res['msg'] = '操作异常!';
        }

        echo json_encode($res);
    }

    /**
     * 按月查询
     * Date: 2017-09-08
     * @return json
     */
    public function signCountMonthAjax()
    {
        $res['status'] = 'false';
        if (input('?post.aTime') && input('?post.bTime')) {
            $aTime = $this->makeTime(input('post.aTime'));
            $bTime = input('post.bTime');

            $year = substr($bTime, 0, 4);
            $month = substr($bTime, 5, 2);
            $days = $this->getMonthDays($year, $month);

            $timeB = $this->makeTime($bTime . '-' . $days, 1);// xxxx-xx-28/29/30/31 23:59:59
            $bTime = $this->makeTime(input('post.bTime'));

            $adminId = $this->admin_id;
            $SchoolCommonModel = new SchoolCommonModel();
            $schoolId = $SchoolCommonModel->getSchoolIdFromAdminId($adminId);

            $AnalysisModel = new AnalysisModel();
            $retData = $AnalysisModel->getSignCountMonth($schoolId, $aTime, $timeB);
            $date = $this->makeEachMonth($aTime, $bTime);

            $data = [];
            if ($retData) {
                foreach ($date as $k => $v) {
                    foreach ($retData as $ke => $va) {
                        $data[$k] = 0;
                        if ($va['date'] == $v) {
                            $data[$k] = $va['num'];
                        }
                    }
                }
            } else {
                foreach ($date as $k => $v) {
                    $data[] = 0;
                }
            }

            $res['date'] = $date;
            $res['data'] = $data;
            $res['status'] = 'ok';
        } else {
            $res['status'] = 'err';
            $res['msg'] = '操作异常!';
        }

        echo json_encode($res);
    }

    /**
     * 生成每一月
     * Date: 2017-09-08
     * @param $aTime
     * @param $bTime
     * @return array|false|string
     */
    public function makeEachMonth($aTime, $bTime)
    {
        $data = [];
        while ($aTime < $bTime) {
            $t = date('Y-m', $aTime);
            $aTime = strtotime($t . ' +1 months');
            $data[] = $t;
        }
        return $data;
    }

    /**
     * 获取当月天数
     * Date: 2017-09-08
     * @param $year
     * @param $month
     * @return int
     */
    public function getMonthDays($year, $month)
    {
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        return $days;
    }

    /**
     * 生成每一天
     * Date: 2017-09-08
     * @param $aTime
     * @param $bTime
     * @return array|false|string
     */
    public function makeEachDate($aTime, $bTime)
    {
        $data = [];
        while ($aTime < $bTime) {
            $t = date('Y-m-d', $aTime);
            $aTime = strtotime($t . ' +1 days');
            $data[] = $t;
        }
        return $data;
    }

    /**
     * 生成默认数据
     * Date: 2017-09-08
     * @param $data
     * @param $date
     * @return bool|string
     */
    public function makeData($data, $date)
    {
        $str = '';
        if ($data) {
            $ret = [];
            foreach ($date as $k => $v) {
                foreach ($data as $ke => $va) {
                    $ret[$k] = 0;
                    if ($v == $va['date']) {
                        $ret[$k] = $va['num'];
                    }
                }
            }
        } else {
            foreach ($date as $k => $v) {
                $ret[] = 0;
            }
        }

        $str = $this->makeDateString($ret);
        return $str;
    }

    /**
     * 生成时间字符串
     * Date: 2017-09-08
     * @param $data
     * @return bool|string
     */
    public function makeDateString($data)
    {
        $str = '';
        foreach ($data as $k => $v) {
            $str .= "'" . $v . "',";
        }
        $str = substr($str, 0, -1);

        return $str;
    }

    /**
     * 生成日期
     * Date: 2017-09-08
     * @param $time
     * @return false|string
     */
    public function makeDate($time)
    {
        return date('Y-m-d', $time);
    }

    /**
     * 生成时间
     * Date: 2017-09-08
     * @param $date
     * @param int $type
     * @return false|int
     */
    public function makeTime($date, $type = 0)
    {
        $time = 0;
        if ($type == 1) {
            $time = strtotime($date . ' 23:59:59');
        } else {
            $time = strtotime($date . ' 00:00:00');
        }

        return $time;
    }

    /**
     * 七天开始和结束时间
     * Date: 2017-09-06
     * @return array
     */
    public function sevenDay()
    {
        $a = strtotime(date('Y-m-d'));
        $b = strtotime('-6 days', $a);
        return ['aTime' => $b, 'bTime' => $a];
    }

    /**
     * 七天日期
     * Date: 2017-09-08
     * @return array
     */
    public function sevenEachDay()
    {
        $data = [];
        for ($i = 6; $i > 0; $i--) {
            $t = date('Y-m-d', strtotime('-' . $i . 'days'));
            array_push($data, $t);
        }
        $data[7] = date('Y-m-d');

        return $data;
    }

    /**
     * 报名统计
     * Date: 2017-09-11
     * @return json
     */
    public function orderCourseCount()
    {
        $date = $this->dayMonth(0, 0, 1, 1);
        return view('orderCourseCount',
            [
                'aTime' => $date['date'][0],
                'bTime' => $date['date'][6],
                'cTime' => date('Y-m-d')
            ]
        );
    }

    /**
     * 报名统计Ajax
     * Date: 2017-09-11
     * @return json
     */
    public function orderCourseCountAjax()
    {
        $res['status'] = 'false';
        if (input('?post.type')) {
            $type = input('post.type');
            $aTime = input('post.aTime');
            $bTime = input('post.bTime');
            $date = $this->dayMonth($aTime, $bTime, $type, 1);

            $schoolId = $this->schoolId;
            $AnalysisModel = new AnalysisModel();
            $resData = [];
            if ($type == 1 || $type == 2) {
                $resData = $AnalysisModel->getOrderCourseCountDay($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            if ($type == 3) {
                $resData = $AnalysisModel->getOrderCourseCountMonth($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            if ($resData) {
                foreach ($date['date'] as $k => $v) {
                    $fillData[$k] = 0;
                    foreach ($resData as $ke => $va) {
                        if ($va['date'] == $v) {
                            $fillData[$k] = $va['num'];
                        }
                    }
                }

            } else {
                $fillData = [];
                foreach ($date['date'] as $k => $v) {
                    $fillData[$k] = 0;
                }
            }
            $date['data'] = $fillData;
            $res['status'] = 'ok';
            $res['data'] = $date;
            $res['debug_data'] = $resData;
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 根据请求返回天、月以及开始、结束时间戳
     * Date: 2017-09-11
     * @param int $aTime
     * @param int $bTime
     * @param int $type 1、默认七天 2、按天 3、按月
     * @param int $tag 0、默认 1、附带debug信息
     * @return array
     */
    public function dayMonth($aTime = 0, $bTime = 0, $type = 1, $tag = 0)
    {
        $data = [];
        $date = [];
        // 默认七天
        if ($type == 1) {
            for ($i = 6; $i > 0; $i--) {
                $t = date('Y-m-d', strtotime('-' . $i . 'days'));
                array_push($date, $t);
            }
            $date[6] = date('Y-m-d');

            $timeA = strtotime(date('Y-m-d'));
            $timeB = strtotime('-6 days', $timeA);

            list($timeA, $timeB) = [$timeB, $timeA + 86399];
        }

        // 按天
        if ($type == 2) {
            $timeA = strtotime($aTime);
            $timeB = strtotime($bTime);

            $date[] = $aTime;
            while ($timeA < $timeB) {
                $timeA = $timeA + 86400;
                $date[] = date('Y-m-d', $timeA);
            }

            $timeA = strtotime($aTime . ' 00:00:00');
            $timeB = strtotime($bTime . ' 23:59:59');
        }

        // 按月
        if ($type == 3) {
            $aTime = substr($aTime, 0, 7);
            $bTime = substr($bTime, 0, 7);

            $year = substr($bTime, 0, 4);
            $month = substr($bTime, 5, 2);
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $timeA = strtotime($aTime);
            $timeB = strtotime($bTime);

            $date = [];
            while ($timeA < $timeB) {
                $timeA = $timeA + 2073600;
                $date[] = date('Y-m', $timeA);
            }

            $timeA = strtotime($aTime . '-01');
            $timeB = strtotime($bTime . '-' . $days . ' 23:59:59');
        }

        $data = [
            'date' => $date,
            'time' => ['timeA' => $timeA, 'timeB' => $timeB]
        ];
        if ($tag) {
            $data['time_debug'] = ['timeA' => date('Y-m-d H:i:s', $timeA), 'timeB' => date('Y-m-d H:i:s', $timeB)];
        }
        return $data;
    }

    /**
     * 金额统计
     * Date: 2017-09-12
     * @return \think\response\View
     */
    public function schoolMoneyCount()
    {
        $date = $this->dayMonth(0, 0, 1, 1);
        return view('schoolMoneyCount',
            [
                'aTime' => $date['date'][0],
                'bTime' => $date['date'][6],
                'cTime' => date('Y-m-d')
            ]
        );
    }

    /**
     * 金额统计ajax
     * Date: 2017-09-12
     * @return json
     */
    public function schoolMoneyCountAjax()
    {
        $res['status'] = 'false';
        if (input('?post.type')) {
            $type = input('post.type');
            $aTime = input('post.aTime');
            $bTime = input('post.bTime');
            $date = $this->dayMonth($aTime, $bTime, $type, 1);

            $schoolId = $this->schoolId;
            $AnalysisModel = new AnalysisModel();
            $resData = [];
            if ($type == 1 || $type == 2) {
                $resData = $AnalysisModel->getSchoolMoneyDay($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            if ($type == 3) {
                $resData = $AnalysisModel->getSchoolMoneyMonth($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            if ($resData) {
                foreach ($date['date'] as $k => $v) {
                    $fillData[$k] = 0;
                    foreach ($resData as $ke => $va) {
                        if ($va['date'] == $v) {
                            $fillData[$k] = $va['num'] / 1000;
                        }
                    }
                }

            } else {
                $fillData = [];
                foreach ($date['date'] as $k => $v) {
                    $fillData[$k] = 0;
                }
            }
            $date['data'] = $fillData;
            $res['status'] = 'ok';
            $res['data'] = $date;
            $res['debug_data'] = $resData;
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * 课程购买统计
     * Date: 2017-09-12
     * fix Date: 2017-11-02
     * fix Content: 作废
     * @return \think\response\View
     */
    public function courseBuyCountObsolete()
    {
        $date = $this->dayMonth(0, 0, 1, 1);
        return view('courseBuyCount',
            [
                'aTime' => $date['date'][0],
                'bTime' => $date['date'][6],
                'cTime' => date('Y-m-d')
            ]
        );
    }

    /**
     * 课程购买统计ajax
     * Date: 2017-09-12
     * fix Date: 2017-11-02
     * fix Content: 作废
     * @return json
     */
    public function courseBuyAjaxObsolete()
    {
        $res['status'] = 'false';
        if (input('?post.type')) {
            $type = input('post.type');
            $aTime = input('post.aTime');
            $bTime = input('post.bTime');
            $date = $this->dayMonth($aTime, $bTime, $type, 1);

            $schoolId = $this->schoolId;
            $AnalysisModel = new AnalysisModel();
            $resData = [];
            if ($type == 1 || $type == 2) {
                $resData = $AnalysisModel->getCourseBuyDay($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            if ($type == 3) {
                $resData = $AnalysisModel->getCourseBuyMonth($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            if ($resData) {
                foreach ($date['date'] as $k => $v) {
                    $fillData[$k] = 0;
                    foreach ($resData as $ke => $va) {
                        if ($va['date'] == $v) {
                            $fillData[$k] = $va['num'];
                        }
                    }
                }

            } else {
                $fillData = [];
                foreach ($date['date'] as $k => $v) {
                    $fillData[$k] = 0;
                }
            }
            $date['data'] = $fillData;
            $res['status'] = 'ok';
            $res['data'] = $date;
            $res['debug_data'] = $resData;
        } else {
            $res['status'] = 'err';
            $res['msg'] = 'param missing';
        }

        echo json_encode($res);
    }

    /**
     * Date: 2017-11-02
     * 课程购买统计新
     * @return \think\response\View
     */
    public function courseBuyCount()
    {
        $date = $this->dayMonth(0, 0, 1, 1);
        return view('courseBuyCount',
            [
                'aTime' => $date['date'][0],
                'bTime' => $date['date'][6],
                'cTime' => date('Y-m-d')
            ]
        );
    }

    /**
     * Date: 2017-11-02
     * 课程购买统计ajax新 按课程统计
     */
    public function courseBuyAjax()
    {
        $res['status'] = 'false';
        if (input('?post.type')) {
            $type = input('post.type');
            $aTime = input('post.aTime');
            $bTime = input('post.bTime');
            $date = $this->dayMonth($aTime, $bTime, $type, 1);

            $schoolId = $this->schoolId;
            $AnalysisModel = new AnalysisModel();
            $resData = [];
            if ($type == 1 || $type == 2) {
                $resData = $AnalysisModel->getCourseBuyDay($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            if ($type == 3) {
                $resData = $AnalysisModel->getCourseBuyMonth($schoolId, $date['time']['timeA'], $date['time']['timeB']);
            }
            $num = [];
            $name = [];
            if ($resData) {
                foreach ($resData as $k => $v) {
                    array_push($num, $v['num']);
                    array_push($name, $v['name']);
                }
            }

            $res['status'] = 'ok';
            $res['data'] = [
                'num' => $num,
                'name' => $name
            ];
            $res['debug_data'] = $resData;

        } else {
            $res['status'] = 'err';
            $res['msg'] = 'Param missing';
        }

        echo json_encode($res);
    }

}