CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(50) UNIQUE NOT NULL,
  `email` varchar(100) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `banner` varchar(255),
  `country` varchar(50),
  `about` text,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `channels` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `banner_url` varchar(255),
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP),
  `subscribers_count` int DEFAULT 0,
  `total_views` int DEFAULT 0,
  `total_videos` int DEFAULT 0
);

CREATE TABLE `videos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `channel_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text,
  `youtube_id` varchar(20) NOT NULL,
  `thumbnail_url` varchar(255),
  `duration` int NOT NULL,
  `resolution` varchar(10) DEFAULT '1080p',
  `category_id` int,
  `views` int DEFAULT 0,
  `is_private` boolean DEFAULT false,
  `scheduled_at` timestamp,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `categories` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(50) UNIQUE NOT NULL
);

CREATE TABLE `tags` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(50) UNIQUE NOT NULL
);

CREATE TABLE `video_tags` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `video_id` int NOT NULL,
  `tag_id` int NOT NULL
);

CREATE TABLE `shorts` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `channel_id` int NOT NULL,
  `youtube_id` varchar(20) NOT NULL,
  `title` varchar(120),
  `duration` int NOT NULL,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `stories` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `channel_id` int NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `subscriptions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `channel_id` int NOT NULL,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `likes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `video_id` int,
  `short_id` int,
  `type` varchar(10) NOT NULL,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `comments` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `video_id` int,
  `short_id` int,
  `parent_id` int,
  `content` text NOT NULL,
  `likes` int DEFAULT 0,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `history` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `video_id` int,
  `short_id` int,
  `last_watched_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `playlists` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `is_private` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `playlist_videos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `playlist_id` int NOT NULL,
  `video_id` int NOT NULL,
  `position` int NOT NULL DEFAULT 0
);

CREATE TABLE `reports` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `reporter_id` int NOT NULL,
  `target_user_id` int,
  `video_id` int,
  `comment_id` int,
  `reason` text NOT NULL,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `notifications` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `analytics` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `video_id` int NOT NULL,
  `views` int DEFAULT 0,
  `likes` int DEFAULT 0,
  `dislikes` int DEFAULT 0,
  `comments` int DEFAULT 0,
  `date` date NOT NULL
);

CREATE TABLE `ads` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `advertiser` varchar(100) NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `target_category_id` int,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `ad_impressions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `ad_id` int NOT NULL,
  `user_id` int,
  `video_id` int,
  `watched_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE INDEX `channels_index_0` ON `channels` (`user_id`);
CREATE INDEX `videos_index_1` ON `videos` (`channel_id`);
CREATE INDEX `videos_index_2` ON `videos` (`category_id`);
CREATE UNIQUE INDEX `video_tags_index_3` ON `video_tags` (`video_id`, `tag_id`);
CREATE UNIQUE INDEX `subscriptions_index_4` ON `subscriptions` (`user_id`, `channel_id`);
CREATE INDEX `likes_index_5` ON `likes` (`user_id`);
CREATE INDEX `likes_index_6` ON `likes` (`video_id`);
CREATE INDEX `likes_index_7` ON `likes` (`short_id`);
CREATE UNIQUE INDEX `playlist_videos_index_8` ON `playlist_videos` (`playlist_id`, `video_id`);
ALTER TABLE `channels` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `videos` ADD FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`);
ALTER TABLE `video_tags` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
ALTER TABLE `video_tags` ADD FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`);
ALTER TABLE `shorts` ADD FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`);
ALTER TABLE `stories` ADD FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`);
ALTER TABLE `subscriptions` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `subscriptions` ADD FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`);
ALTER TABLE `likes` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `likes` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
ALTER TABLE `likes` ADD FOREIGN KEY (`short_id`) REFERENCES `shorts` (`id`);
ALTER TABLE `comments` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `comments` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
ALTER TABLE `comments` ADD FOREIGN KEY (`short_id`) REFERENCES `shorts` (`id`);
ALTER TABLE `comments` ADD FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`);
ALTER TABLE `history` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `history` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
ALTER TABLE `history` ADD FOREIGN KEY (`short_id`) REFERENCES `shorts` (`id`);
ALTER TABLE `playlists` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `playlist_videos` ADD FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`);
ALTER TABLE `playlist_videos` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
ALTER TABLE `reports` ADD FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`);
ALTER TABLE `reports` ADD FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`);
ALTER TABLE `reports` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
ALTER TABLE `reports` ADD FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`);
ALTER TABLE `notifications` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `analytics` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
ALTER TABLE `ads` ADD FOREIGN KEY (`target_category_id`) REFERENCES `categories` (`id`);
ALTER TABLE `ad_impressions` ADD FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`);
ALTER TABLE `ad_impressions` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `ad_impressions` ADD FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
