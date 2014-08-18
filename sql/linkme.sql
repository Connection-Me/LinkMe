-- phpMyAdmin SQL Dump
-- version 2.11.2.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 年 08 月 15 日 06:17
-- 服务器版本: 5.0.45
-- PHP 版本: 5.2.5
--
-- 20140815
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 数据库: `linkme`
--

-- --------------------------------------------------------

--
-- 表的结构 `activity`
--

CREATE TABLE `activity` (
  `id` int(11) NOT NULL auto_increment COMMENT 'aid',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='活动表' AUTO_INCREMENT=1 ;

--
-- 导出表中的数据 `activity`
--


-- --------------------------------------------------------

--
-- 表的结构 `friend`
--

CREATE TABLE `friend` (
  `id` int(11) NOT NULL auto_increment COMMENT 'id',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='好友表' AUTO_INCREMENT=1 ;

--
-- 导出表中的数据 `friend`
--


-- --------------------------------------------------------

--
-- 表的结构 `group`
--

CREATE TABLE `group` (
  `id` int(11) NOT NULL auto_increment COMMENT 'group_id',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='群组表' AUTO_INCREMENT=1 ;

--
-- 导出表中的数据 `group`
--


-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment COMMENT 'id',
  `uuid` varchar(20) collate utf8_unicode_ci NOT NULL COMMENT '用户id',
  `username` varchar(20) collate utf8_unicode_ci NOT NULL COMMENT '账户名',
  `password` varchar(20) collate utf8_unicode_ci NOT NULL COMMENT '密码(经加密)',
  `weibo` varchar(50) collate utf8_unicode_ci default NULL COMMENT '微博id',
  `wechat` varchar(50) collate utf8_unicode_ci default NULL COMMENT '微信id',
  `nickname` varchar(20) collate utf8_unicode_ci default NULL COMMENT '昵称(可中文)',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表' AUTO_INCREMENT=1 ;

--
-- 导出表中的数据 `user`
--

