-- Opcional, pero recomendado si usas utf8mb4
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) NOT NULL DEFAULT 'default.png',
  banner VARCHAR(255),
  country VARCHAR(50),
  about TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE channels (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  banner_url VARCHAR(255),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  subscribers_count INT UNSIGNED NOT NULL DEFAULT 0,
  total_views INT UNSIGNED NOT NULL DEFAULT 0,
  total_videos INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_channels_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE videos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  channel_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  youtube_id VARCHAR(20) NOT NULL,
  thumbnail_url VARCHAR(255),
  duration INT UNSIGNED NOT NULL,
  resolution VARCHAR(10) NOT NULL DEFAULT '1080p',
  category_id INT UNSIGNED DEFAULT NULL,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  is_private TINYINT(1) NOT NULL DEFAULT 0,
  scheduled_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_videos_channel (channel_id),
  KEY idx_videos_category (category_id),
  KEY idx_videos_created_at (created_at),
  KEY idx_videos_channel_created (channel_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tags (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE video_tags (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  video_id INT UNSIGNED NOT NULL,
  tag_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_video_tags_video_tag (video_id, tag_id),
  KEY idx_video_tags_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE shorts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  channel_id INT UNSIGNED NOT NULL,
  youtube_id VARCHAR(20) NOT NULL,
  title VARCHAR(120),
  duration INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_shorts_channel (channel_id),
  KEY idx_shorts_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  channel_id INT UNSIGNED NOT NULL,
  media_url VARCHAR(255) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_stories_channel (channel_id),
  KEY idx_stories_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE subscriptions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  channel_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_subscriptions_user_channel (user_id, channel_id),
  KEY idx_subscriptions_channel (channel_id),
  KEY idx_subscriptions_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE likes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  video_id INT UNSIGNED DEFAULT NULL,
  short_id INT UNSIGNED DEFAULT NULL,
  type VARCHAR(10) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_likes_user (user_id),
  KEY idx_likes_video (video_id),
  KEY idx_likes_short (short_id),
  KEY idx_likes_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE comments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  video_id INT UNSIGNED DEFAULT NULL,
  short_id INT UNSIGNED DEFAULT NULL,
  parent_id INT UNSIGNED DEFAULT NULL,
  content TEXT NOT NULL,
  likes INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comments_user (user_id),
  KEY idx_comments_video (video_id),
  KEY idx_comments_short (short_id),
  KEY idx_comments_parent (parent_id),
  KEY idx_comments_video_created (video_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE history (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  video_id INT UNSIGNED DEFAULT NULL,
  short_id INT UNSIGNED DEFAULT NULL,
  last_watched_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_history_user (user_id),
  KEY idx_history_video (video_id),
  KEY idx_history_short (short_id),
  KEY idx_history_user_lastwatched (user_id, last_watched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE playlists (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  is_private TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_playlists_user (user_id),
  KEY idx_playlists_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE playlist_videos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  playlist_id INT UNSIGNED NOT NULL,
  video_id INT UNSIGNED NOT NULL,
  position INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_playlist_videos_playlist_video (playlist_id, video_id),
  KEY idx_playlist_videos_video (video_id),
  KEY idx_playlist_videos_playlist_position (playlist_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reports (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reporter_id INT UNSIGNED NOT NULL,
  target_user_id INT UNSIGNED DEFAULT NULL,
  video_id INT UNSIGNED DEFAULT NULL,
  comment_id INT UNSIGNED DEFAULT NULL,
  reason TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reports_reporter (reporter_id),
  KEY idx_reports_target_user (target_user_id),
  KEY idx_reports_video (video_id),
  KEY idx_reports_comment (comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  type VARCHAR(50) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notifications_user (user_id),
  KEY idx_notifications_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE analytics (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  video_id INT UNSIGNED NOT NULL,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  likes INT UNSIGNED NOT NULL DEFAULT 0,
  dislikes INT UNSIGNED NOT NULL DEFAULT 0,
  comments INT UNSIGNED NOT NULL DEFAULT 0,
  date DATE NOT NULL,
  PRIMARY KEY (id),
  KEY idx_analytics_video (video_id),
  KEY idx_analytics_video_date (video_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ads (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  advertiser VARCHAR(100) NOT NULL,
  media_url VARCHAR(255) NOT NULL,
  target_category_id INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ads_target_category (target_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ad_impressions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ad_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED DEFAULT NULL,
  video_id INT UNSIGNED DEFAULT NULL,
  watched_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ad_impressions_ad (ad_id),
  KEY idx_ad_impressions_user (user_id),
  KEY idx_ad_impressions_video (video_id),
  KEY idx_ad_impressions_ad_watched (ad_id, watched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
