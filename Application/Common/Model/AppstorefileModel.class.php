<?php
/**
 * Created by PhpStorm.
 * User: Uctoo-Near
 * Date: 2016/4/8
 * Time: 9:13
 */

namespace Common\Model;

use Think\Model;

class AppstorefileModel extends Model{

/*
 * 下载文件
 *暂时用不上；
 * */
    private function downloadFile($url, $local)
    {
        $file = fopen($url, "rb");
        if ($file) {
            //获取文件大小
            $filesize = -1;
            $headers = get_headers($url, 1);
            if ((!array_key_exists("Content-Length", $headers))) $filesize = 0;
            $filesize = $headers["Content-Length"];
            //不是所有的文件都会先返回大小的，有些动态页面不先返回总大小，这样就无法计算进度了
            if (file_exists($local)) {
                unlink($local);
            }
            if (isset($headers['Location'])) {
                $url = $headers['Location'];
            }
            if (is_array($filesize)) {
                $filesize = $filesize[1];
            }
            $filesize = intval($filesize);

            if ($filesize != -1) {
                $this->write('&nbsp;&nbsp;&nbsp;' . L('_FILE_SIZE_TOTAL_') . number_format($filesize / 1024, 2) . 'KB');
                $this->write('&nbsp;&nbsp;&nbsp;' . L('_FILE_DOWNLOAD_START_'));
                // $this->showProgress();
            }
            /* $newf = fopen($local, "wb");
             $downlen = 0;
             $total = 0;
          /* if ($newf) {
                 while (!feof($file)) {
                     $data = fread($file, 1024 * 8);//默认获取8K
                     $downlen += strlen($data);//累计已经下载的字节数
                     fwrite($newf, $data, 1024 * 8);
                     $total += 1024 * 8;
                     if ($total > 1024 * 1024 * 2) {
                         $total = 0;
                         $this->setValue('"' . number_format($downlen / $filesize * 100, 2) . '%' . '"');
                         $this->replace('&nbsp;&nbsp;&nbsp;>已经下载' . number_format($downlen / $filesize * 100, 2) . '% - ' . number_format($downlen / 1024 / 1024, 2) . 'MB', 'success');
                     }
                 }
             }
             if ($file) {
                 fclose($file);
             }
             if ($newf) {
                 fclose($newf);
             }*/
            $this->getFile($url, $local);
            @chmod($local, 0777);
            if (filesize($local) == 0) {
                $this->replace('&nbsp;&nbsp;&nbsp;' . L('_FILE_SIZE_ERROR_'), 'danger');
                // $this->hideProgress();
                exit;
            }
            $this->replace('&nbsp;&nbsp;&nbsp;' . L('_FILE_DOWNLOAD_COMPLETE_'), 'success');
            $this->hideProgress();
        } else {
            $this->write('&nbsp;&nbsp;&nbsp;' . L('_FILE_DOWNLOAD_FAIL_TIP_'), 'danger');
            exit;
        }
    }
/*
 *
 * 远程获取文件
 * $服务端文件url；
 * $path 包含文件名的路径；
 *
 * */
    public function getFile($url, $path, $type = 0)
    {
        if (trim($url) == '') {
            return false;
        }

        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            //使用缓冲的方式。
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
        $size = strlen($content);
        //文件大小
        $fp2 = @fopen($path, 'a');
        dump($content);
        dump(fwrite($fp2, $content));
        fclose($fp2);
        unset($content, $url);
        return $path;
    }

    public function downfile($filename){


        $fileinfo = pathinfo($filename);
        header('Content-type: application/x-'.$fileinfo['extension']);
        header('Content-Disposition: attachment; filename='.$fileinfo['basename']);
        header('Content-Length: '.filesize($filename));
        readfile($thefile);
        exit();

    }




}