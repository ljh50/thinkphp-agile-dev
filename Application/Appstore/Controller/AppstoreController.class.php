<?php
// +----------------------------------------------------------------------
// | UCToo [ Universal Convergence Technology ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.uctoo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: patrick <contact@uctoo.com> <http://www.uctoo.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;

use Think\Model;




class AppstoreController extends AdminController
{

    function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {
        $this->display();
    }


    /*
     * 下载文件，安装
     * */
    public function getfile(){

        $token = I('post.token');
//        $cookie_file='PHPSESSID=ud1pen71s03oro4gpv082n8vm7;';
        $url = 'http://www.uctoo.cn/index.php?s=/sappstore/index/get_product_info_token/token/'.$token.'.html';
        $ch = curl_init($url);
        $dpost = array('ajax'=>1,);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_COOKIE, $cookie_file); //使用上面获取的cookies
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dpost);
        $contents = curl_exec($ch);

        $header= curl_getinfo($ch);
        curl_close($ch);

        $json = json_decode($contents,true);
        $type = $json['info']['type'];

        if($header['http_code']!==200){
            $this->error('网络错误');
        }


        $url = 'http://www.uctoo.cn/index.php?s=/sappstore/index/download_file';

        $ch = curl_init($url);
        $dpost = array('token'=>$token,'ajax'=>1,);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_COOKIE, $cookie_file); //使用上面获取的cookies
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dpost);
        $contents = curl_exec($ch);
        $header= curl_getinfo($ch);
        curl_close($ch);




        $downname = date('ymd',time()).rand(031,12123134);

        //下载文件   TODO:未定义下载方式，与服务端对接后确定；
        $path = __ROOT__.'updata/'.$downname.'.zip';
        $dir = dirname($path);
        if(!is_dir($dir)) mkdir($path);
        $fp =fopen($path,'wb');
        fwrite($fp,$contents);
        fclose($fp);
        $modelname = $this->unzip($downname,$type);
        sleep(5);
        unlink($path);
        switch($type){
            case 0:
                $this->install($modelname);
            break;
            case 1:
                $this->redirect('admin/addons/install',array('addon_name'=>$modelname));
            break;
            case 2:
                $this->error('主题未上线');
            break;

        }


        $this->success('模块安装成功',U('admin/index/index'));
    }


        /*
         * 解压安装模块
         * 用xxx模块文件夹进行压缩；
         * 解压出来就是Aplication/XXX模块/
         * */
    public function unzip($a,$type=0){
        $name = $a.'.zip';

        load('Appstore/function');
        $size = get_zip_originalsize($name,$type);
        //echo '解压到'.$size.'完成';
        $modelname = explode('/',$size);
        return $modelname['1'];
    }

    /*
     * 模块安装
     *
     *
     * */
    public function install($name){
        $module['name'] = $name;
        $moduleModel = D('Common/Module');
        $moduleModel->reload();
        $module = $moduleModel->getModule($module['name']);
        $res = $moduleModel->install($module['id']);
        //dump($res);
        $installsql = APP_PATH.$module['name'].'/info/install.sql';
        $res = D('')->executeSqlFile($installsql);
        //dump($res);
    }



















}

