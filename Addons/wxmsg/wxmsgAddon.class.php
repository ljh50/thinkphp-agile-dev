<?php

namespace Addons\wxmsg;
use Common\Controller\Addon;

/**
 * 微信回复插件
 * @author 无名
 */

    class wxmsgAddon extends Addon{

        public $info = array(
            'name'=>'wxmsg',
            'title'=>'微信回复',
            'description'=>'这是一个临时描述',
            'status'=>1,
            'author'=>'无名',
            'version'=>'0.1'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }

        //实现的wxmsg钩子方法
        public function wxmsg($param){


            switch($param['type']){

                case 'text':

                    $param['weObj']->text($param['replay_msg']['detile']);
                    break;

                case 'picture':

                    $i = 0;
                    $res = $param['replay_msg'];
                    $pic =explode(',',$res['pic']);

                        $reData[0]['Title'] = $res['title0'];
                        $reData[0]['Description'] = $res['detile0'];

                        $reData[0]['PicUrl'] = get_cover_url($pic[$i]) ; //'http://images.domain.com/templates/domaincom/logo.png';

                        $reData[0]['Url'] = $res['url0'];

                    if($res['title1']){
                        $i++;
                        $reData[$i]['Title'] = $res['title1'];
                        $reData[$i]['Description'] = $res['detile1'];
                        $reData[$i]['PicUrl'] = get_cover_url($pic[$i]) ; //'http://images.domain.com/templates/domaincom/logo.png';
                        $reData[$i]['Url'] = $res['url1'];
                    }

                    if($res['title2']){
                        $i++;
                        $reData[$i]['Title'] = $res['title2'];
                        $reData[$i]['Description'] = $res['detile2'];
                        $reData[$i]['PicUrl'] = get_cover_url($pic[$i]) ; //'http://images.domain.com/templates/domaincom/logo.png';
                        $reData[$i]['Url'] = $res['url2'];
                    }

                    if($res['title3']){
                        $i++;
                        $reData[$i]['Title'] = $res['title3'];
                        $reData[$i]['Description'] = $res['detile3'];
                        $reData[$i]['PicUrl'] = get_cover_url($pic[$i]) ; //'http://images.domain.com/templates/domaincom/logo.png';
                        $reData[$i]['Url'] = $res['url3'];
                    }

                    if($res['title4']){
                        $i++;
                        $reData[$i]['Title'] = $res['title4'];
                        $reData[$i]['Description'] = $res['detile4'];
                        $reData[$i]['PicUrl'] = get_cover_url($pic[$i]) ; //'http://images.domain.com/templates/domaincom/logo.png';
                        $reDta[$i]['Url'] = $res['url4'];
                    }

                    $param['weObj']->news($reData);
                    break;


            }

        }

    }