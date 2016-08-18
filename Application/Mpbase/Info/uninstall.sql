DROP TABLE IF EXISTS `uctoo_member_public`;
DROP TABLE IF EXISTS `uctoo_custom_menu`;

/*删除menu相关数据*/
set @tmp_id=0;
select @tmp_id:= id from `uctoo_menu` where `title` = '基础设置';
delete from `uctoo_menu` where  `id` = @tmp_id or (`pid` = @tmp_id  and `pid` !=0);
delete from `uctoo_menu` where  `title` = '基础设置';
/*删除相应的后台菜单*/
delete from `uctoo_menu` where  `url` like 'Mpbase/%';
delete from `uctoo_menu` where  `url` like 'CustomMenu/%';
/*删除相应的权限节点*/
delete from `uctoo_auth_rule` where  `module` = 'Mpbase';
/*删除相应的后台菜单*/



