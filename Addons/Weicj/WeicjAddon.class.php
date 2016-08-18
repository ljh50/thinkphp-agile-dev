<?php

namespace Addons\Weicj;
use Common\Controller\Addon;

/**
 * 微场景插件
 * @author kocorp
 */

    class WeicjAddon extends Addon{

        public $info = array(
            'name'=>'Weicj',
            'title'=>'微场景',
            'description'=>'微场景为产品、品牌以及事件的展示搭建了一个舞台，通过图片和音乐渲染氛围展示想传达的内容，并引导客户。可与其他插件配合使用以达到更好的营销效果。',
            'status'=>1,
            'author'=>'kocorp',
            'version'=>'1.0',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Weicj/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Weicj/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

    }