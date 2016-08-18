<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2015 http://uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Patrick <contact@uctoo.com>
// +----------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;
use Com\TPWechat;
use Com\Wxpay\lib\WxPayConfig;
use Com\Wxpay\lib\WxPayOrderQuery;
use Com\Wxpay\lib\WxPayApi;
/**
 * 微信交互控制器，中控服务器
 * 主要获取和反馈微信平台的数据，分析用户交互和系统消息分发。
 */
class WeixinController extends Controller {

    private $options = array(
          'token'=>APP_TOKEN, //填写你设定的key
          'encodingaeskey'=>'', //填写加密用的EncodingAESKey
          'appid'=>'', //填写高级调用功能的app id
          'appsecret'=>'' //填写高级调用功能的密钥
      );

    private $member_public;   //公众号

    public function _initialize(){
        /* 读取数据库中的公众号信息初始化微信类 */
    }


     /**
     * 微信消息接口入口
     * 所有发送到微信的消息都会推送到该操作
     * 所以，微信公众平台后台填写的api地址则为该操作的访问地址
     * 在mp.weixin.qq.com 开发者中心配置的 URL(服务器地址)  http://域名/index.php/home/weixin/index/id/member_public表的id.html
     */
	public function index($mp_id = '') {
        //设置当前上下文的公众号mp_id
        $mp_id = get_mpid($mp_id);//初始化公众号
        $map['mp_id'] = $mp_id;
        $this->member_public = M('MemberPublic')->where($map)->find();
        $this->options['appid'] = $this->member_public['appid'];    //初始化options信息
        $this->options['appsecret'] = $this->member_public['secret'];
        $this->options['encodingaeskey'] = $this->member_public['encodingaeskey'];
        $weObj = new TPWechat($this->options);
        $weObj->valid();
        $weObj->getRev();
        $data = $weObj->getRevData();
        $type = $weObj->getRevType();
        $ToUserName = $weObj->getRevTo();
        $FromUserName = $weObj->getRevFrom();
        $params['weObj'] = &$weObj;
        $params['mp_id'] = $this->member_public['mp_id'];
        $params['weOptions'] = $this->options;

        //如果被动响应可获得用户信息就记录下
        if (! empty ( $ToUserName )) {
            get_token ( $ToUserName );
        }
        if (! empty ( $FromUserName )) {
          $oid =  get_openid($FromUserName);
        }

        hook('init_ucuser',$params);   //把消息分发到addons/ucuser/init_ucuser的方法中,初始化公众号粉丝信息

        $map['openid'] = get_openid();
        $map['mp_id'] = $params['mp_id'];
        $ucuser = D('Ucuser');
        $user = $ucuser->where($map)->find();       //查询出公众号的粉丝
        $fsub = $user["subscribe"];               //记录首次关注状态

        //与微信交互的中控服务器逻辑可以自己定义，这里实现一个通用的
        switch ($type) {
            //事件
            case TPWechat::MSGTYPE_EVENT:         //先处理事件型消息
                $event = $weObj->getRevEvent();

                switch ($event['event']) {
                    //关注
                    case TPWechat::EVENT_SUBSCRIBE:

                        //二维码关注
                        if(isset($event['eventkey']) && isset($event['ticket'])){

                            //普通关注
                        }else{

                        }

//                        $weObj->reply();
                        //获取回复数据
                        $where['mp_id']=get_mpid();
                        $where['mtype']= 1;
                        $where['statu']= 1;
                        $model_re = M('replay_messages');
                        $data_re=$model_re->where($where)->find();
                        $params['type']=$data_re['type'];

                        $params['replay_msg']=D('Mpbase/Autoreply')->get_type_data($data_re);

                        D('Home/Wxmsg')->wxmsg($params);
//                        hook('wxmsg',$params);

                        $weObj->reply();

                    if(!$user["subscribe"]){   //未关注，并设置关注状态为已关注
                        $user["subscribe"] = 1;     
                        $ucuser->where($map)->save($user);
                    }
			        hook('welcome', $params);   //把消息分发到实现了welcome方法的addons中,参数中包含本次用户交互的微信类实例和公众号在系统中id

                        exit;
			break;
                    //扫描二维码
                    case TPWechat::EVENT_SCAN:

                        break;
                    //地理位置
                    case TPWechat::EVENT_LOCATION:

                        break;
                    //自定义菜单 - 点击菜单拉取消息时的事件推送
                    case TPWechat::EVENT_MENU_CLICK:

//                        hook('keyword',$params);   //把消息分发到实现了keyword方法的addons中,参数中包含本次用户交互的微信类实例和公众号在系统中id
//                        $weObj->reply();           //在addons中处理完业务逻辑，回复消息给用户
                        $where['keywork']=array('like', '%' . $data['Content'] . '%');
                        $where['mtype']= 3;
                        $where['statu']= 1;
                        $where['mp_id']=get_mpid();
                        $model_re = M('replay_messages');
                        $data_re=$model_re->where($where)->find();
                        $params['type']=$data_re['type'];
                        $params['replay_msg']=D('Mpbase/Autoreply')->get_type_data($data_re);

                        D('Home/Wxmsg')->wxmsg($params);
//                        hook('wxmsg',$params);

                        $weObj->reply();  //在addons中处理完业务逻辑，回复消息给用户
                        break;


                        break;
                    //自定义菜单 - 点击菜单跳转链接时的事件推送
                    case TPWechat::EVENT_MENU_VIEW:

                        break;
                    //自定义菜单 - 扫码推事件的事件推送
                    case TPWechat::EVENT_MENU_SCAN_PUSH:

                        break;
                    //自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
                    case TPWechat::EVENT_MENU_SCAN_WAITMSG:

                        break;
                    //自定义菜单 - 弹出系统拍照发图的事件推送
                    case TPWechat::EVENT_MENU_PIC_SYS:

                        break;
                    //自定义菜单 - 弹出拍照或者相册发图的事件推送
                    case TPWechat::EVENT_MENU_PIC_PHOTO:

                        break;
                    //自定义菜单 - 弹出微信相册发图器的事件推送
                    case TPWechat::EVENT_MENU_PIC_WEIXIN:

                        break;
                    //自定义菜单 - 弹出地理位置选择器的事件推送
                    case TPWechat::EVENT_MENU_LOCATION:

                        break;
                    //取消关注
                    case TPWechat::EVENT_UNSUBSCRIBE:
                    if($user["subscribe"]){
                        $user["subscribe"] = 0;     //取消关注设置关注状态为取消
                        $ucuser->where($map)->save($user);
                    }

                        break;
                    //群发接口完成后推送的结果
                    case TPWechat::EVENT_SEND_MASS:

                        break;
                    //模板消息完成后推送的结果
                    case TPWechat::EVENT_SEND_TEMPLATE:

                        break;
                    default:

                        break;
                }
                break;
            //文本
            case TPWechat::MSGTYPE_TEXT :

                $where['keywork']=array('like', '%' . $data['Content'] . '%');
                $where['mtype']= 3;
                $where['statu']= 1;
                $where['mp_id']=get_mpid();
                $model_re = M('replay_messages');
                $data_re=$model_re->order('time desc')->where($where)->find();
//              关键字匹配失败进入自动回复
                if(!$data_re){
                    unset($where);
                    $where['mtype']= 2;
                    $where['statu']= 1;
                    $where['mp_id']=get_mpid();
                    $data_re=$model_re->order('time desc')->where($where)->find();
                }

                $params['type']=$data_re['type'];
                $params['replay_msg']=D('Mpbase/Autoreply')->get_type_data($data_re);
                D('Home/Wxmsg')->wxmsg($params);
//                hook('wxmsg',$params);

                $weObj->reply();  //在addons中处理完业务逻辑，回复消息给用户
                break;
            //图像
            case TPWechat::MSGTYPE_IMAGE :

                break;
            //语音
            case TPWechat::MSGTYPE_VOICE :

                break;
            //视频
            case TPWechat::MSGTYPE_VIDEO :

                break;
            //位置
            case TPWechat::MSGTYPE_LOCATION :

                break;
            //链接
            case TPWechat::MSGTYPE_LINK :

                break;
            default:

                break;
        }

        // 记录日志
        if (C('DEVELOP_MODE')) { // 是否开发者模式
            addWeixinLog ( $data, $GLOBALS ['HTTP_RAW_POST_DATA'] );
        }
	}



	/*
	 * 微信支付统一回调接口 后续逻辑可查看 PayNotifyCallBackController 中 NotifyProcess() 说明
	 */
	public function notify(){
		$rsv_data = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$result   = xmlToArray($rsv_data);
		addWeixinLog(var_export($rsv_data, true), $GLOBALS ['HTTP_RAW_POST_DATA']);
		//回复公众平台支付结果
		$notify       = new PayNotifyCallBackController();
		$map["appid"] = $result["appid"];
		$map["mchid"] = $result["mch_id"];
		$info         = M('member_public')->where($map)->find();
		//获取公众号信息，jsApiPay初始化参数
		$cfg = array(
			'APPID'      => $info['appid'],
			'MCHID'      => $info['mchid'],
			'KEY'        => $info['mchkey'],
			'APPSECRET'  => $info['secret'],
			'NOTIFY_URL' => $info['notify_url'],
		);
		WxPayConfig::setConfig($cfg);
		$notify->Handle(false);
	}

	/*
	 * 查询微信支付的订单
	 * 注意 这里未做权限判断
	 */
	public function orderquery()
	{
		$id = I('id','','intval');
		$order                      = M("Order");
		if(empty($id)
		||!($odata = $order->where('id = '. $id )->find()))
		{
			$this->error('该支付记录不存在');
		}
		$map["mp_id"] = $odata["mp_id"];
		$info         = M('member_public')->where($map)->find();
		//获取公众号信息，jsApiPay初始化参数
		$cfg = array(
			'APPID'      => $info['appid'],
			'MCHID'      => $info['mchid'],
			'KEY'        => $info['mchkey'],
			'APPSECRET'  => $info['secret'],
			'NOTIFY_URL' => $info['notify_url'],
		);
		WxPayConfig::setConfig($cfg);
		$input = new WxPayOrderQuery();
		$input->SetOut_trade_no($odata['order_id']);
		$result = WxPayApi::orderQuery($input);
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& array_key_exists("trade_state", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS"
			&& $result["trade_state"] == "SUCCESS")
		{
			// $odata['module'] = Shop 则在D('ShopOrder','Logic')->AfterPayOrder() 内处理后续逻辑
			$class = parse_res_name($odata['module'].'/'.$odata['module'].'Order','Logic');
			if(class_exists($class) &&
				method_exists($class,'AfterPayOrder'))
			{
				$m = new $class();
				$m->AfterPayOrder($result,$odata);
			}
			$this->success('已支付');
		}
		$this->error((empty($result['trade_state_desc'])?'未支付':$result['trade_state_desc']));
	}
}