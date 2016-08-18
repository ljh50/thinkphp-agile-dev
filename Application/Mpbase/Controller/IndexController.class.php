<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: patrick <contact@uctoo.com> <http://www.uctoo.com>
// +----------------------------------------------------------------------


namespace Mpbase\Controller;

use Think\Controller;
/**
 * 前台业务逻辑都放在
 * @var
 */

class IndexController extends Controller
{

    public function _initialize()
    {

    }

    public function index($page = 1, $mp_id = 0)
    {

        $this->display();
    }

}