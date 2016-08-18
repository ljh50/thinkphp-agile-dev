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
INSERT INTO `ocenter_issue_content` VALUES ('29', '大洼大洼', '<p>大娃娃的</p>', '5', '21', '14', '1', '0', '1430704938', '1430704938', '1', 'http://');
INSERT INTO `ocenter_issue_content` VALUES ('30', '测试代码高亮', '<pre class=\"brush:php;toolbar:false\">public function reload()\r\n{\r\n    $modules = $this->select();\r\n    foreach ($modules as $m) {\r\n        if (file_exists(APP_PATH . \'/\' . $m[\'name\'] . \'/Info/info.php\')) {\r\n            $info = array_merge($m, $this->getInfo($m[\'name\']));\r\n            $this->save($info);\r\n        }\r\n    }\r\n    $this->cleanModulesCache();\r\n}</pre><p><br/></p>', '2', '22', '14', '1', '0', '1430705543', '1430705543', '1', 'http://');
