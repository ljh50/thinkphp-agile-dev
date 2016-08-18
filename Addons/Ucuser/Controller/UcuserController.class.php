<?php

namespace Addons\Ucuser\Controller;
use Home\Controller\AddonsController;
use Common\Model\UcuserModel;
use Ucuser\Model\UcuserScoreModel;
use Com\TPWechat;
use Com\JsSdkPay;
use Com\ErrCode;

class UcuserController extends AddonsController{

              public function index(){
                $params['mp_id'] = $map['mp_id'] = get_mpid();
                $this->assign ( 'mp_id', $params['mp_id'] );

		      $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
		      if($mid === false){
                  $this->error('只可在微信中访问');
              }
              $user = get_mid_ucuser($mid);                    //获取本地存储公众号粉丝用户信息

              $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
              $surl = get_shareurl();
              if(!empty($surl)){
                  $this->assign ( 'share_url', $surl );
              }

              $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
              $this->assign ( 'appinfo', $appinfo );

              $options['appid'] = $appinfo['appid'];    //初始化options信息
              $options['appsecret'] = $appinfo['secret'];
              $options['encodingaeskey'] = $appinfo['encodingaeskey'];
              $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $fans = $weObj->getUserInfo($user['openid']);
        if($user['status'] != 2 && !empty($fans['openid'])){      //没有同步过用户资料，同步到本地数据
            $user = array_merge($user ,$fans);
            $user['status'] = 2;
            $model = D('Ucuser');
            $model->save($user);
        }

        if($user['login'] == 1){              //登录状态就显示微信的用户资料，未登录状态显示本地存储的用户资料
            if(!empty($fans['openid'])){
                $user = array_merge($user ,$fans);
            }
          }
        $this->assign ( 'user', $user );

        $member = get_member_by_openid($user["openid"]);          //获取会员信息
        $score = D('Ucenter/Score')->getUserScore($member['id'],1);//查积分
        $this->assign ( 'member', $member );
        $this->assign ( 'score', $score );
		//$templateFile = $this->model ['template_list'] ? $this->model ['template_list'] : '';
		$this->display ( );
	}

    public function login(){
        $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $map['id'] = I('id');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        $user = get_mid_ucuser($mid);                    //获取公众号粉丝用户信息
        $this->assign ( 'user', $user );

        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );

        $options['appid'] = $appinfo['appid'];    //初始化options信息
        $options['appsecret'] = $appinfo['secret'];
        $options['encodingaeskey'] = $appinfo['encodingaeskey'];
        $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        if (IS_POST) {

            $aMobile = I('post.mobile', '', 'op_t');
            $aPassword = I('post.password', '', 'op_t');
            $aRemember = I('post.remember', 0, 'intval');

            //微信端登录时检测是否在pc端有帐号，如果有pc端帐号，并且微信端密码和pc端不同，就把pc端密码复制给微信端帐号，以支持pc端注册的帐号直接在微信端登录
            $umap['mobile'] = $aMobile;
            $member = UCenterMember()->where($umap)->find();
            if (empty ($member)) {                                 //在pc端没注册，注册一个pc端帐号

            } else {                                                     //已经通过网站注册过帐号
                if($member['password'] != $user['password']){
                    $data['mid'] = $mid;
                    $data['uid'] = $member['id'];                            //将UCenterMember表的id写入ucuser表uid字段
                    $data['mobile'] = $aMobile;
                    $data['password'] = $member['password'];              //同步加密后的密码
                    $ucuser = M('Ucuser');
                    $ucuser->save($data);
                }
            }

            $ucuser = D('Common/Ucuser');
            $res = $ucuser->login($mid,$aMobile,$aPassword,$aRemember);
           if($res > 0){
                $this->success ( '登录成功', addons_url ( 'Ucuser://Ucuser/index' ) );
            }else{
                $this->error ( $ucuser->getError () );
            }

        } else { //显示登录页面
            $this->display();
        }

    }

    public function register(){
        $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $map['id'] = I('id');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        $user = get_mid_ucuser($mid);                    //获取公众号粉丝用户信息
        $this->assign ( 'user', $user );

        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );

        $options['appid'] = $appinfo['appid'];    //初始化options信息
        $options['appsecret'] = $appinfo['secret'];
        $options['encodingaeskey'] = $appinfo['encodingaeskey'];
        $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        if (IS_POST) {

            $aMobile = I('post.mobile', '', 'op_t');
            $aPassword = I('post.password', '', 'op_t');
            $verify = I('post.verify', '', 'op_t');
            $aMid = I('mid', 0, 'intval');
            //读取SESSION中的验证信息
                $mobile = session('reset_password_mobile');
                 //提交修改密码和接收验证码的手机号码不一致
                 if ($aMobile != $mobile) {
                echo '提交注册的手机号码和接收验证码的手机号码不一致';
                     return false;
                 }
                 $res = D('Verify')->checkVerify($aMobile, "mobile", $verify, 0);
                 //确认验证信息正确
                 if(!$res){
                     echo  '验证码错误';
                     return false;
                 }else{

            }

            //判断是否在pc端已注册
            $umap['mobile'] = $aMobile;
            $member = UCenterMember()->where($umap)->find();
            if (empty ($member)) {                                 //在pc端没注册，注册一个pc端帐号
                //先在Member表注册会员，公众号粉丝在绑定手机后可登录网站
                $aUsername = $aMobile;                  //以手机号作为默认UcenterMember用户名和Member昵称
                $aNickname = $aMobile;          //以手机号作为默认UcenterMember用户名和Member昵称
                $email = $user['openid'].'@mp_id'.$user['mp_id'].'.com';   //以openid@mpid123.com作为默认邮箱
                $aUnType = 5;                                           //微信公众号粉丝注册
                $aRole = 3;                                             //默认公众号粉丝用户角色
                /* 注册用户 */
                $uid = UCenterMember()->register($aUsername, $aNickname, $aPassword, $email, $aMobile, $aUnType);
                if (0 < $uid) { //注册成功
                    initRoleUser($aRole,$uid); //初始化角色用户
                    set_user_status($uid, 1);                           //微信注册的用户状态直接设置为1
                    $data['mid'] = $mid;
                    $user['uid'] = $data['uid'] = $uid;                               //将member表的uid写入ucuser表uid字段
                    Ucuser()->save($data);
                } else { //注册失败，返回错误信息
                    echo "注册用户失败";
                    return false;
                }
            } else {                                                     //已经通过网站注册过帐号
                $data['mid'] = $mid;
                $data['uid'] = $member['id'];                            //将UCenterMember表的id写入ucuser表uid字段
                $data['mobile'] = $aMobile;
                $data['password'] = think_ucenter_md5($aPassword, UC_AUTH_KEY);
                Ucuser()->save($data);
            }
	    
            if($res > 0){
                $this->success ( '注册成功', addons_url ( 'Ucuser://Ucuser/login' ) );
            }else{
                echo '帐号已注册';
            }

        } else { //显示注册页面
            $this->display();
        }

    }

    public function profile(){
        $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $map['id'] = I('id');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        $user = get_mid_ucuser($mid);                    //获取公众号粉丝用户信息

        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $surl = get_shareurl();
        if(!empty($surl)){
            $this->assign ( 'share_url', $surl );
        }

        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );

        $options['appid'] = $appinfo['appid'];    //初始化options信息
        $options['appsecret'] = $appinfo['secret'];
        $options['encodingaeskey'] = $appinfo['encodingaeskey'];
        $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $fans = $weObj->getUserInfo($user['openid']);
        $this->assign ( 'user', $user );

        if (IS_POST) {
            $data['mid'] = $mid;
            $data['nickname'] = I('post.nickname', '', 'op_t');
            $data['mobile'] = I('post.mobile', '', 'op_t');
            $data['email'] = I('post.email', '', 'op_t');
            $data['sex'] = I('post.sex', '', 'intval');
            $data['qq'] = I('post.qq', '', 'op_t');
            $data['weibo'] = I('post.weibo', '', 'op_t');
            $data['signature'] = I('post.signature', '', 'op_t');
            $verify = I('post.verify', '', 'op_t');

            //读取SESSION中的验证信息
            $mobile = session('reset_password_mobile');
            //提交修改密码和接收验证码的手机号码不一致
            if ($data['mobile'] != $mobile) {
                echo '提交修改密码和接收验证码的手机号码不一致';
                return false;
            }
            $res = D('Verify')->checkVerify($data['mobile'], "mobile", $verify, 0);
            //确认验证信息正确
            if(!$res){
                echo  '验证码错误';
                return false;
            }else{

            }

            $ucuser = D('Common/Ucuser');
            $res = $ucuser->save($data);
            if($res > 0){
                $this->success ( '更新资料成功', addons_url ( 'Ucuser://Ucuser/profile' ) );
            }else{
                $this->error ( $ucuser->getError () );
            }

        } else { //显示资料页面
           if($user['openid'] != $fans['openid']){        //本地保存的openid和公众平台获取的不同，不允许用户自己以外的人访问
               $this->error ( '无权访问用户资料',addons_url ( 'Ucuser://Ucuser/login' ),5 );
           }
            $this->display();
        }

    }

    public function logout($mid = 0){
        if($mid == 0){
            $mid = get_ucuser_mid();
        }
        $ucuser = D('Common/Ucuser');
        $ucuser->logout($mid);
        $this->success ( '已退出登录', addons_url ( 'Ucuser://Ucuser/index' ) );
    }

public function forget(){

        $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $map['id'] = I('id');
        $mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($mid === false){
            $this->error('只可在微信中访问');
        }
        $ucuser = get_mid_ucuser($mid);
        if (IS_POST) {
            $aMobile = I('post.mobile', '', 'op_t');
            $verify = I('post.verify', '', 'op_t');
            $password = I('post.password', '', 'op_t');
            $repassword = I('post.repassword', '', 'op_t');

            //确认两次输入的密码正确
            if ($password != $repassword) {
                $this->error('两次输入的密码不一致');
            }
            //读取SESSION中的验证信息
            $mobile = session('reset_password_mobile');
           //提交修改密码和接收验证码的手机号码不一致
            if ($aMobile != $mobile) {
                $this->error('提交修改密码和接收验证码的手机号码不一致');
            }

            $res = D('Verify')->checkVerify($aMobile, "mobile", $verify, 0);
            //确认验证信息正确
            if(!$res){
                echo '验证码错误';
                return false;
            }else{
                echo true;
            }

            //将新的密码写入数据库
            $data1 = array('mid' => $mid, 'mobile' => $aMobile, 'password' => $password);
            $model = D('Common/Ucuser');
            $data1 = $model->create($data1);
            if (!$data1) {
                $this->error('密码格式不正确');
            }
            $result = $model->where(array('mid' => $mid))->save($data1);
            if ($result === false) {
                $this->error('数据库写入错误');
            }

            //将新的密码写入数据库

            $data = array('id' => $ucuser['uid'], 'mobile' => $aMobile, 'password' => $password);
            $model = UCenterMember();
            $data = $model->create($data);
            if (!$data) {
                $this->error('密码格式不正确');
            }
            $result = $model->where(array('id' => $ucuser['uid']))->save($data);
            if ($result === false) {
                $this->error('数据库写入错误');
            }

            //显示成功消息
            $this->success('密码重置成功',  addons_url ( 'Ucuser://Ucuser/login' ) );

        }

            $this->display();
    }

    /**
     * sendVerify 发送短信验证码
     * @author:patrick contact@uctoo.com
     *
     */
    public function sendVerify()
    {
        $mobile = I('post.mobile', '', 'op_t');

        if (empty($mobile)) {
            $this->error('手机号不能为空');
        }

        //保存SESSION中的验证手机号码
        session('reset_password_mobile',$mobile);

        $res = sendSMS($mobile,"");
        echo $res;             //ajax 返回提示
    }

    /**
     * checkVerify 检测验证码
     * @author:patrick contact@uctoo.com
     *
     */
    public function checkVerify()
    {
        $aMobile = I('post.mobile', '', 'op_t');
        $verify = I('post.verify', '', 'op_t');
        $aUid = I('mid', 0, 'intval');

        //读取SESSION中的验证信息
        $mobile = session('reset_password_mobile');
        //提交修改密码和接收验证码的手机号码不一致
        if ($aMobile != $mobile) {
            echo '提交注册的手机号码和接收验证码的手机号码不一致';
            return false;
        }

       $res = D('Verify')->checkVerify($aMobile, "mobile", $verify, 0);

       if (!$res) {
          echo '验证码错误';
          return false;
       }else{
          echo true;
       }
    }

    /**
     * myscore UCToo 5维图显示页
     * @author:patrick contact@uctoo.com
     *
     */
    public function myscore(){
        $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $to_mid = I('to_mid');

        $from_mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($to_mid == 0){
            $to_mid = $from_mid;       //没有打分对象，就是默认浏览者5维首页
        }
        if($from_mid === false){
            $this->error('只可在微信中访问');
        }
        $to_user = get_mid_ucuser($to_mid);                    //获取本地存储公众号粉丝用户信息
        $from_user = get_mid_ucuser($from_mid);                    //获取本地存储公众号粉丝用户信息

        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );

        $options['appid'] = $appinfo['appid'];    //初始化options信息
        $options['appsecret'] = $appinfo['secret'];
        $options['encodingaeskey'] = $appinfo['encodingaeskey'];
        $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $fans = $weObj->getUserInfo($from_user['openid']);
        if($from_user['status'] != 2 && !empty($fans['openid'])){      //没有同步过用户资料，同步到本地数据
            $from_user = array_merge($from_user ,$fans);
            $from_user['status'] = 2;
            $model = D('Ucuser');
            $model->save($from_user);
        }

        if($from_user['login'] == 1){              //登录状态就显示微信的用户资料，未登录状态显示本地存储的用户资料
            if(!empty($fans['openid'])){
                $from_user = array_merge($from_user ,$fans);
            }
        }
        $this->assign ( 'to_user', $to_user );
        $this->assign ( 'from_user', $from_user );

        //5维图业务逻辑
        $map['to_mid'] = $to_mid;
        $myscore = M('Myscore')->where($map)->order('score_time desc')->limit(100)->select();

        $avgScore1 = M('Myscore')->where($map)->avg('score1');
        $avgScore2 = M('Myscore')->where($map)->avg('score2');
        $avgScore3 = M('Myscore')->where($map)->avg('score3');
        $avgScore4 = M('Myscore')->where($map)->avg('score4');
        $avgScore5 = M('Myscore')->where($map)->avg('score5');

        $this->assign ( 'myscore', $myscore );
        $this->assign ( 'avgScore1', round($avgScore1,1) );
        $this->assign ( 'avgScore2', round($avgScore2,1) );
        $this->assign ( 'avgScore3', round($avgScore3,1) );
        $this->assign ( 'avgScore4', round($avgScore4,1) );
        $this->assign ( 'avgScore5', round($avgScore5,1) );

        $totalscorelist = M('Totalscore')->where($params)->order('totalscore desc')->limit(5)->select();
        $mytotalscore = M('Totalscore')->where($map)->find();
        $where['totalscore'] = array('egt',$mytotalscore['totalscore']);
        $myrank = M('Totalscore')->where($params)->where($where)->count();

        $this->assign ( 'totalscorelist', $totalscorelist );
        $this->assign ( 'mytotalscore', $mytotalscore );
        $this->assign ( 'myrank', $myrank );

        //分享数据定义
        $sharedata['title']= $to_user['nickname']."的5维";
        $shares1 = $avgScore1? round($avgScore1,1) : "5";
        $shares2 = $avgScore2? round($avgScore2,1) : "5";
        $shares3 = $avgScore3? round($avgScore3,1) : "5";
        $shares4 = $avgScore4? round($avgScore4,1) : "5";
        $shares5 = $avgScore5? round($avgScore5,1) : "5";
        $sharedata['desc']= "颜".$shares1.",学".$shares2.",财".$shares3.",品".$shares4.",战".$shares5.",不服来单挑！";
        $domain = 'http://'.$_SERVER['HTTP_HOST'];
        $sharedata['link'] = addons_url('Ucuser://Ucuser/domyscore', array('mp_id' => get_mpid(),'to_mid'=>$to_mid));
        $sharedata['imgUrl'] = $to_user['headimgurl'];
        $this->assign ( 'sharedata', $sharedata );

        $to_url = addons_url('Ucuser://Ucuser/myscore', array('mp_id' => get_mpid(),'to_mid'=>$to_mid));     //被打分者5维图首页
        $from_url = addons_url('Ucuser://Ucuser/myscore', array('mp_id' => get_mpid(),'to_mid'=>$from_mid)); //打分者5维图首页
        $help_url = addons_url('Ucuser://Ucuser/help', array('mp_id' => get_mpid(),'to_mid'=>$from_mid)); //帮助地址

        $this->assign ( 'to_url', $to_url );
        $this->assign ( 'from_url', $from_url );
        $this->assign ( 'help_url', $help_url );

        $this->display ( );
    }

    /**
     * domyscore UCToo 5维图打分页
     * @author:patrick contact@uctoo.com
     *
     */
    public function domyscore(){
        $params['mp_id'] = $map['mp_id'] = get_mpid();
        $this->assign ( 'mp_id', $params['mp_id'] );
        $to_mid = I('to_mid');
        if($to_mid == 0){
            $this->error('缺少打分对象');
        }
        $from_mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($from_mid === false){
            $this->error('只可在微信中访问');
        }
        $to_user = get_mid_ucuser($to_mid);                    //获取本地存储公众号粉丝用户信息
        $from_user = get_mid_ucuser($from_mid);                    //获取本地存储公众号粉丝用户信息

        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $appinfo = get_mpid_appinfo ( $params ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );

        $options['appid'] = $appinfo['appid'];    //初始化options信息
        $options['appsecret'] = $appinfo['secret'];
        $options['encodingaeskey'] = $appinfo['encodingaeskey'];
        $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $fans = $weObj->getUserInfo($from_user['openid']);
        if($from_user['status'] != 2 && !empty($fans['openid'])){      //没有同步过用户资料，同步到本地数据
            $from_user = array_merge($from_user ,$fans);
            $from_user['status'] = 2;
            $model = D('Ucuser');
            $model->save($from_user);
        }

        if($from_user['login'] == 1){              //登录状态就显示微信的用户资料，未登录状态显示本地存储的用户资料
            if(!empty($fans['openid'])){
                $from_user = array_merge($from_user ,$fans);
            }
        }
        $this->assign ( 'to_user', $to_user );
        $this->assign ( 'from_user', $from_user );

        //5维图业务逻辑
        $map['to_mid'] = $to_mid;
        $myscore = M('Myscore')->where($map)->order('score_time desc')->limit(100)->select();

        $this->assign ( 'myscore', $myscore );

        $totalscorelist = M('Totalscore')->where($params)->order('totalscore desc')->limit(5)->select();
        $mytotalscore = M('Totalscore')->where($map)->find();
        $where['totalscore'] = array('egt',$mytotalscore);
        $myrank = M('Totalscore')->where($params)->where($where)->count();

        $this->assign ( 'totalscorelist', $totalscorelist );
        $this->assign ( 'mytotalscore', $mytotalscore );
        $this->assign ( 'myrank', $myrank );

        //分享数据定义
        $sharedata['title']= $to_user['nickname']."的5维";
        $shares1 = "5";
        $shares2 = "5";
        $shares3 = "5";
        $shares4 = "5";
        $shares5 = "5";
        $sharedata['desc']= "颜".$shares1.",学".$shares2.",财".$shares3.",品".$shares4.",战".$shares5.",不服来单挑！";
        $domain = 'http://'.$_SERVER['HTTP_HOST'];
        $sharedata['link'] = addons_url('Ucuser://Ucuser/domyscore', array('mp_id' => get_mpid(),'to_mid'=>$to_mid));
        $sharedata['imgUrl'] = $to_user['headimgurl'];
        $this->assign ( 'sharedata', $sharedata );

        $savescore_url = addons_url('Ucuser://Ucuser/savemyscore', array('mp_id' => get_mpid()));
        $to_url = addons_url('Ucuser://Ucuser/myscore', array('mp_id' => get_mpid(),'to_mid'=>$to_mid));     //被打分者5维图首页
        $from_url = addons_url('Ucuser://Ucuser/myscore', array('mp_id' => get_mpid(),'to_mid'=>$from_mid)); //打分者5维图首页
        $help_url = addons_url('Ucuser://Ucuser/help', array('mp_id' => get_mpid(),'to_mid'=>$from_mid)); //帮助地址

        $this->assign ( 'savescore_url', $savescore_url );
        $this->assign ( 'to_url', $to_url );
        $this->assign ( 'from_url', $from_url );
        $this->assign ( 'help_url', $help_url );

        $this->display ( );
    }

    /**
     * domyscore UCToo 5维图保存打分
     * @author:patrick contact@uctoo.com
     *
     */
    public function savemyscore(){
        $data['to_mid'] = $tdata['to_mid'] = I('to_mid');
        $data['to_headimgurl'] = $tdata['to_headimgurl'] = I('to_headimgurl');
        $data['to_name'] = $tdata['to_name'] = I('to_name');
        $data['from_mid'] = I('from_mid');
        $data['from_headimgurl'] = I('from_headimgurl');
        $data['from_name'] = I('from_name');
        $data['mp_id'] = $tdata['mp_id'] = I('mp_id');
        $data['score_time'] = I('score_time');
        $data['remark'] = I('remark');
        $data['score1'] = I('score1');
        $data['score2'] = I('score2');
        $data['score3'] = I('score3');
        $data['score4'] = I('score4');
        $data['score5'] = I('score5');
        if(empty($data['to_mid'])|| empty($data['from_mid'])|| empty($data['mp_id'])){
            echo '打分参数错误';
            return false;
        }
        if( $data['score1']<= 0 || 5< $data['score1']){
            echo '打分错误';
            return false;
        }
        if( $data['score2']<= 0 || 5< $data['score2']){
            echo '打分错误';
            return false;
        }
        if( $data['score3']<= 0 || 5< $data['score3']){
            echo '打分错误';
            return false;
        }
        if( $data['score4']<= 0 || 5< $data['score4']){
            echo '打分错误';
            return false;
        }
        if( $data['score5']<= 0 || 5< $data['score5']){
            echo '打分错误';
            return false;
        }

        $map['to_mid'] = $tmap['to_mid'] = I('to_mid');
        $map['from_mid'] = I('from_mid');
        $map['mp_id'] = $tmap['mp_id'] = I('mp_id');
        $myscore_mod = M('Myscore');
        $myscore = $myscore_mod->where($map)->find();
        if (empty ($myscore)) {                                 //相同的人还没打过分
            /* 保存打分 */
            if($myscore_mod->create($data) && $myscore_mod->add()){
                echo "打分成功";
                //操作积分
                D('Ucuser/UcuserScore')->setUserScore($data['from_mid'],10,2,'inc'); //打分的粉丝加10威望
                D('Ucuser/UcuserScore')->setUserScore($data['to_mid'],1,1,'inc'); //被打分的加1积分
                //给被打分的发模板消息
                $param['mp_id'] = $data['mp_id'];
                $param['template_id'] = "cGwtzvU8HleBVvrIIq7WjpwXWjW4kvQY6d02Fa7jAdE";
                $to_user = get_mid_ucuser($data['to_mid']);                    //获取本地存储公众号粉丝用户信息
                $param['touser'] = $to_user["openid"];
                $to_url = addons_url('Ucuser://Ucuser/myscore', array('mp_id' => get_mpid(),'to_mid'=>$data['to_mid']));     //被打分者5维图首页
                $param['url'] = $to_url;
                $param['from_name'] = $data['from_name'];
                $param['score2'] = $to_user['score2'];
                $param['score1'] = $to_user['score1'];

                hook('TplMsg',$param);   //把消息分发到addons/TplMsg/TplMsg的方法中,发送模板信息

                //增加5维图总分
                $from_tscore = $data['score1'] + $data['score2'] +$data['score3'] +$data['score4'] +$data['score5'] ;
                $tscore_mod = M('Totalscore');
                $tscore = $tscore_mod->where($tmap)->find();
                if (empty ($tscore)) {                                 //被打分者还没总分数据
                    /* 保存打分 */
                    $tdata['totalscore'] = $from_tscore;             //第一次打分总和就是初始总分
                    if($tscore_mod->create($tdata) && $tscore_mod->add()){
			echo 'ok';
                    } else {
                        echo "增加总分失败";
                        return false;
                    }
                } else {                                                     //已经有总分数据
                    $tscore_mod->where($tmap)->setInc('totalscore',$from_tscore);
                }
            } else {
                echo "打分失败";
                return false;
            }
        } else {                                                     //已经打过分
            echo "你已经打过分了";
            return false;
        }

    }

    /**
     * showscore UCToo 5维图显示打分详情
     * @author:patrick contact@uctoo.com
     *
     */
    public function showscore(){

        $map['id'] = I('id');
        if(empty($map['id'])){
            $this->error('打分详情参数错误');
        }
        $showscore = M('Myscore')->where($map)->find();
        $from_mid = get_ucuser_mid();   //获取粉丝用户mid，一个神奇的函数，没初始化过就初始化一个粉丝
        if($from_mid === false){
            $this->error('只可在微信中访问');
        }
        $to_user = get_mid_ucuser($showscore['to_mid']);                    //获取本地存储公众号粉丝用户信息
        $from_user = get_mid_ucuser($from_mid);                    //获取本地存储公众号粉丝用户信息

        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $appinfo = get_mpid_appinfo ( $showscore ['mp_id'] );   //获取公众号信息
        $this->assign ( 'appinfo', $appinfo );

        $options['appid'] = $appinfo['appid'];    //初始化options信息
        $options['appsecret'] = $appinfo['secret'];
        $options['encodingaeskey'] = $appinfo['encodingaeskey'];
        $weObj = new TPWechat($options);

        $auth = $weObj->checkAuth();
        $js_ticket = $weObj->getJsTicket();
        if (!$js_ticket) {
            $this->error('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.ErrCode::getErrText($weObj->errCode));
        }
        $js_sign = $weObj->getJsSign($url);

        $this->assign ( 'js_sign', $js_sign );

        $fans = $weObj->getUserInfo($from_user['openid']);
        if($from_user['status'] != 2 && !empty($fans['openid'])){      //没有同步过用户资料，同步到本地数据
            $from_user = array_merge($from_user ,$fans);
            $from_user['status'] = 2;
            $model = D('Ucuser');
            $model->save($from_user);
        }

        if($from_user['login'] == 1){              //登录状态就显示微信的用户资料，未登录状态显示本地存储的用户资料
            if(!empty($fans['openid'])){
                $from_user = array_merge($from_user ,$fans);
            }
        }
        $this->assign ( 'to_user', $to_user );
        $this->assign ( 'from_user', $from_user );

        $to_url = addons_url('Ucuser://Ucuser/myscore', array('mp_id' => get_mpid(),'to_mid'=>$showscore['to_mid']));     //被打分者5维图首页
        $from_url = addons_url('Ucuser://Ucuser/myscore', array('mp_id' => get_mpid(),'to_mid'=>$showscore['from_mid'])); //打分者5维图首页
        $help_url = addons_url('Ucuser://Ucuser/help', array('mp_id' => get_mpid(),'to_mid'=>$from_mid)); //帮助地址

        $this->assign ( 'showscore', $showscore );
        $this->assign ( 'to_url', $to_url );
        $this->assign ( 'from_url', $from_url );
        $this->assign ( 'help_url', $help_url );

        //分享数据定义
        $sharedata['title']= $showscore['to_name']."的5维";
        $shares1 = $showscore['score1'];
        $shares2 = $showscore['score2'];
        $shares3 = $showscore['score3'];
        $shares4 = $showscore['score4'];
        $shares5 = $showscore['score5'];
        $sharedata['desc']= "颜".$shares1.",学".$shares2.",财".$shares3.",品".$shares4.",战".$shares5.",不服来单挑！";
        $sharedata['link'] = addons_url('Ucuser://Ucuser/domyscore', array('mp_id' => get_mpid(),'to_mid'=>$showscore['to_mid']));
        $sharedata['imgUrl'] = $showscore['to_headimgurl'];
        $this->assign ( 'sharedata', $sharedata );

        $this->display ( );
    }

    /**
     * help UCToo 5维图帮助信息
     * @author:patrick contact@uctoo.com
     *
     */
    public function help(){

        $home_url = addons_url('Ucuser://Ucuser/index', array('mp_id' => get_mpid()));
        $this->assign ( 'home_url', $home_url );

        $this->display ( );
    }
}