CREATE INDEX idx_channels_user ON channels(user_id);
CREATE INDEX idx_videos_channel ON videos(channel_id);
CREATE INDEX idx_videos_category ON videos(category_id);
CREATE INDEX idx_likes_user ON likes(user_id);
CREATE INDEX idx_likes_video ON likes(video_id);
CREATE INDEX idx_likes_short ON likes(short_id);
CREATE INDEX idx_comments_parent ON comments(parent_id);
