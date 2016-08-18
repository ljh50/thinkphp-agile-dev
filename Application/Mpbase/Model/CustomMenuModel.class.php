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
 * Class CustomMenuModel 专辑模型
 * @package Mpbase\Model
 * @auth uctoo
 */
class CustomMenuModel extends Model {

    protected $tableName='custom_menu';

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );


    /**获得分类树
     * @param int  $id
     * @param bool $field
     * @return array
     * @auth 陈一枭
     */
    public function getTree($id = 0, $field = true){


        /* 获取所有分类 */
        $map['token']  = $id;
        $list = $this->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_');


        return $list;
    }


    /**
     * 获取分类详细信息
     * @param  milit   $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function info($id, $field = true){
        /* 获取分类信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['token'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    public function getCmType($key = null){
        $array = array('click' => '点击推事件', 'view' => '跳转URL', 'scancode_push' => '扫码推事件', 'scancode_waitmsg' => '扫码带提示', 'pic_sysphoto' => '弹出系统拍照发图', 'pic_photo_or_album' => '弹出拍照或者相册发图', 'pic_weixin' => '弹出微信相册发图器', 'location_select' => '弹出地理位置选择器', 'none' => '无事件的一级菜单');
        return empty($key)?$array:$array[$key];
    }

}