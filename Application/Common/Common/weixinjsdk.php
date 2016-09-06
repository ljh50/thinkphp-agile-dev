<?php



$map ['mp_id'] = get_mpid( $_SESSION['route_mp_id']);
$appinfo = M ( 'member_public' )->where ( $map )->find ();
$options['appid'] = $appinfo['appid'];    //初始化options信息
$options['appsecret'] = $appinfo['secret'];
$options['encodingaeskey'] = $appinfo['encodingaeskey'];

$weObj = new \Com\TPWechat($options);

$auth = $weObj->checkAuth($appinfo['appid'],$appinfo['secret']);

$js_ticket = $weObj->getJsTicket();
if (!$js_ticket) {
    echo ('获取js_ticket失败！错误码：'.$weObj->errCode.' 错误原因：'.$weObj->errCode);
}
$js_sign = $weObj->getJsSign($url);


//分享数据定义
$sharedata['title']= $user['nickname']."";
$sharedata['desc']= "123";
$sharedata['link'] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$sharedata['imgUrl'] = $user['headimgurl'];


?>
<script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.1.0.js"></script>
<script>

    wx.config({
        debug: true,
        appId: '<?php $js_sign['appid'] ?>',
        timestamp: <?php $js_sign['timestamp']?>,
        nonceStr: '<?php $js_sign['noncestr']?>',
        signature: '<?php $js_sign['signature']?>',
        jsApiList: [
            // 所有要调用的 API 都要加到这个列表中
            'checkJsApi',
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'onMenuShareQQ',
            'onMenuShareWeibo',
            'openProductSpecificView',
            'addCard',
            'chooseCard',
            'openCard'
        ]
    });
    wx.ready(function () {
        // 在这里调用 API
        //朋友圈
        wx.onMenuShareTimeline({
            title: '<?php $sharedata['title'] ?>',
            desc: '<?php $sharedata['desc'] ?>',
            link: '<?php $sharedata['link'] ?>',
            imgUrl: '<?php $sharedata['imgUrl'] ?>',
            trigger: function (res) {
                //alert('用户点击并朋友圈');
            },
            success: function () {
                // 用户确认分享后执行的回调函数
                //alert('Timeline分享成功');
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
                //alert('您取消了分享Timeline');
            }
        });
        wx.onMenuShareAppMessage({
            title: '<?php $sharedata['title'] ?>',
            desc: '<?php $sharedata['desc'] ?>',
            link: '<?php $sharedata['link'] ?>',
            imgUrl: '<?php $sharedata['imgUrl'] ?>',
            type: '', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                //alert('分享给朋友分享成功');
            },
            cancel: function () {
                //alert('您取消了分享给朋友');
            }
        });

    });
    wx.error(function (res) {
        //alert(res.errMsg);
    });


    $('.a_link').on("tap",function(){
        var link = $(this).attr('attr-url');
        //alert(link)
        window.location.href = link;
    });

    $('#go_to_points').on('tap',function(){
        var url = "<?php $sharedata['link'] ?>";
        window.location.href = url;
    });

</script>
<!--end 微信JSSDK-->