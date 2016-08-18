<?php

namespace Addons\Jssdk\Controller;
use Home\Controller\AddonsController;
use Com\TPWechat;
use Com\JsSdkPay;
use Com\ErrCode;

class JssdkController extends AddonsController{

    // JSSDK 演示页面
    public function index() {

        //JSSDK 初始部分
        $param ['mp_id'] = I('mp_id');

        //$param ['id'] = I('id');                                          //如有插件中数据id,分享url中应加入id参数
        //$url = addons_url ( 'Jssdk://Jssdk/index', $param );  //分享的url需要和自定义回复入口url保持相同
       $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $info = get_mpid_appinfo ( $param ['mp_id'] );

        $options['appid'] = $info['appid'];    //初始化options信息
        $options['appsecret'] = $info['secret'];
        $options['encodingaeskey'] = $info['encodingaeskey'];
        $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $addon_config = get_addon_config('Jssdk');
        $this->assign ( 'addon_config', $addon_config );

        //微信支付部分
        //此处可以动态获取数据库中的MCHID和KEY
        $jssdkpay = new JsSdkPay($options);
        $jssdkpay->MCHID = "";                            // 动态MCHID;微信支付分配的商户号
        $jssdkpay->KEY = "";        // 动态KEY;

        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $jssdkpay->parameters['openid'] = get_openid();          //trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。
        $jssdkpay->parameters['body']="订单支付";                     //商品或支付单简要描述
        $jssdkpay->parameters['out_trade_no'] = "outtradeno".time();  //商户系统内部的订单号,32个字符内、可包含字母,不可重复
        $jssdkpay->parameters['total_fee'] = 100;                      //收款金额，此处单位为分 出现小数点接口报错必须是整数
        $jssdkpay->parameters['notify_url'] = 'http://test.uctoo.com/index.php/addon/Jssdk/Jssdk/alarmnotify';   //接收微信支付异步通知回调地址
        $jssdkpay->parameters['trade_type'] = "JSAPI";               //取值如下：JSAPI，NATIVE，APP
        $jssdkpay->parameters['spbill_create_ip'] = get_client_ip(); //APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。

        //以下非必填参数根据需要添加
        //$jssdkpay->parameters['device_info'] = "013467007045764";            //微信支付分配的终端设备号，商户自定义
        //$jssdkpay->parameters['detail'] = "UCToo   蓝色";                     //商品名称明细列表
        //$jssdkpay->parameters['attach'] = "说明";                             //附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        //$jssdkpay->parameters['fee_type'] = "CNY";                           //符合ISO 4217标准的三位字母代码，默认人民币：CNY
        //$jssdkpay->parameters['time_start'] = "20091225091010";              //订单生成时间，格式为yyyyMMddHHmmss
        //$jssdkpay->parameters['time_expire'] = "20091227091010";             //订单失效时间，格式为yyyyMMddHHmmss
        //$jssdkpay->parameters['goods_tag'] = "WXG";                          //商品标记，代金券或立减优惠功能的参数
        //$jssdkpay->parameters['product_id'] = "12235413214070356458058";     //trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。


        $jssdkpay->prepay_id = $jssdkpay->getPrepayId();                //微信生成的预支付回话标识，用于后续接口调用中使用，该值有效期为2小时

        //=========步骤3：使用jsapi调起支付============

        $jsApiParameters = $jssdkpay->getParameters();

        //JSSDK 用户支付完成后的一些系统操作
        $param1 ['dcnum'] = $jssdkpay->parameters['out_trade_no'];
        $param1 ['openid'] = $jssdkpay->parameters['openid'];

        $ajaxurl = addons_url ( 'Jssdk://Jssdk/orderpaid', $param1 );  //用户支付完成后，在微信支付返回alarmnotify之前（不保证时序），可以通过ajax调用，进行一些预处理操作

        $jsApiParameters = substr($jsApiParameters,1,-1).",success: function (res) {
                // 支付成功后的js回调函数
               
                }";

        $this->assign("jsApiParameters",$jsApiParameters);        //向页面传整理好的调起支付参数

        $this->display ();
    }

    //[postajax]订单已支付
    public function orderpaid(){

        $token = get_token();
        $dcnum = I('dcnum');
        $openid = get_openid();

        $map["token"] = $token;
        $map["openid"] = $openid;
        $map["dcnum"] = $dcnum;

        //未支付状态的订单设置为已支付状态，没有安全认证，仅代表状态，通过微信支付订单查询接口获得准确状态
        $dddata = M ("mall_order")->where ($map)->order ( 'id asc' )->find ();

        if($dddata["statekz"] == "0"){
            //未支付，设置为已支付
            M ("mall_order")->where ($map)->save(array("statekz"=>1));
        }

        echo "ok";
    }

    //支付完成接收支付服务器返回通知
    public function alarmnotify(){
        $post_data = $GLOBALS ['HTTP_RAW_POST_DATA'];
        \Think\Log::record ( "alarmnotify". $post_data  );

        $result = xmlToArray($post_data);
        $resp = true;
        $respdata["return_code"] = "SUCCESS";
        $respdata["return_msg"] = "";

        $map["appid"] = $result["appid"];
        $map["mch_id"] = $result["mch_id"];
        $map["openid"] = $result["openid"];
        $map["out_trade_no"] = $result["out_trade_no"];

       // while (true) {  //alarmnotify和orderpaid调用时序无保证，未将历史订单的支付流水写入订单表就一直循环

        $orderdata = M ( "mall_order_his" )->where ( $map )->order ( 'id DESC' )->find();

        if($orderdata["id"] != ""){
            //已经记录过的订单数据

            $map1["openid"] = $orderdata["openid"];
            $map1["dcnum"] = $orderdata["out_trade_no"];

            //TODO:进行安全校验，修订订单支付状态
            $dddata = M ("ml_mall_order")->where ($map1)->order ( 'id asc' )->find ();
            if($dddata["statekz"] == "0" || $dddata["statekz"] == "1"){
                //未支付，设置为已支付，没有记录交易流水的将交易流水写入订单表
                M ("mall_order")->where ($map1)->save(array("statekz"=>1,"transaction_id"=>$orderdata["transaction_id"]));
            }
            if($resp == true){//根据不同的错误设置返回数据
                return arrayToXml($respdata);
            }else{
                return arrayToXml($respdata);
            }

        }else{
            $order = M ( "mall_order_his" );
            /* 保存附件 */
            if($order->create($result) && $order->add()){
                $resp = true;
            } else {
                $resp = false;
                $respdata["return_msg"] = "保存订单数据错误";
            }
        }
    }
   // }
}
