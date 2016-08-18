-- -----------------------------
-- 表结构 `ocenter_issue`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `allow_post` tinyint(4) NOT NULL,
  `pid` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;


-- -----------------------------
-- 表结构 `ocenter_issue_content`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `ocenter_issue_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `view_count` int(11) NOT NULL COMMENT '阅读数量',
  `cover_id` int(11) NOT NULL COMMENT '封面图片id',
  `issue_id` int(11) NOT NULL COMMENT '所在专辑',
  `uid` int(11) NOT NULL COMMENT '发布者id',
  `reply_count` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `status` tinyint(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='专辑内容表';

-- -----------------------------
-- 表内记录 `ocenter_issue`
-- -----------------------------
INSERT INTO `ocenter_issue` VALUES ('13', '默认专辑', '1409712474', '1409712467', '1', '0', '0', '0');
INSERT INTO `ocenter_issue` VALUES ('14', '默认二级', '1409712480', '1409712475', '1', '0', '13', '0');
-- -----------------------------
-- 表内记录 `ocenter_issue_content`
-- -----------------------------
INSERT INTO `ocenter_issue_content` VALUES ('29', 'OpenSNS官方订制门店', '<p><span style=\"color: rgb(53, 53, 53); font-family: &#39;Microsoft YaHei&#39;; font-size: 13px; line-height: 20px; background-color: rgb(255, 255, 255);\">嘉兴奕想信息技术有限公司，opensns官方子公司，是嘉兴想天信息科技有限公司为opensns二次开发专门申请的公司。</span></p>', '7', '3', '14', '1', '0', '1430704938', '1452479973', '1', 'http://os.opensns.cn/appstore/index/shop/id/102.html');
INSERT INTO `ocenter_issue_content` VALUES ('30', 'OpenSNS官方旗舰店', '<p>OpenCenter和OpenSNS开发商-嘉兴想天信息科技有限公司 官方店，提供卓越品质的服务，服务内容包含二次开发。</p><pre class=\"brush:php;toolbar:false\">public&nbsp;function&nbsp;reload(){\r\n&nbsp;&nbsp;$modules&nbsp;=&nbsp;$this-&gt;select();\r\n&nbsp;&nbsp;foreach&nbsp;($modules&nbsp;as&nbsp;$m)&nbsp;{\r\n&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(file_exists(APP_PATH&nbsp;.&nbsp;&#39;/&#39;&nbsp;.&nbsp;$m[&#39;name&#39;]&nbsp;.&nbsp;&#39;/Info/info.php&#39;))&nbsp;{\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$info&nbsp;=&nbsp;array_merge($m,&nbsp;$this-&gt;getInfo($m[&#39;name&#39;]));&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$this-&gt;save($info);&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;&nbsp;&nbsp;&nbsp;\r\n&nbsp;&nbsp;}&nbsp;&nbsp;&nbsp;&nbsp;\r\n&nbsp;&nbsp;$this-&gt;cleanModulesCache();\r\n}</pre>', '13', '2', '14', '1', '0', '1430705543', '1452480316', '1', 'http://os.opensns.cn/appstore/index/shop/id/1.html');
