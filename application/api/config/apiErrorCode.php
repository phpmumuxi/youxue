<?php
/**
 * Created by PhpStorm.
 * User: Liu
 * Date: 2017-07-05
 * Time: 14:05
 */

// api 错误码
return [
    'token' => [200, 'token丢失'],
    'tokenFalse' => [201, 'token错误'],
    'success' => [100, '操作成功'],
    'failure' => [400, '操作失败'],

    'phone' => [10001, '请输入正确的手机号码'],
    'password' => [10002, '密码不能少于6位，多于16位'],
    'login' => [10003, '登录失败，账户或者密码错误'],
    'havuse' => [10004, '当前账户已注册'],
    'username' => [10005, '用户名不能少于2位，大于6位'],
    'name' => [10006, '姓名不能少于2位，大于6位'],
    'code' => [10007, '验证码错误或已失效'],
    'usermiss' => [10009, '帐号不存在'],
    'passWordError' => [10010, '旧密码错误'],
    'error' => [10011, '未知错误'],
    'handleError' => [10012, '自动验证数据出错'],
    'abandon' => [10013, '该接口已经弃用，请勿使用'],
    'recom' => [10014, '已存在推荐人，不可重复添加'],
    'passwordT' => [10015, '两次密码输入不一致'],
    'passwordW' => [10016, '新密码与旧密码一致'],
    'isAdviser' => [10017, '当前用户不是顾问'],
    'userNo' => [10018,'该用户不是同城优学注册会员'],
    'shareYes' => [10019,'已存在分享人'],
    'phoneError' => [10020,'当前手机号已被注册过'],

    'paramMiss' => [10100, '缺少参数'],
    'typeError' => [10101, '类型错误'],
    'jurisdic' => [10102, '权限不够'],
    'paramError' => [10103, '参数错误'],

    'weiError' => [10200, '微信支付回调错误'],

    'bankCardError' => [11002, '银行卡错误'],
    'bankCardMiss' => [11003, '银行卡丢失'],
    'deleteBankError' => [11004, '删除银行卡失败'],
    'cardHad' => [11005, '银行卡已经存在，不能重复添加'],

    'msgApiError' => [20002, '短信接口返回失败'],
    'msgError' => [20003, '当前不可获取验证码，请稍后重试'],
    'phoneCodeError' => [20004, '短信验证失败'],

    'fileMax' => [30001, '上传文件超出大小限制'],
    'fileType' => [30002, '上传文件类型不对'],
    'fileFail' => [30003, '上传文件失败'],
    'fileNo' => [30004, '没找到上传文件'],

    'replayError' => [40001, '回复信息不存在'],
    'replaySelf' => [40002, '当前信息不能回复'],

    'userNoJuri' => [40100, '您还不是vip或者顾问，不能发布妈妈约活动'],
    'dateMiss' => [40101, '当前妈妈约活动不存在'],
    'signDateExit' => [40102, '已经报名当前妈妈约活动'],
    'babyNum' => [40103, '宝贝数量没填'],
    'parentNum' => [40104, '父母数量没填'],
    'dateNoChange' => [40105, '当前活动不能操作'],
    'momImgError' => [40106, '您还没有权限上传图片到此活动中'],
    'momStateError' => [40107, '当前活动不能上传图片'],
    'dateEnd' => [40108, '当前活动已经截至'],
    'dataMax' => [40109, '当前报名人数已超出规定人数'],
    'momDateUser' => [40110, '当前用户是活动发起者，不能报名'],
    'momDateShare' => [40111, '该活动已分享过了'],
    'momDateNoJoin' => [40112, '未报名参加该活动'],
    'momDateStatus' => [40113, '该活动报名已截至'],
    'momDateShareNo' => [40114, '该活动未分享过'],

    'shopMiss' => [40201, '商家丢失'],

    'classMiss' => [40301, '课程丢失'],
    'classReceive' => [40302, '免费体验券同一品牌只能免费体验一次'],
    'orderClassMiss' => [40303, '当前课程订单不存在'],
    'classNoPay' => [40304, '当前课程订单不能支付'],
    'vipClassError' => [40305, '体验券丢失'],
    'useVipClassError' => [40306, '此体验券已被使用'],

    'goodsMiss' => [40400, '商品丢失'],
    'goodsError' => [40401, '当前商品没有款式'],
    'cartsMiss' => [40402, '购物车丢失'],
    'goodsStockMiss' => [40403, '商品库存不足'],


    'orderMiss' => [40500, '订单丢失'],
    'schoolSelectError' => [40501, '学校选择错误'],
    'selectedSchool' => [40502, '当前学校已经选择过了，不能重复选择'],

    'payNoMoney' => [40600, '当前余额不足'],
    'payNoDou' => [40601, '当前豆豆不足'],
    'addressMiss' => [40602, '用户地址不存在'],
    'payd' => [40603, '当前订单不能支付'],
    'payNoVou' => [40604, '当前特权券不足'],

    'douNoCencel' => [40701, '当前豆豆不能取消上架'],
    'douNoBuy' => [40702, '当前豆豆不能购买'],
    'douOrderMiss' => [40703, '豆豆订单丢失'],

    'memberMoney' => [40704, '当前用户不能会员奖励'],
    'teacherMoney' => [40705, '当前用户不能老师奖励'],
    'adviserMoney' => [40706, '当前用户不能顾问奖励'],

    'buyMiss' => [41000, '当前团购不存在'],
    'buyOrderMiss' => [41001, '当前团购订单不存在'],
    'noBuy' => [41002, '没有团购券'],
    'brandMiss' => [41003, '品牌丢失'],
    'schoolMiss' => [41004, '学校丢失'],
    'buyTimed' => [41005, '团购已经过期'],
    'buyUsed' => [41006, '当前校区只能使用一张团购券'],
    'buyBrand' => [41007, '其中品牌有被体验过，不可重复选择'],
    'buyStatus' => [41008, '当前订单已支付过'],
    'buyStatusUse' => [41009, '该团购券已使用过'],

    'orderMoney' => [41100, '当前订单丢失'],
    'orderNoPay' => [41101, '当前不能支付'],
    'orderMoneyError' => [41102, '支付金额不能超过当前订单金额'],
    'orderLeave' => [41103, '剩余金额小于当前支付金额'],
    'ordermoneyPay' => [41104, '当前支付金额输入有误'],

    'memberMiss' => [41200, '当前会员不存在'],
    'orderMember' => [41201, '会员订单丢失'],

    'safePass' => [41300, '支付密码设置错误'],
    'safePassError' => [41301, '支付密码错误'],

    'moneyMin' => [41400, '提现金额不能少于100'],

    'memberLevel' => [41500, '会员等级不够'],
    'memberEndTime' => [41501, '会员已经到期了'],

    'cmbcMember' => [41502, '您不是招商用户，不能购买招商会员'],
    'nameError' => [41503, '当前用户无详情'],

    'playMiss' => [42000, '当前体育玩乐券不存在'],
    'orderPlayMiss' => [42001, '当前体育玩乐券订单不存在'],
    'playNoPay' => [42002, '当前体育玩乐券订单不能支付'],
    'noPlay' => [42003, '没有体育玩乐券'],

    'apiBaiduError' => [50000, '百度地图有问题了'],
    'apiBaiduAddError' => [50001, '当前地址找不到'],
    'apiBaiduLocaError' => [50002, '当前定位找不到'],

    'bankCard' => [80001, '银行卡号不符'],
    'bankType' => [80002, '银行卡类型错误'],
    'shopName' => [80003, '商户名称不能为空'],
    'bankUserName' => [80004, '银行卡持卡人姓名不能为空'],
    'bankName' => [80005, '银行卡开户行名称不能为空'],
    'SName' => [80006, '当前商户名称已存在'],
    'cityCode' => [80007, '地址错误'],

    'adminName' => [80100, "用户名不能少于5位，大于20位"],
    'nickName' => [80101, "昵称不能少于2位，大于6位"],
    'adminExist' => [80102, "当前用户名已存在"],
    'id' => [80103, "ID非法"],

    'brandName' => [80200, '品牌名称不能为空'],
    'fileImg' => [80201, '未选择上传图片'],

    'classMoney' => [80300, '课程价格不能为空'],
    'classTitle' => [80301, '课程名称不能为空'],

    'schoolName' => [80400, '校区不存在'],

    'goodsName' => [80500, '商品名称不能为空'],
    'goodsMoney' => [80501, '商品价格不能为空'],
    'goodsMoneyCost' => [80502, '商品成本价不能为空'],
    'goodsSpecif' => [80503, '商品规格不能为空'],
    'goodsColor' => [80504, '商品颜色不能为空'],
    'goodsStock' => [80505, '商品库存不能为空'],
    'goodsNorms' => [80506, '该商品颜色与规格已存在'],
    'goodsFree' => [80507, '该商品不可重复领取'],
    'goodsShop' => [80508, '不满足领取该商品的条件'],

    'loginError' => [80600, '用户名或密码错误'],
    'adviserNull' => [80601, '请选择顾问'],

    'remarksName' => [80700, '用户备注姓名不能为空'],
    'adviserExist' => [80701, '该顾问已存在本校区'],
    'adviserClient' => [80702, '该顾问名下存在客户'],

    'playTypeName' => [80800, '体育玩乐券类别名字不能为空'],
    'playTypeMoney' => [80801, '体育玩乐券类别单价不合法'],
    'playTypeDate' => [80802, '体育玩乐券类别有效天数不合法'],
    'playTypeDelete' => [80803, '体育玩乐券类别下不为空，不可被删除'],
    'playName' => [80804, '体育玩乐券名字不能为空'],
    'playMoney' => [80805, '体育玩乐券价格不合法'],
    'playDate' => [80806, '体育玩乐券有效天数不合法'],
    'playNum' => [80807, '体育玩乐券张数不合法'],
    'playType' => [80808, '体育玩乐券类别有误'],

    'cmbcNum' => [80809, '生成招商码个数必须输入整数'],
    'cmbcTerm' => [80810, '招商码有效天数必须输入整数'],
    'cmbcDel' => [80811, '该招商码不能符合删除条件'],
    'choiceTime' => [80812, '时间选择有误'],
    'cmbcCode' => [80813, '招商码有误'],
    'cmbcCodeTime' => [80814, '招商码已过期'],
    'cmbcUser' => [80815, '已经是招商用户'],

    'vipFreeMiss' => [80900, 'vip免费商品丢失'],
    'vipFreeGet' => [80901, '已领取过此免费特权,请您查看领取其他超低折扣特权。'],
    'appVersionError' => [80902, '版本号错误'],
    'appApkError' => [80903, '安卓包名称错误'],
    'depositError' => [80904, '无可操作记录'],
    'vipFreeUse' => [80908, '该特权已使用过'],

    'posOrderError' => [80905, '该pos机订单已被使用过'],
    'posError' => [80906, '该pos机订单不符合要求'],
    'posDel' => [80907, '该pos机订单已作废'],

    'goodsFreeIdError' => [81000, '免费领取商品id丢失'],
    'goodsFreeSchoolidError' => [81001, '免费领取商品校区id丢失'],
    'goodsFreeRuleidError' => [81002, '免费领取商品规格id丢失'],
    'goodsFreeDoudouError' => [81003, '免费领取商品doudou丢失'],
    'goodsFreeVipError' => [81004, '不是VIP不能领取vip免费领'],
    'goodsFreeTakeError' => [81005, '领取失败'],
    'goodsFreeShopidError' => [81006, '免费领取商品商铺id丢失'],
    'goodsFreeTakeOverError' => [81007, '商品已抢完'],
    'goodsFreeTakeTakeError' => [81008, '该商品已领取过'],
    'goodsFreeGetIdError' => [81009, '已领商品记录id丢失'],
    'goodsFreeUseError' => [81010, '已领商品使用失败'],

    'LngError' => [82001, '经度lng丢失'],
    'LatError' => [82002, '纬度lat丢失'],

    'welfareGoodsOff' => [83001,'福利专区商品下架'],
    'welfareGoodsTookOff' => [83002,'福利专区商品已领完'],
    'welfareGoodsExchangeOff' => [83003,'福利专区商品已兑换完'],
    'WelfareTookAll' => [83004,'福利专区商品免费领半年内已领完'],
    'WelfareTook' => [83005,'福利专区商品免费领半年内已领过'],
    'welfareMemberLevelTimeOff' => [83006,'福利专区会员过期'],
    'welfareLevelNoPermission' => [83007,'福利专区当前vip无权限'],
    'welfareDoudouOff' => [83008,'福利专区兑换豆豆不足'],
    'welfareTakeOutTime' => [83009,'福利专区实物领取超过限定时间'],

    'starTicke'=>[84000,'星座券未领完，请先全部领完后再开启新的活动'],


    //pos机错误码
    'posOrder' => ['1017', '该渠道无此订单'],
    'posSuccess' => ['0000', '处理成功'],
    'isPost' => ['9999', '其他错误']

];
