-- phpMyAdmin SQL Dump
-- version 4.0.10.11
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2016-08-10 19:05:06
-- 服务器版本: 5.5.46-log
-- PHP 版本: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `tcl_sdvserver`
--

CREATE DATABASE IF NOT EXISTS `tcl_sdvserver` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tcl_sdvserver`;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_access_point`
--

CREATE TABLE IF NOT EXISTS `sdv_access_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_key` varchar(50) NOT NULL,
  `merchant` varchar(50) NOT NULL,
  `station_type` tinyint(1) DEFAULT '1',
  `phone` varchar(20) DEFAULT NULL,
  `server_version` varchar(10) DEFAULT '',
  `client_version` varchar(10) DEFAULT '',
  `created_date` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未审 1:已审',
  `station_number` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_autoupload`
--

CREATE TABLE IF NOT EXISTS `sdv_autoupload` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ip` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `upload_time` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_caser`
--

CREATE TABLE IF NOT EXISTS `sdv_caser` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '案件ID主键',
  `case_number` varchar(100) NOT NULL COMMENT '案件编号',
  `unit_number` varchar(50) NOT NULL COMMENT '部门编号',
  `alarm_number` varchar(50) NOT NULL COMMENT '接警单号',
  `police_num` varchar(50) NOT NULL COMMENT '警员编号',
  `case_type` int(2) NOT NULL COMMENT '案件类型',
  `happen_time` int(11) NOT NULL COMMENT '案发时间',
  `case_status` int(2) NOT NULL COMMENT '案件状态',
  `case_desc` text COMMENT '案件描述',
  `enntry_time` int(11) NOT NULL COMMENT '录入时间',
  `update_time` int(11) DEFAULT NULL,
  `related_data` text COMMENT '关联的证据',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_config`
--

CREATE TABLE IF NOT EXISTS `sdv_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '配置名(英文小写字母，数字，下划线)',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '配置值',
  `config_type` varchar(50) NOT NULL DEFAULT 'system' COMMENT '配置名类型(默认为system,system类型的配置会自动在控制器初始化时加载)',
  `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态:0:禁用; 1:启用;',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '配置项备注-说明',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`name`),
  KEY `idx_state_config_type` (`state`,`config_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='配置表' AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_device`
--

CREATE TABLE IF NOT EXISTS `sdv_device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_device_log`
--

CREATE TABLE IF NOT EXISTS `sdv_device_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NOT NULL DEFAULT '' COMMENT '设备所操作的功能模块',
  `start_date` int(11) DEFAULT NULL,
  `end_date` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL COMMENT '时间长',
  `station_number` varchar(30) NOT NULL,
  `upload_status` tinyint(1) NOT NULL DEFAULT '0',
  `archive_num` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_import`
--

CREATE TABLE IF NOT EXISTS `sdv_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '文件名称',
  `rename` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '文件类型，值说明：图片 1，视频 2',
  `desc` text,
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL COMMENT '#存入警员编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_information`
--

CREATE TABLE IF NOT EXISTS `sdv_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `police_num` varchar(50) NOT NULL COMMENT '警员编号',
  `equipment_num` varchar(255) NOT NULL COMMENT '设备编号',
  `file_name` varchar(255) NOT NULL COMMENT '文件名',
  `file_alias` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名别名',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(单位:Byte)',
  `type` varchar(255) NOT NULL COMMENT '文件类型:log,photo,audio,video',
  `category_id` int(6) NOT NULL DEFAULT '1' COMMENT '类别id,关联类别表 sdv_category 主键,默认1表示无类别',
  `level` int(11) NOT NULL COMMENT '重要级别',
  `record_date` int(11) NOT NULL COMMENT '创建时间',
  `upload_date` int(11) NOT NULL COMMENT '导入时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未上传,1:上传',
  `station_id` varchar(30) NOT NULL DEFAULT '' COMMENT '工作站编号',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '资源真实目录',
  `file_path` varchar(255) NOT NULL DEFAULT '' COMMENT '证据文件路径(备用)',
  `totalTime` int(11) NOT NULL DEFAULT '0' COMMENT '音频视频文件播放时长',
  `existed_file` tinyint(1) NOT NULL DEFAULT '0' COMMENT '文件是否已经存在服务器上了',
  `archive_num` varchar(100) NOT NULL COMMENT '文件编号，唯一',
  `del_status` int(2) NOT NULL DEFAULT '0' COMMENT '删除状态',
  `unit_number` varchar(30) NOT NULL DEFAULT '' COMMENT '部门编号',
  `trans_filename` varchar(255) DEFAULT '' COMMENT '转码后文件名',
  `trans_status` int(2) DEFAULT '0',
  `status2` tinyint(1) DEFAULT '0',
  `alarm_number` varchar(50) DEFAULT '' COMMENT '接警单号',
  `case_number` varchar(100) DEFAULT '',
  `special_file` tinyint(1) DEFAULT '0',
  `alarm_number_temp` varchar(50) DEFAULT '' COMMENT '接警单号临时字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uiq_archive_num` (`archive_num`) USING BTREE,
  KEY `police_num` (`police_num`),
  KEY `record_date` (`record_date`),
  KEY `del_status` (`del_status`),
  KEY `alarm_number` (`alarm_number`),
  KEY `alarm_number_temp` (`alarm_number_temp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='资料信息表' AUTO_INCREMENT=134 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_information_desc`
--

CREATE TABLE IF NOT EXISTS `sdv_information_desc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text,
  `remark` text,
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `archive_num` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_log`
--

CREATE TABLE IF NOT EXISTS `sdv_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NOT NULL COMMENT '所操作的功能模块',
  `opt` varchar(255) NOT NULL,
  `ip` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL COMMENT '#存入警员编号',
  `desc` varchar(255) DEFAULT '0' COMMENT '操作项或者备注',
  `upload_status` tinyint(1) NOT NULL DEFAULT '0',
  `dept_number` varchar(30) DEFAULT NULL COMMENT '部门编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=263963 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_logws`
--

CREATE TABLE IF NOT EXISTS `sdv_logws` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_matche`
--

CREATE TABLE IF NOT EXISTS `sdv_matche` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `equipment_num` varchar(255) NOT NULL,
  `police_num` varchar(255) NOT NULL,
  `Number` varchar(40) DEFAULT NULL,
  `status` int(2) DEFAULT '2',
  `spare` varchar(40) DEFAULT NULL,
  `binding_status` int(2) DEFAULT '2',
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL COMMENT '#存入警员编号',
  `watermark` varchar(255) DEFAULT NULL,
  `resolution` varchar(255) DEFAULT NULL,
  `buy_date` varchar(255) DEFAULT NULL,
  `device_type` varchar(50) NOT NULL,
  `upload_status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_memory`
--

CREATE TABLE IF NOT EXISTS `sdv_memory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memory_cycle` varchar(255) DEFAULT NULL,
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL COMMENT '#存入警员编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_module`
--

CREATE TABLE IF NOT EXISTS `sdv_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '模块名称',
  `identifier` varchar(255) NOT NULL COMMENT '唯一标识符',
  `parent_id` int(11) NOT NULL COMMENT '父节点',
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL COMMENT '#存入警员编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_notice`
--

CREATE TABLE IF NOT EXISTS `sdv_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `created_date` int(11) NOT NULL,
  `unit_number` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `send_date` int(11) DEFAULT NULL,
  `deadline` int(11) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `number_all` varchar(200) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `status` int(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_permission`
--

CREATE TABLE IF NOT EXISTS `sdv_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `module_ids` text NOT NULL COMMENT '拥有的功能模块',
  `extend` int(11) DEFAULT NULL COMMENT '额外权限：查看本工作站所有资料权限 1，查看下属工作站权限 2',
  `del_role` int(11) DEFAULT NULL COMMENT '删除资料权限',
  `ws_extend` int(11) NOT NULL DEFAULT '1' COMMENT '工作站额外权限',
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL COMMENT '#存入警员编号',
  `act_info` varchar(10) NOT NULL DEFAULT '0000000' COMMENT '1~7位表示7中操作权限',
  `check_data_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '数据权限, 0表示本级 ,1 表示所有的下级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_police_classification`
--

CREATE TABLE IF NOT EXISTS `sdv_police_classification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '警种名称',
  `created_date` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `created_by` varchar(30) NOT NULL DEFAULT '' COMMENT '#存入警员编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_role`
--

CREATE TABLE IF NOT EXISTS `sdv_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `role_level` int(6) NOT NULL DEFAULT '0' COMMENT '角色级别：值越大，级别越高，默认最小0',
  `created_date` int(11) NOT NULL,
  `created_by` varchar(30) NOT NULL COMMENT '#存入警员编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_stations`
--

CREATE TABLE IF NOT EXISTS `sdv_stations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ip` int(11) unsigned NOT NULL,
  `created_date` int(11) NOT NULL,
  `unit_number` varchar(40) DEFAULT NULL,
  `online_date` int(11) NOT NULL DEFAULT '0',
  `storage_size` bigint(20) NOT NULL DEFAULT '0',
  `storage_rest` bigint(20) NOT NULL DEFAULT '0',
  `memory_rate` float NOT NULL DEFAULT '0',
  `cpu_rate` float NOT NULL DEFAULT '0',
  `mac_addr` varchar(40) DEFAULT NULL,
  `client_version` varchar(50) DEFAULT NULL,
  `server_version` varchar(50) DEFAULT NULL,
  `address` varchar(40) DEFAULT NULL,
  `manager` varchar(40) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `station_number` varchar(30) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `ftpIp` int(11) DEFAULT NULL,
  `ftp_pass` varchar(30) DEFAULT NULL,
  `issued` varchar(2) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `ftp_user` varchar(30) DEFAULT NULL,
  `upgrade` varchar(10) DEFAULT NULL,
  `merchant` varchar(50) DEFAULT NULL,
  `restart_shutdown` tinyint(1) NOT NULL DEFAULT '3' COMMENT '1 重启 2关机 3 正常',
  `online_time` int(11) NOT NULL DEFAULT '0' COMMENT '在线时长标记',
  `online_datetime` int(11) NOT NULL DEFAULT '0' COMMENT '在线时长',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_stations_log`
--

CREATE TABLE IF NOT EXISTS `sdv_stations_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `station_id` int(11) NOT NULL,
  `log_type` tinyint(1) NOT NULL DEFAULT '1',
  `log_filename` varchar(30) NOT NULL,
  `log_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_unit`
--

CREATE TABLE IF NOT EXISTS `sdv_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Number` varchar(40) NOT NULL,
  `Desc` varchar(255) DEFAULT NULL,
  `parent_number` varchar(40) NOT NULL DEFAULT '0',
  `contact` varchar(30) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `unit_level` int(11) NOT NULL DEFAULT '1',
  `created_date` int(11) NOT NULL,
  `child_count` int(11) NOT NULL DEFAULT '0',
  `station_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `Number` (`Number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=180 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_upload`
--

CREATE TABLE IF NOT EXISTS `sdv_upload` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `level` int(11) DEFAULT NULL COMMENT '自动上传资料级别',
  `created_date` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_user`
--

CREATE TABLE IF NOT EXISTS `sdv_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `police_num` varchar(255) NOT NULL COMMENT '警员编号',
  `password` varchar(255) NOT NULL DEFAULT '123456',
  `name` varchar(255) NOT NULL COMMENT '警员姓名',
  `sex` int(11) NOT NULL COMMENT '值说明：男 1，女 2',
  `mobile_num` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL COMMENT '所属角色',
  `desc` varchar(255) DEFAULT NULL,
  `created_date` int(11) NOT NULL,
  `reg_date` int(11) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `status` int(11) DEFAULT '0' COMMENT '0代表无修改，1代表已修改',
  `Number` varchar(30) NOT NULL,
  `police_classification_id` INT(5) NOT NULL DEFAULT  '1' COMMENT  '警种',
  PRIMARY KEY (`id`),
  KEY `police_num` (`police_num`),
  KEY `Number` (`Number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=108 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_workstation`
--

CREATE TABLE IF NOT EXISTS `sdv_workstation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `ip` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `uploadtask_time` varchar(20) DEFAULT NULL,
  `station_number` varchar(30) NOT NULL,
  `manager` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `net_way` tinyint(1) NOT NULL DEFAULT '1',
  `unit_number` varchar(30) DEFAULT NULL,
  `client_version` double DEFAULT NULL,
  `server_version` double DEFAULT NULL,
  `status` int(2) DEFAULT NULL,
  `address` varchar(40) DEFAULT NULL,
  `type` int(2) DEFAULT NULL,
  `cloudIp` int(11) unsigned NOT NULL COMMENT '云服务IP',
  `storage_time` int(11) NOT NULL,
  `wslevel` int(2) DEFAULT NULL,
  `access_key` varchar(50) DEFAULT NULL,
  `merchant` varchar(50) DEFAULT NULL,
  `uploadtask_end` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `sdv_category`
--
CREATE TABLE `sdv_category` (
  `id` int(6) NOT NULL AUTO_INCREMENT COMMENT '证据类别id',
  `pid` int(6) NOT NULL DEFAULT '0' COMMENT '证据类别父类id,0代表顶级类别，且顶级类别pid必须为0',
  `cate_name` varchar(50) NOT NULL DEFAULT '' COMMENT '证据类别名称',
  `del_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:类别正常状态,1类别已经被删除(这里为了防止类别被删除了相关联的证据找不到类别，所以这里不做真正的删除delete操作)',
  `storage_time` int(6) NOT NULL DEFAULT '0' COMMENT '该类别证据保存时间(单位:天),默认值为0,表示永久保存',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='证据分类表';


-- --------------------------------------------------------

--
-- 表的结构 `sdv_information_third`
--

CREATE TABLE `sdv_information_third` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `police_num` varchar(50) NOT NULL COMMENT '警员编号',
  `police_name` varchar(255) NOT NULL DEFAULT '' COMMENT '警员姓名',
  `equipment_num` varchar(255) NOT NULL COMMENT '设备编号',
  `file_name` varchar(255) NOT NULL COMMENT '文件名称',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小(单位:Byte)',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '文件类型(video,audio,photo,log)',
  `level` int(6) NOT NULL DEFAULT '1' COMMENT '重要级别(1:低,2:中,3:高)',
  `record_date` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `upload_date` int(11) NOT NULL DEFAULT '0' COMMENT '导入时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未上传,1:上传',
  `revise` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审查状态,未审查:0,已审查:1',
  `revised_by` varchar(50) NOT NULL DEFAULT '' COMMENT '审查人(警员编号)',
  `station_id` varchar(50) DEFAULT '' COMMENT '工作站编号',
  `path` varchar(255) DEFAULT '' COMMENT '资源目录',
  `url` varchar(255) DEFAULT '' COMMENT '资源http路径',
  `totalTime` int(11) NOT NULL DEFAULT '0',
  `existed_file` tinyint(1) NOT NULL DEFAULT '0' COMMENT '文件是否已经存在',
  `archive_num` varchar(50) NOT NULL COMMENT '文件编号，唯一',
  `del_status` int(2) NOT NULL DEFAULT '0' COMMENT '删除状态',
  `unit_number` varchar(30) NOT NULL DEFAULT '' COMMENT '部门编号',
  `description` text NOT NULL DEFAULT '' COMMENT '备注',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '标记',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uiq_archive_num` (`archive_num`) USING BTREE,
  KEY `police_num` (`police_num`),
  KEY `record_date` (`record_date`),
  KEY `del_status` (`del_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='西班牙工作站数据汇总表';


--
-- 表的结构 `sdv_information_queue`
--

CREATE TABLE IF NOT EXISTS `sdv_information_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `opt` int(2) DEFAULT '1' COMMENT '1:insert;2:update;',
  `deal_times` int (2) DEFAULT '0' COMMENT '处理次数',
  `_MASK_SYNC_V2` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据更新时间',
  PRIMARY KEY (`id`, `opt`),
  KEY `idx_deal_times` (`deal_times`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT 'sdv_information表记录写操作数据，用于队列处理,同步数据';




-- 触发器
DELIMITER $

CREATE TRIGGER tri_information_update
AFTER UPDATE ON sdv_information
FOR EACH ROW
BEGIN
REPLACE INTO sdv_information_queue (`id`, `opt`, `deal_times`) VALUES(NEW.id, 2, 0);
END$

DELIMITER ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;





















