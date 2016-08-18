<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: patrick <contact@uctoo.com> <http://www.uctoo.com>
// +----------------------------------------------------------------------

return array(
    //模块名
    'name' => 'Appstore',
    //别名
    'alias' => '应用商城',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 1,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 0,
    //模块描述
    'summary' => '应用商城模块，用户可以在应用市场购买UCToo产品和服务',
    //开发者
    'developer' => 'UCT',
    //开发者网站
    'website' => 'http://www.uctoo.com',
    //前台入口，可用U函数
    'entry' => 'Appstore/index/index',

    'admin_entry' => 'Admin/Appstore/index',

    'icon' => 'cloud',

    'can_uninstall' => 0
);