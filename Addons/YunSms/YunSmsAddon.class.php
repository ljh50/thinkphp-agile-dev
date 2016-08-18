<?php

namespace Addons\YunSms;

use Common\Controller\Addon;

class YunSmsAddon extends Addon
{
    public $info = array(
        'name' => 'YunSms',
        'title' => '云之讯',
        'description' => '',
        'status' => 1,
        'author' => 'uctoo',
        'version' => '1.0.0'
    );

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    /**
     * sms  短信钩子，必需，用于确定插件是短信服务
     * @return bool
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function sms()
    {
        return true;
    }
    public function sendSms($mobile, $content){

        $uid = modC('SMS_UID', '', 'USERCONFIG');
        $pwd = modC('SMS_PWD', '', 'USERCONFIG');
        $http = modC('SMS_HTTP', '', 'USERCONFIG');

        $accountsid = modC('SMS_ACCOUNTSID', '', 'USERCONFIG');
        $token = modC('SMS_TOKEN', '', 'USERCONFIG');
        $appId = modC('SMS_APPID', '', 'USERCONFIG');
        $templateId = modC('SMS_TEMPLATEID', '', 'USERCONFIG');
        $http = 'http://http.yunsms.cn/tx/';
        if (empty($uid) || empty($pwd)) {
            return '管理员还未配置短信信息，请联系管理员配置';
        }
        $data = array
        (
            'uid' => $uid, //用户账号
            'pwd' => strtolower(md5($pwd)), //MD5位32密码
            'mobile' => $mobile, //号码
            'content' => $content, //内容
            'time' => '', //定时发送
            'mid' => '', //子扩展号
            'encode' => 'utf8',
        );


        //初始化必填
        $options['accountsid']=$accountsid; //填写自己的
        $options['token']=$token; //填写自己的
        //初始化 $options必填
        $ucpass = new \Vendor\Ucpaas($options);

        //随机生成6位验证码
        srand((double)microtime()*1000000);//create a random number feed.
        $ychar="0,1,2,3,4,5,6,7,8,9";
        $list=explode(",",$ychar);
        for($i=0;$i<6;$i++){
            $randnum = rand(0,9); // 10+26;
            $authnum .= $list[$randnum];
        }
        //短信验证码（模板短信）,默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
        $appId = $appId;  //填写自己的
        $to = $mobile;
        // $templateId = "1";
        $param=$authnum;
        $param = $param.",10";                                       //添加短信模板的第二个参数，“您的验证码为{1}，请于{2}分钟内正确输入验证码”
        $arr=$ucpass->templateSMS($appId,$to,$templateId,$param);
        if (substr($arr,21,6) == 000000) {
            D('Verify')->addSMSVerify($to,$authnum);                //保存验证短信到验证码表

            //如果成功就，这里只是测试样式，可根据自己的需求进行调节
            return "短信验证码已发送成功，请注意查收短信";

        }else{

            //如果不成功
            return "短信验证码发送失败，请联系客服";

        }


    }





}