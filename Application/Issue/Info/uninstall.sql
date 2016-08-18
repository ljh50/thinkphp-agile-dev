DROP TABLE IF EXISTS `uctoo_issue`;
DROP TABLE IF EXISTS `uctoo_issue_content`;


/*删除menu相关数据*/
set @tmp_id=0;
select @tmp_id:= id from `uctoo_menu` where `title` = '专辑';
delete from `uctoo_menu` where  `id` = @tmp_id or (`pid` = @tmp_id  and `pid` !=0);
delete from `uctoo_menu` where  `title` = '专辑';
/*删除相应的后台菜单*/
delete from `uctoo_menu` where  `url` like 'Issue/%';
/*删除相应的权限节点*/
delete from `uctoo_auth_rule` where  `module` = 'Issue';