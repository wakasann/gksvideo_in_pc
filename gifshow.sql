-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2020-02-13 22:48:09
-- 服务器版本： 5.7.26
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `gifshow`
--

-- --------------------------------------------------------

--
-- 表的结构 `download_video`
--

CREATE TABLE `download_video` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `kwaiId` varchar(50) DEFAULT '0' COMMENT '快手id 或者用户id 可通过此id找到用户',
  `user_name` varchar(50) DEFAULT NULL COMMENT '用户姓名',
  `sex` varchar(2) DEFAULT NULL COMMENT '性别',
  `desc` text COMMENT '描述',
  `cover_thumbnail_urls` text COMMENT '封面图片cdn json',
  `mv_photo_cdns` text COMMENT '视频或图片地址',
  `view_count` int(11) DEFAULT '0' COMMENT '观看数量',
  `like_count` int(11) DEFAULT '0' COMMENT '点心数量',
  `comment_count` int(11) DEFAULT '0' COMMENT '回复数量',
  `time` varchar(255) DEFAULT NULL COMMENT '创建时间',
  `photo_id` varchar(255) NOT NULL,
  `mtype` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型3:短视频，6 图片',
  `is_download` tinyint(4) DEFAULT '0',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转储表的索引
--

--
-- 表的索引 `download_video`
--
ALTER TABLE `download_video`
  ADD PRIMARY KEY (`id`),
  ADD KEY `photo_id` (`photo_id`),
  ADD KEY `is_download` (`is_download`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `download_video`
--
ALTER TABLE `download_video`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
