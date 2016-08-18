<?php

namespace Addons\Weicj\Controller;
use Home\Controller\AddonsController;
use Com\TPWechat;
use Com\JsSdkPay;
use Com\ErrCode;

class WeicjController extends AddonsController{


              public function index(){
                $map['id'] = I('id');
		$params['mp_id'] = $map['mp_id'] = get_mpid();
		

		hook('init_ucuser',$params);   //把消息分发到addons/ucuser/init_ucuser的方法中,初始化公众号粉丝信息
	
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                  $surl = get_shareurl();
                  if(!empty($surl)){
                      $this->assign ( 'share_url', $surl );
                  }

        $info = get_mpid_appinfo ( $params ['mp_id'] );

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
			
		$info = M('weicj')->where($map)->find();

		$info['pic1'] = get_cover_url($info['pic1']);
		$info['pic2'] = get_cover_url($info['pic2']);
		$info['pic3'] = get_cover_url($info['pic3']);
		$info['pic4'] = get_cover_url($info['pic4']);
		$info['pic5'] = get_cover_url($info['pic5']);
		$info['pic6'] = get_cover_url($info['pic6']);
		$info['clickpic'] = get_cover_url($info['clickpic']);

		if($info['andio']){
			$file = M ( 'file' )->where ( 'id=' . $info['andio'] )->find ();
			$filename = 'http://'.$_SERVER['HTTP_HOST']. '/Uploads/Download/' . $file ['savepath'] . $file ['savename'];
			$info['trueaudio'] = $filename;
		}else{	
			$info['trueaudio'] = $info['audio2'];
		}

		$this->assign('info',$info);

		//$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( );
	}

}