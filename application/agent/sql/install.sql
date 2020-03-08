-- -----------------------------
-- 导出时间 `2019-01-23 14:31:25`
-- -----------------------------

-- -----------------------------
-- 表结构 `tsp_agent`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_agent`;
CREATE TABLE `tsp_agent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_code` varchar(50) NOT NULL COMMENT '机构码',
  `agent_username` varchar(100) NOT NULL COMMENT '用户名',
  `agent_password` varchar(100) NOT NULL COMMENT '密码',
  `agent_money` decimal(11,2) NOT NULL COMMENT '余额',
  `agent_phone` varchar(11) NOT NULL COMMENT '手机号',
  `agent_level` tinyint(2) NOT NULL COMMENT '级别',
  `agent_parent` tinyint(2) NOT NULL DEFAULT '0' COMMENT '所属上级',
  `agent_time` timestamp NULL DEFAULT NULL COMMENT '注册时间',
  `agent_realname` varchar(100) DEFAULT NULL COMMENT '真实姓名',
  `agent_idcard` varchar(100) DEFAULT NULL COMMENT '身份证号',
  `agent_bankname` varchar(100) DEFAULT NULL COMMENT '银行名称',
  `agent_banknumber` varchar(50) DEFAULT NULL COMMENT '银行卡号',
  `agent_rate` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- -----------------------------
-- 表数据 `tsp_agent`
-- -----------------------------
INSERT INTO `tsp_agent` VALUES ('1', 'A001', '谷乐多', '$2y$10$/PQIKuLXsUfKwwKHTnhfkeOciwb4AQb6Zs1K9/ssyOokKdOfLAkq6', '144.00', '15668330258', '1', '0', '', '果乐多', '', '', '', '20.00');
INSERT INTO `tsp_agent` VALUES ('2', 'A597', '美乐滋', '$2y$10$Mw5fTJfIqAo9P.Y01j88lOAhXN9tadOeiIRnQv15E/jkXGbuRiuk2', '16.00', '15668330912', '2', '1', '', '', '', '', '', '10.00');
INSERT INTO `tsp_agent` VALUES ('3', 'A008', '特仑苏', '$2y$10$EFb.v0u6oO7V7FPBpHW5.uuo.nArkyzg/pUKm3oBsP7z/RmV0fVfG', '40.00', '15668330911', '3', '2', '', '', '', '', '', '20.00');

-- -----------------------------
-- 表结构 `tsp_agent_withdraw`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_agent_withdraw`;
CREATE TABLE `tsp_agent_withdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `withdraw_agent` int(11) DEFAULT NULL,
  `withdraw_amount` decimal(15,2) DEFAULT NULL,
  `withdraw_status` tinyint(2) DEFAULT '0' COMMENT '0,待审核,1,成功,2,失败',
  `withdraw_time` timestamp NULL DEFAULT NULL,
  `withdraw_card` varchar(255) DEFAULT NULL COMMENT '卡号',
  `withdraw_realname` varchar(255) DEFAULT NULL COMMENT '姓名',
  `withdraw_bank` varchar(255) DEFAULT NULL COMMENT '银行',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- -----------------------------
-- 表数据 `tsp_agent_withdraw`
-- -----------------------------
INSERT INTO `tsp_agent_withdraw` VALUES ('1', '6', '500.00', '2', '', '123456', 'tony', 'huifeng');

-- -----------------------------
-- 表结构 `tsp_agent_record`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_agent_record`;
CREATE TABLE `tsp_agent_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `affect_money` decimal(15,2) NOT NULL,
  `agent_money` decimal(15,2) NOT NULL,
  `record_info` varchar(255) DEFAULT NULL,
  `recod_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

-- -----------------------------
-- 表数据 `tsp_agent_record`
-- -----------------------------
INSERT INTO `tsp_agent_record` VALUES ('1', '1', '4', '200.00', '0.00', '管理员调整', '0');
INSERT INTO `tsp_agent_record` VALUES ('2', '1', '4', '-100.00', '200.00', '管理员调整', '1547695913');
INSERT INTO `tsp_agent_record` VALUES ('3', '1', '4', '120.00', '-100.00', '管理员调整', '1547696309');
INSERT INTO `tsp_agent_record` VALUES ('4', '3', '7', '40.00', '40.00', '成交返佣', '1547791906');
INSERT INTO `tsp_agent_record` VALUES ('5', '2', '7', '16.00', '16.00', '成交返佣', '1547791906');
INSERT INTO `tsp_agent_record` VALUES ('6', '1', '7', '144.00', '144.00', '成交返佣', '1547791906');
INSERT INTO `tsp_agent_record` VALUES ('7', '3', '7', '40.00', '40.00', '成交返佣', '1547792002');
INSERT INTO `tsp_agent_record` VALUES ('8', '2', '7', '16.00', '16.00', '成交返佣', '1547792002');
INSERT INTO `tsp_agent_record` VALUES ('9', '1', '7', '144.00', '144.00', '成交返佣', '1547792002');
INSERT INTO `tsp_agent_record` VALUES ('10', '3', '7', '40.00', '120.00', '成交返佣', '1547792079');
INSERT INTO `tsp_agent_record` VALUES ('11', '2', '7', '16.00', '48.00', '成交返佣', '1547792079');
INSERT INTO `tsp_agent_record` VALUES ('12', '1', '7', '144.00', '432.00', '成交返佣', '1547792079');
INSERT INTO `tsp_agent_record` VALUES ('13', '3', '7', '40.00', '160.00', '成交返佣', '1547792368');
INSERT INTO `tsp_agent_record` VALUES ('14', '2', '7', '16.00', '64.00', '成交返佣', '1547792368');
INSERT INTO `tsp_agent_record` VALUES ('15', '1', '7', '144.00', '576.00', '成交返佣', '1547792368');
INSERT INTO `tsp_agent_record` VALUES ('16', '3', '7', '40.00', '0.00', '成交返佣', '1547792391');
INSERT INTO `tsp_agent_record` VALUES ('17', '2', '7', '16.00', '0.00', '成交返佣', '1547792391');
INSERT INTO `tsp_agent_record` VALUES ('18', '1', '7', '144.00', '0.00', '成交返佣', '1547792391');
