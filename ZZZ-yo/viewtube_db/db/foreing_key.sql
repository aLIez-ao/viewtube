ALTER TABLE channels
  ADD CONSTRAINT fk_channels_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE videos
  ADD CONSTRAINT fk_videos_channel
    FOREIGN KEY (channel_id) REFERENCES channels(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_videos_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE video_tags
  ADD CONSTRAINT fk_video_tags_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_video_tags_tag
    FOREIGN KEY (tag_id) REFERENCES tags(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE shorts
  ADD CONSTRAINT fk_shorts_channel
    FOREIGN KEY (channel_id) REFERENCES channels(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE stories
  ADD CONSTRAINT fk_stories_channel
    FOREIGN KEY (channel_id) REFERENCES channels(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE subscriptions
  ADD CONSTRAINT fk_subscriptions_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_subscriptions_channel
    FOREIGN KEY (channel_id) REFERENCES channels(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE likes
  ADD CONSTRAINT fk_likes_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_likes_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_likes_short
    FOREIGN KEY (short_id) REFERENCES shorts(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE comments
  ADD CONSTRAINT fk_comments_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_comments_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_comments_short
    FOREIGN KEY (short_id) REFERENCES shorts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_comments_parent
    FOREIGN KEY (parent_id) REFERENCES comments(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE history
  ADD CONSTRAINT fk_history_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_history_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_history_short
    FOREIGN KEY (short_id) REFERENCES shorts(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE playlists
  ADD CONSTRAINT fk_playlists_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE playlist_videos
  ADD CONSTRAINT fk_playlist_videos_playlist
    FOREIGN KEY (playlist_id) REFERENCES playlists(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_playlist_videos_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE reports
  ADD CONSTRAINT fk_reports_reporter
    FOREIGN KEY (reporter_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_reports_target_user
    FOREIGN KEY (target_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_reports_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_reports_comment
    FOREIGN KEY (comment_id) REFERENCES comments(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE notifications
  ADD CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE analytics
  ADD CONSTRAINT fk_analytics_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ads
  ADD CONSTRAINT fk_ads_target_category
    FOREIGN KEY (target_category_id) REFERENCES categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE ad_impressions
  ADD CONSTRAINT fk_ad_impressions_ad
    FOREIGN KEY (ad_id) REFERENCES ads(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_ad_impressions_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_ad_impressions_video
    FOREIGN KEY (video_id) REFERENCES videos(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
