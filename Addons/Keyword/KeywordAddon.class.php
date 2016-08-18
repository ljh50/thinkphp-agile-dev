<?php

namespace Addons\Keyword;
use Common\Controller\Addon;

/**
 * 关键词插件
 * @author UCToo
 */

    class KeywordAddon extends Addon{

        public $info = array(
            'name'=>'Keyword',
            'title'=>'关键词',
            'description'=>'关键词数据管理和微信关键词消息处理行为插件',
            'status'=>1,
            'author'=>'UCToo',
            'version'=>'0.1'
        );

        public function install(){

            return true;
        }

        public function uninstall(){

            return true;
        }

        //实现的keyword钩子方法，对关键词进行匹配，根据具体处理业务的addon数据或配置，组装回复给用户的内容
        public function keyword($params){

            if($params['mp_id']){
                $kmap['mp_id'] = $params['mp_id'];
                $kmap['keyword'] = $params['weObj']->getRevContent();       //TODO:先只支持精确匹配，后续根据keyword_type字段增加模糊匹配 ，用户在对话框发送关键字
                if(empty($kmap['keyword'])){                                //用户点击菜单拉取关键字对应的回复
                    $data = $params['weObj']->getRevData();
                    $kmap['keyword'] = $data['EventKey'];
                }
                $Keyword = M('Keyword')->where($kmap)->find();

                if($Keyword['model'] && $Keyword['aim_id']){              //关键词匹配第一优先级，如果有指定模型，就用模型中的aim_id数据组装回复的内容

                    $amap['id'] =  $Keyword['aim_id'];
                    $aimData = M($Keyword['model'])->where($amap)->find();

                    $reData[0]['Title'] = $aimData['title'];
                    $reData[0]['Description'] = $aimData['intro'];
                    $reData[0]['PicUrl'] = get_cover_url($aimData['cover']) ; //'http://images.domain.com/templates/domaincom/logo.png';
                    $reData[0]['Url'] = $aimData['url'];

                    $params['weObj']->news($reData);
                }elseif ($Keyword['title']){                                 //关键词匹配第二优先级，关键词表中的回复内容
                    $amap['name'] =  $Keyword['addon'];
                    $aimData = M('Addons')->where($amap)->find();             //插件信息组装回复，当然插件需要先安装了
                  
                    $reData[0]['Title'] = $Keyword['title']? $Keyword['title'] :$aimData['title'];   //关键词匹配第三优先级，插件中的配置信息
                    $reData[0]['Description'] = $Keyword['description']? $Keyword['description'] :$aimData['description'];
                  
                    $reData[0]['PicUrl'] = get_cover_url( $Keyword['cover'] ); //在后台关键词管理功能上传回复封面图片
                    $param['mp_id'] = $params['mp_id'];
                    $reData[0]['Url'] = $Keyword['url'];
                    if($Keyword['url']){                                        //关键词没有链接地址，就回复成文本消息
                        $params['weObj']->news($reData);
                    }else{
                        $params['weObj']->text($Keyword['description']);
                    }

                }else{                                                           //没匹配到关键词转到官方多客服  http://dkf.qq.com/，避免出现“该公众号暂时无法提供服务，请稍候再试”的提示
                    $params['weObj']->transfer_customer_service();
                }

            }else{

            }
           // $params['weObj']->text("hello ");
        }

    }