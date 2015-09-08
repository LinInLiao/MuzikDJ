SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `muzikdj` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `muzikdj`;


DROP TABLE IF EXISTS `playlist`;
CREATE TABLE IF NOT EXISTS `playlist` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','deleted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Playlist';

INSERT INTO `playlist` (`id`, `name`, `created`, `status`) VALUES
('2bb38f80-270d-11e5-b995-5cab4453d42f', 'anonymous', '2015-07-10 22:08:42', 'active');


DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('public','private','deleted') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rooms';


DROP TABLE IF EXISTS `room_playlist`;
CREATE TABLE IF NOT EXISTS `room_playlist` (
  `room_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `playlist_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `sort` mediumint(8) unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Room playlist';


DROP TABLE IF EXISTS `songs`;
CREATE TABLE IF NOT EXISTS `songs` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('youtube','vimeo','soundcloud') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'youtube',
  `url` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` mediumint(8) unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','deleted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Songs';


DROP TABLE IF EXISTS `song_playlist`;
CREATE TABLE IF NOT EXISTS `song_playlist` (
  `song_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `playlist_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `sort` mediumint(9) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Song playlist';


DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','deleted','pending') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `token` varchar(48) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users';


DROP TABLE IF EXISTS `user_playlist`;
CREATE TABLE IF NOT EXISTS `user_playlist` (
  `user_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `playlist_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `sort` mediumint(8) unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User playlist mapping';


DROP TABLE IF EXISTS `user_room`;
CREATE TABLE IF NOT EXISTS `user_room` (
  `user_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `room_id` char(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `sort` mediumint(8) unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User room';

ALTER TABLE `playlist`
 ADD PRIMARY KEY (`id`), ADD KEY `created` (`created`), ADD KEY `status` (`status`);

ALTER TABLE `rooms`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `alias` (`alias`), ADD KEY `room` (`name`,`password`), ADD KEY `name` (`name`), ADD KEY `status` (`status`);

ALTER TABLE `room_playlist`
 ADD PRIMARY KEY (`room_id`,`playlist_id`), ADD KEY `sort` (`sort`), ADD KEY `room_id` (`room_id`), ADD KEY `playlist_id` (`playlist_id`);

ALTER TABLE `songs`
 ADD PRIMARY KEY (`id`), ADD KEY `sort` (`sort`), ADD KEY `type` (`type`), ADD KEY `user_id` (`user_id`), ADD KEY `status` (`status`);

ALTER TABLE `song_playlist`
 ADD PRIMARY KEY (`song_id`,`playlist_id`), ADD KEY `sort` (`sort`), ADD KEY `song_playlist_ibfk_2` (`playlist_id`);

ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`), ADD KEY `status` (`status`), ADD KEY `token` (`token`), ADD KEY `account` (`email`,`password`);

ALTER TABLE `user_playlist`
 ADD PRIMARY KEY (`user_id`,`playlist_id`), ADD KEY `sort` (`sort`), ADD KEY `created` (`created`), ADD KEY `user_playlist_ibfk_2` (`playlist_id`);

ALTER TABLE `user_room`
 ADD PRIMARY KEY (`user_id`,`room_id`), ADD KEY `sort` (`sort`), ADD KEY `created` (`created`), ADD KEY `user_room_ibfk_2` (`room_id`);

 ALTER TABLE `room_playlist`
ADD CONSTRAINT `room_playlist_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
ADD CONSTRAINT `room_playlist_ibfk_2` FOREIGN KEY (`playlist_id`) REFERENCES `playlist` (`id`);

ALTER TABLE `songs`
ADD CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION;

ALTER TABLE `song_playlist`
ADD CONSTRAINT `song_playlist_ibfk_1` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`),
ADD CONSTRAINT `song_playlist_ibfk_2` FOREIGN KEY (`playlist_id`) REFERENCES `playlist` (`id`);

ALTER TABLE `user_playlist`
ADD CONSTRAINT `user_playlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
ADD CONSTRAINT `user_playlist_ibfk_2` FOREIGN KEY (`playlist_id`) REFERENCES `playlist` (`id`);

ALTER TABLE `user_room`
ADD CONSTRAINT `user_room_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
ADD CONSTRAINT `user_room_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);
