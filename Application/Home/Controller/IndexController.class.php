<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: patrick <contact@uctoo.com> <http://www.uctoo.com>
// +----------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;


/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends Controller
{

    //系统首页
    public function index()
    {
        if(is_login()){
        }
        hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != ''&&strtolower($default_url)!='home/index/index') {
            redirect(get_nav_url($default_url));
        }
        $this->display();
    }

    public function home()
    {
        if(is_login()){
        }
        hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != ''&&strtolower($default_url)!='home/index/index') {
            redirect(get_nav_url($default_url));
        }

        $show_blocks = get_kanban_config('BLOCK', 'enable', array(), 'Home');

        $this->assign('showBlocks', $show_blocks);


        $enter = modC('ENTER_URL', '', 'Home');
        $this->assign('enter', get_nav_url($enter));
        $this->display();
    }

    protected function _initialize()
    {

        /*读取站点配置*/
        $config = api('Config/lists');
        C($config); //添加配置

        if (!C('WEB_SITE_CLOSE')) {
            $this->error(L('_ERROR_WEBSITE_CLOSED_'));
        }
    }

    public function help() {
        if (empty ( $_GET ['id'] )) {
            $this->error ( '公众号参数非法' );
        }
        $map['uid'] = is_login();
        $map['id'] = $_GET ['id'];
        $mp = M('MemberPublic')->where($map)->find();
        $this->assign('mp_id', mpid_md5($mp['appid']));
        $this->display ( );
    }

	/*
	 * 二维码
	 */
	public function qrcode()
	{
		error_reporting(E_ERROR);
		require_once VENDOR_PATH.'phpqrcode/phpqrcode.php';
		$url = urldecode($_GET["data"]);
		\QRcode::png($url);
	}
}