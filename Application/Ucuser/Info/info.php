<?php
/**
 * 所属项目 UCToo.
 * 开发者: UCToo
 * 创建日期: 2015-04-30
 * 创建时间: 08:36
 * 版权所有 UCT(www.uctoo.com)
 */

return array(
    //模块名
    'name' => 'Ucuser',
    //别名
    'alias' => '微会员',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '微信用户中心模块，系统核心模块',
    //开发者
    'developer' => 'UCToo',
    //开发者网站
    'website' => 'http://www.uctoo.com',
    //前台入口，可用U函数
    'entry' => 'Ucuser/index/index',

   'admin_entry' => 'Admin/Ucuser/index',

    'icon'=>'comments',

    'can_uninstall' => 0
);