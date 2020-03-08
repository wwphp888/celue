-- -----------------------------
-- 导出时间 `2018-12-13 14:12:17`
-- -----------------------------

-- -----------------------------
-- 表结构 `tsp_vip`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_vip`;
CREATE TABLE `tsp_vip` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `vip_name` varchar(50) NOT NULL COMMENT '用户名',
  `vip_password` varchar(100) NOT NULL COMMENT '密码',
  `vip_paypassword` varchar(100) NOT NULL COMMENT '支付密码',
  `vip_phone` varchar(11) NOT NULL COMMENT '手机号',
  `vip_idcard` int(30) NOT NULL COMMENT '身份证号',
  `vip_realname` varchar(50) NOT NULL COMMENT '真实姓名',
  `vip_money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `register_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '注册时间',
  `last_login_time` timestamp NULL DEFAULT NULL COMMENT '最后一次登录时间',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '0,禁用,1,正常',
  `recommendCode` varchar(50) NOT NULL COMMENT '推荐码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- -----------------------------
-- 表数据 `tsp_vip`
-- -----------------------------
INSERT INTO `tsp_vip` VALUES ('2', '龟田志斌', '$2y$10$/PQIKuLXsUfKwwKHTnhfkeOciwb4AQb6Zs1K9/ssyOokKdOfLAkq6', '$2y$10$9ax/hgQ3ehpcut/tFqfx4etZu0mVrs96Hg57/ZILNgssaQ9PzRGje', '15668330912', '111222', '', '110.00', '2018-12-10 14:50:47', '', '1', 'A001');
INSERT INTO `tsp_vip` VALUES ('4', '柳生但马守', '$2y$10$/ZpsZmf15ZQYsM7C1f64pOCBd65BvI3al6SW5ctjDWhymvR/LsM3i', '$2y$10$UuXE.kpzAHip.M1FQqfp7.3qCuJpYRI.2GIezg.IHiG4kcobJuhhy', '15668330913', '0', '', '0.00', '0000-00-00 00:00:00', '', '1', '022');

-- -----------------------------
-- 表结构 `tsp_vip_bank`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_vip_bank`;
CREATE TABLE `tsp_vip_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_vip` int(11) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_number` varchar(255) DEFAULT NULL,
  `bank_detail` varchar(255) DEFAULT NULL,
  `bank_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- -----------------------------
-- 表数据 `tsp_vip_bank`
-- -----------------------------

-- -----------------------------
-- 表结构 `tsp_vip_recharge`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_vip_recharge`;
CREATE TABLE `tsp_vip_recharge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recharge_amount` decimal(15,2) DEFAULT NULL,
  `recharge_status` tinyint(2) DEFAULT '0' COMMENT '0,未付款,1,成功,2,失败,3,手动充值',
  `recharge_vip` int(11) DEFAULT NULL,
  `recharge_type` int(11) DEFAULT NULL,
  `recharge_order` varchar(255) DEFAULT NULL COMMENT '订单号',
  `recharge_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- -----------------------------
-- 表数据 `tsp_vip_recharge`
-- -----------------------------
INSERT INTO `tsp_vip_recharge` VALUES ('1', '10.00', '3', '2', '1', '1231231231', '');

-- -----------------------------
-- 表结构 `tsp_vip_withdraw`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_vip_withdraw`;
CREATE TABLE `tsp_vip_withdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `withdraw_vip` int(11) DEFAULT NULL,
  `withdraw_amount` decimal(15,2) DEFAULT NULL,
  `withdraw_status` tinyint(2) DEFAULT '0' COMMENT '0,待审核,1,成功,2,失败',
  `withdraw_time` timestamp NULL DEFAULT NULL,
  `withdraw_card` varchar(255) DEFAULT NULL COMMENT '卡号',
  `withdraw_realname` varchar(255) DEFAULT NULL COMMENT '姓名',
  `withdraw_bank` varchar(255) DEFAULT NULL COMMENT '银行',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- -----------------------------
-- 表数据 `tsp_vip_withdraw`
-- -----------------------------

-- -----------------------------
-- 表结构 `tsp_vip_record`
-- -----------------------------
DROP TABLE IF EXISTS `tsp_vip_record`;
CREATE TABLE `tsp_vip_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_vip` int(11) DEFAULT NULL,
  `record_affect` decimal(15,2) DEFAULT NULL,
  `record_info` varchar(255) DEFAULT NULL,
  `record_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- -----------------------------
-- 表数据 `tsp_vip_record`
-- -----------------------------
