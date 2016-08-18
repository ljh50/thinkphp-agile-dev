<?php
namespace Home\Controller;

use Com\Wxpay\lib\WxPayApi;
use Com\Wxpay\lib\WxPayNotify;
use Com\Wxpay\lib\WxPayOrderQuery;
use Com\TPWechat;
use Think\Hook;

class PayNotifyCallBackController extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$data = WxPayApi::orderQuery($input);
		if(array_key_exists("return_code", $data)
			&& array_key_exists("result_code", $data)
			&& array_key_exists("trade_state", $data)
			&& $data["return_code"] == "SUCCESS"
			&& $data["result_code"] == "SUCCESS"
			&& $data["trade_state"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理方法，成功的时候返回true，失败返回false，处理商城订单
	public function NotifyProcess($data, &$msg)
	{

		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		// 保存微信支付订单流水
        $transaction = M('transaction');
        $transaction->data($data)->add();
		// 微信通用订单保存
		$order                      = M("Order");
		$odata                      = $order->where('order_id = "' . $data["out_trade_no"] . '"')->find();
		$odata['buyer_openid']      = $data['openid'];
		$odata['order_status']      = 5;//完成
		$odata['order_total_price'] = $data['total_fee'];
		$odata['trans_id'] = $data['transaction_id'];
		$order->save($odata); // 写入数据


		// $odata['module'] = Shop 则在D('ShopOrder','Logic')->AfterPayOrder() 内处理后续逻辑
		$class = parse_res_name($odata['module'].'/'.$odata['module'].'Order','Logic');
		if(class_exists($class) &&
			method_exists($class,'AfterPayOrder'))
		{
			$m = new $class();
			$m->AfterPayOrder($data,$odata);
		}

		return true;
	}
}

