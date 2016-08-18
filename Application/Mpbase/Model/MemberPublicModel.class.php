<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: patrick <contact@uctoo.com> <http://www.uctoo.com>
// +----------------------------------------------------------------------

namespace Mpbase\Model;
use Think\Model;

/**
 * Class MemberPublicModel 公众号模型
 * @package Mpbase\Model
 * @auth patrick
 */
class MemberPublicModel extends Model {

    public function getMpType($key = null){
        $array = array(1 => '普通订阅号', 2 => '认证订阅号/普通服务号', 3 => '认证服务号', 4 => '企业号');
        return !isset($key)?$array:$array[$key];
    }

    public function addMp($data)
    {
        $res = $this->add($data);
        return $res;
    }

    public function getMp($where){
        $mp = $this->where($where)->find();
        return $mp;
    }

    public function getList($where){
        $list = $this->where($where)->select();
        return $list;
    }

    public function editMp($data)
    {
        $res = $this->save($data);
        return $res;
    }
}