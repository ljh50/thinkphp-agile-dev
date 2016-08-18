<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: patrick <contact@uctoo.com> <http://www.uctoo.com>
// +----------------------------------------------------------------------

namespace Mpbase\Model;
use Admin\Builder\AdminConfigBuilder;
use Think\Model;

/**
 * Class AutoreplyModel 自动回复模型
 * @package Mpbase\Model
 * @auth patrick
 */
class AutoreplyModel extends Model {

    public function __construct($name, $tablePrefix, $connection)
    {
        parent::__construct($name, $tablePrefix, $connection);
    }

    public function getArType($key = null,$type = 0){
        //解决关注回复不能是多图文
        if($type){
            $array = array( 2 => '消息自动回复', 3 => '关键词自动回复');
        }else{
            $array = array(1 => '关注自动回复', 2 => '消息自动回复', 3 => '关键词自动回复');
        }
        return !isset($key)?$array:$array[$key];
    }


    public function getMessagesType(){
        $array =array(
            array('id'=>0,'value' =>'全部'),
            array('id'=>1,'value' =>'关注自动回复'),
            array('id'=>2,'value' =>'消息自动回复'),
            array('id'=>3,'value' =>'关键词自动回复')
        );
        return $array;
    }

    public function replyMessagesType(){
        $array =array(
            array('id'=>0,'value' =>'全部'),
            array('id'=>1,'value' =>'文本'),
            array('id'=>2,'value' =>'图文'),
            array('id'=>3,'value' =>'待定')
        );
        return $array;
    }
    public function addAr($data)
    {
        $res = $this->add($data);
        return $res;
    }

    public function getAr($where){
        $mp = $this->where($where)->find();
        return $mp;
    }

    public function getList($where){
        $list = $this->where($where)->select();
        return $list;
    }

    public function editAr($data)
    {
        $res = $this->save($data);
        return $res;
    }

/*
 * 然并卵的东西
 * */
    public function builder_picture_messages(){
        $builder = new AdminConfigBuilder();
        $builder

            ->keyText('title0','标题','第一条')
            ->keyText('detile0','内容')
            ->keyText('url0','url0')
            ->keyText('title1','标题','第二条')
            ->keyText('detile1','内容')
            ->keyText('url1','url1')
            ->keyText('title2','标题','第三条')
            ->keyText('detile2','内容')
            ->keyText('url2','url2')
            ->keyText('title3','标题','第四条')
            ->keyText('detile3','内容')
            ->keyText('url3','url3')
            ->keyText('title4','标题','第五条')
            ->keyText('detile4','内容')
            ->keyText('url4','url4')
            ->keyMultiImage('pic','图片','按顺序添加图片');


    }

    /*
     *判断类型选择数据库
     *
     * */
    public function post_messages($data){

        switch($data['type']){
            case 'picture':
                $picturemodel = M('picture_messages');
                !$data['ms_id']?
                $res = $picturemodel->field('title0,detile0,url0,title1,detile1,url1,title2,detile2,url2,title3,detile3,url3,title4,detile4,url4,pic')->add($data):
                $res = $picturemodel->field('title0,detile0,url0,title1,detile1,url1,title2,detile2,url2,title3,detile3,url3,title4,detile4,url4,pic')->save($data);
                break;
            case 'text':
                $textmodel = M('text_messages');
                !$data['ms_id']?
                $res = $textmodel->field('detile')->add($data):
                $res = $textmodel->field('id,detile')->save($data);
                break;
            default:
                return false;
        }

        return $res;
    }


    public function get_mes_data($data){

        $res_data= $this->get_type_data($data);
        return array_merge($res_data,$data);


    }

    public function get_type_data($data){

        $id = $data['ms_id'];

        switch($data['type']){
            case 'picture':
                $model = M('picture_messages');
                break;
            case 'text':
                $model = M('text_messages');
                break;
        }

        return $model->find($id);
    }


}