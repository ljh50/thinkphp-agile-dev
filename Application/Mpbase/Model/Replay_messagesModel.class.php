<?php
/**
 * Created by PhpStorm.
 * User: Uctoo-Near
 * Date: 2016/4/12
 * Time: 16:37
 */

namespace Mpbase\Model;
use Think\Model;

class Replay_messagesModel extends Model{
    protected $_validate = array(
        array('title','require','请填入名称 '),
        array('ms_id','require','发送内容失败，请联系管理员 '),
        array('type','require','错误，请联系管理员'),
        array('mp_id','require','公众号id错误，请联系管理员')
    );


//    protected $_auto = array (
//        array('mp_id','get_mpid',3,'function'),
//    );










}