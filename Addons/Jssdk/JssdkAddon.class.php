<?php

namespace Addons\Jssdk;
use Common\Controller\Addon;

/**
 * Jssdk插件
 * @author uctoo
 */

    class JssdkAddon extends Addon{

        public $info = array(
            'name'=>'Jssdk',
            'title'=>'微信JSSDK演示案例',
            'description'=>'微信开放平台JSSDK演示案例合辑',
            'status'=>1,
            'author'=>'uctoo',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Jssdk/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Jssdk/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

    }