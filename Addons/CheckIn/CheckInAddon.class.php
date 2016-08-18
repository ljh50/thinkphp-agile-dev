<?php

namespace Addons\CheckIn;

use Common\Controller\Addon;

/**
 * 签到插件
 * @author 嘉兴想天信息科技有限公司
 */
class CheckInAddon extends Addon
{

    public $info = array(
        'name' => 'CheckIn',
        'title' => '签到',
        'description' => '签到插件',
        'status' => 1,
        'author' => 'xjw129xjt(肖骏涛)',
        'version' => '0.1'
    );


    public function install()
    {
        $prefix = C("DB_PREFIX");
        D()->execute("DROP TABLE IF EXISTS `{$prefix}checkin`");
        D()->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}checkin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQL
        );

        D()->execute(<<<SQL
        ALTER TABLE  `{$prefix}member` ADD  `con_check` INT NOT NULL DEFAULT  '0',
ADD  `total_check` INT NOT NULL DEFAULT  '0';
SQL
        );


        return true;
    }

    public function uninstall()
    {

        $prefix = C("DB_PREFIX");
        D()->execute("DROP TABLE IF EXISTS `{$prefix}checkin`");

        D()->execute(<<<SQL
ALTER TABLE `{$prefix}member`
  DROP `con_check`,
  DROP `total_check`;
SQL
        );
        return true;
    }


    public function checkIn($param)
    {
        $model = $this->checkInModel();
        $uid = is_login();
        $check = $model->getCheck($uid);
        $this->assign('check', $check);
        $this->assignDate();
        $html = $this->rank('today');
        $this->assign('html', $html);
        $this->display('View/checkin');

    }

    private function checkInModel()
    {
        return D('Addons://CheckIn/CheckIn');
    }

    public function rank($type)
    {
        $time = get_some_day(0);
        $rank = S('check_rank_' . $type . '_' . $time);
        if (empty($rank)) {
            $model = $this->checkInModel();
            $rank = $model->getRank($type);
            S('check_rank_' . $type . '_' . $time, $rank, 300);
        }
        foreach($rank as &$v){
            $v['user'] = query_user(array('avatar32','avatar64','space_url', 'nickname', 'uid',), $v['uid']);
        }
        unset($v);
        $this->assign('rank', $rank);
        $this->assign('type', $type);
        $this->assign('type_ch', $type == 'con' ? '连签' : '累签');
        $html = $this->fetch('View/rank');
        return $html;
    }

    private function assignDate()
    {
        $week = date('w');
        switch ($week) {
            case '0':
                $week = '周日';
                break;
            case '1':
                $week = '周一';
                break;
            case '2':
                $week = '周二';
                break;
            case '3':
                $week = '周三';
                break;
            case '4':
                $week = '周四';
                break;
            case '5':
                $week = '周五';
                break;
            case '6':
                $week = '周六';
                break;
        }
        $this->assign('day', date('Y.m.d'));
        $this->assign('week', $week);

    }


    public function doCheckIn()
    {
        $cachedir=RUNTIME_PATH."/Temp/";
        if ($dh = opendir($cachedir)) {
            while (($file = readdir($dh)) !== false) {
                unlink($cachedir.$file);
            }
            closedir($dh);
        }
        $time = get_some_day(0);
        $uid = is_login();

        $model = $this->checkInModel();
        $memberModel = D('Member');
        $check = $model->getCheck($uid);
        if (!$check) {
            $model->addCheck($uid);
            $memberModel->where(array('uid' => $uid))->setInc('total_check');
            //签到积分奖励 从addons表获得设置的类型和积分数
            $getscore_str=M('addons');
            $score_arr=$getscore_str->where('name="CheckIn"')->field('config')->select();
            $score_str = $score_arr[0];
            $res = '';
            foreach($score_str as $v2){
                $res = $v2;

            }
            unset($v2);
            $res1=$res;
            $count=substr_count($res,',');
            for($i=0;$i<$count;$i++) {
                $str_type_1 = strstr($res, ',"');
                $str_type_2 = strstr($str_type_1, 'score');
                $str_k1 = strpos($str_type_2, '"');//获得,的位置
                $score_type = substr($str_type_2, 0, $str_k1);
                $res = $str_type_2;//类型
                //获得数值
                if ($i == ($count - 1)) {
                    $str_type_3 = strstr($res1, ',"');
                    $str_type_4 = strstr($str_type_3, ':');
                    $str_k2 = strpos($str_type_4, '}');//获得,的位置
                    $str_k3 = $str_k2 - 3;
                    if ($str_k3 == 0) {
                        $score_value = 0;
                    } else {
                        $score_value = intval(substr($str_type_4, 2, $str_k3));
                    }

                } else {
                    $str_type_3 = strstr($res1, ',"');
                    $str_type_4 = strstr($str_type_3, ':');
                    $str_k2 = strpos($str_type_4, ',');//获得,的位置
                    $str_k3 = $str_k2 - 3;
                    if ($str_k3 == 0) {
                        $score_value = 0;
                    } else {
                        $score_value = intval(substr($str_type_4, 2, $str_k3));
                    }

                }
                $res1 = $str_type_4;
                $t = substr($score_type, 5, strlen($score_type) - 5);
                $memberModel->where(array('uid' => $uid))->setInc($score_type, $score_value);
                //积分日志
                if ($score_value != 0) {
                    $nowscore = $memberModel->where(array('uid' => $uid))->getfield($score_type);
                    $scorelogdata = M('score_log');
                    $data['uid'] = $uid;
                    $data['type'] = $t;
                    $data['action'] = 'inc';
                    $data['value'] = $score_value;
                    $data['finally_value'] = $nowscore;
                    $nowtime = date("ymdhis", time());
                    $data['create_time'] = $nowtime;
                    $data['remark'] = "签到[$score_type]类型积分+[$score_value]";
                    $data['model'] = 'weibo';
                    $data['record_id'] = $uid;
                    $scorelog = $scorelogdata->add($data);
                    /*    $scoreModel= D('Ucenter/Score') ;
                 $scoreModel->setUserScore($uid, $score_value,$score_type,inc, 'weibo',$uid,'签到['.$score_type.']类型积分+['.$score_value.']');
                 $scoreModel->addScoreLog($uid, $score_type, 'inc',$score_value, 'weibo',$uid,'签到['.$score_type.']类型积分+['.$score_value.']');*/
                }
            }
            unset($i);
            $model->checkYesterday($uid);
            clean_query_user_cache($uid, array('con_check', 'total_check'));
            S('check_rank_today_' . $time, null);
            S('check_rank_con_' . $time, null);
            S('check_rank_total_' . $time, null);
            return true;
        } else {
            return false;
        }
    }


    public function handleAction($param)
    {

        $config = $this->getConfig();
        if (!empty($config['action'])) {
            $action_info = M('Action')->getByName($config['action']);
            if ($action_info['id'] == $param['action_id']) {
                $res = $this->doCheckIn();
                if($res){
                    $param['log_score'] .= '签到成功!';
                    return $res;
                }
            }
        }
        return false;

    }


}