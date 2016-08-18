<?php


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;

class PeopleController extends AdminController
{
    public function config()
    {
        $builder = new AdminConfigBuilder();
        $data = $builder->handleConfig();
        $builder->title(L('_BASIC_CONF_'));
        $data['MAX_SHOW_HEIGHT'] = $data['MAX_SHOW_HEIGHT'] ? $data['MAX_SHOW_HEIGHT'] :160;
        $builder->keyInteger('MAX_SHOW_HEIGHT', L('_MAX_SHOW_HEIGHT_'))->keyDefault('MAX_SHOW_HEIGHT',160);

        $role_list=M('Role')->where(array('status'=>1))->field('id,title')->select();
        foreach($role_list as &$val){
            $val=array('data-id' => $val['id'], 'title' => $val['title']);
        }
        unset($val);
        $default = array(array('data-id' => 'disable', 'title' => L('_DISABLE_'), 'items' => $role_list), array('data-id' => 'enable', 'title' => L('_ENABLED_'), 'items' => array()));
        $builder->keyKanban('SHOW_ROLE_TAB', L('_IDENTITY_TAB_'),L('_IDENTITY_TAB_AFFIX_'));
        $data['SHOW_ROLE_TAB'] = $builder->parseKanbanArray($data['SHOW_ROLE_TAB'], $role_list, $default);
        $builder->group(L('_BASIC_CONF_'), 'MAX_SHOW_HEIGHT,SHOW_ROLE_TAB');

        $data['USER_SHOW_TITLE1'] = $data['USER_SHOW_TITLE1'] ? $data['USER_SHOW_TITLE1'] : L('_ACTIVE_MEMBER_');
        $data['USER_SHOW_COUNT1'] = $data['USER_SHOW_COUNT1'] ? $data['USER_SHOW_COUNT1'] : 5;
        $data['USER_SHOW_ORDER_FIELD1'] = $data['USER_SHOW_ORDER_FIELD1'] ? $data['USER_SHOW_ORDER_FIELD1'] : 'score1';
        $data['USER_SHOW_ORDER_TYPE1'] = $data['USER_SHOW_ORDER_TYPE1'] ? $data['USER_SHOW_ORDER_TYPE1'] : 'desc';
        $data['USER_SHOW_CACHE_TIME1'] = $data['USER_SHOW_CACHE_TIME1'] ? $data['USER_SHOW_CACHE_TIME1'] : '600';


        $data['USER_SHOW_TITLE2'] = $data['USER_SHOW_TITLE2'] ? $data['USER_SHOW_TITLE2'] : L('_NEW_MEMBER_');
        $data['USER_SHOW_COUNT2'] = $data['USER_SHOW_COUNT2'] ? $data['USER_SHOW_COUNT2'] : 5;
        $data['USER_SHOW_ORDER_FIELD2'] = $data['USER_SHOW_ORDER_FIELD2'] ? $data['USER_SHOW_ORDER_FIELD2'] : 'reg_time';
        $data['USER_SHOW_ORDER_TYPE2'] = $data['USER_SHOW_ORDER_TYPE2'] ? $data['USER_SHOW_ORDER_TYPE2'] : 'desc';
        $data['USER_SHOW_CACHE_TIME2'] = $data['USER_SHOW_CACHE_TIME2'] ? $data['USER_SHOW_CACHE_TIME2'] : '600';


        $score=D("Ucenter/Score")->getTypeList(array('status'=>1));
        $order['reg_time']=L('_REGISTER_TIME_');
        $order['last_login_time']=L('_LAST_LOGIN_TIME_');


        foreach ($score as $s) {
            $order['score'.$s['id']]='【'.$s['title'].'】';
        }

        $builder->keyText('USER_SHOW_TITLE1', L('_TITLE_NAME_'), L('_BLOCK_TITLE_'));
        $builder->keyText('USER_SHOW_COUNT1', L('_SHOW_PEOPLE_'), L('_TIP_AFTER_ENABLED_'));
        $builder->keyRadio('USER_SHOW_ORDER_FIELD1', L('_SORT_NUMBER_'), L('_SHOW_SORT_STYLE_'), $order);
        $builder->keyRadio('USER_SHOW_ORDER_TYPE1', L('_SORT_STYLE_'),L('_SHOW_SORT_STYLE_'), array('desc' => L('_COUNTER_'), 'asc' => L('_DIRECT_')));
        $builder->keyText('USER_SHOW_CACHE_TIME1', L('_CACHE_TIME_'), L('_TIP_CACHE_TIME_'));

        $builder->keyText('USER_SHOW_TITLE2', L('_TITLE_NAME_'), L('_BLOCK_TITLE_'));
        $builder->keyText('USER_SHOW_COUNT2', L('_SHOW_PEOPLE_'), L('_TIP_AFTER_ENABLED_'));
        $builder->keyRadio('USER_SHOW_ORDER_FIELD2', L('_SORT_NUMBER_'), L('_SHOW_SORT_STYLE_'), $order);
        $builder->keyRadio('USER_SHOW_ORDER_TYPE2', L('_SORT_STYLE_'), L('_SHOW_SORT_STYLE_'), array('desc' => L('_COUNTER_'), 'asc' => L('_DIRECT_')));
        $builder->keyText('USER_SHOW_CACHE_TIME2', L('_CACHE_TIME_'), L('_TIP_CACHE_TIME_'));



        $builder->group(L('_HOME_SHOW_LEFT_'), 'USER_SHOW_TITLE1,USER_SHOW_COUNT1,USER_SHOW_ORDER_FIELD1,USER_SHOW_ORDER_TYPE1,USER_SHOW_CACHE_TIME1');
        $builder->group(L('_HOME_SHOW_RIGHT_'), 'USER_SHOW_TITLE2,USER_SHOW_COUNT2,USER_SHOW_ORDER_FIELD2,USER_SHOW_ORDER_TYPE2,USER_SHOW_CACHE_TIME2');
        $builder->data($data);
        $builder->buttonSubmit();
        $builder->display();
    }

}