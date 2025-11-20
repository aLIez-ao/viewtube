SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM ad_impressions;
DELETE FROM videos;
DELETE FROM channels;
DELETE FROM users;

ALTER TABLE ad_impressions AUTO_INCREMENT = 1;
ALTER TABLE videos AUTO_INCREMENT = 1;
ALTER TABLE channels AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO users (id, username, email, password, avatar) VALUES 
(1, 'Admin', 'admin@viewtube.com', '123456', 'default.png');

INSERT INTO channels (id, user_id, name, description) VALUES 
(1, 1, 'ViewTube Oficial', 'El canal oficial del administrador.');

INSERT INTO categories (id, name) VALUES (1, 'Tecnología');

INSERT INTO videos (channel_id, title, description, youtube_id, thumbnail_url, duration, category_id, views) VALUES 
(1, 'Paisajes relajantes 4K', 'Video de prueba.', 'ysz5S6PUM-U', 'https://img.youtube.com/vi/ysz5S6PUM-U/mqdefault.jpg', 600, 1, 1500),
(1, 'Curso de PHP Moderno', 'Aprende backend.', 'n3tWv1f4hPc', 'https://img.youtube.com/vi/n3tWv1f4hPc/mqdefault.jpg', 1250, 1, 5300),
(1, 'Música Lo-Fi', 'Para estudiar.', 'jfKfPfyJRdk', 'https://img.youtube.com/vi/jfKfPfyJRdk/mqdefault.jpg', 3400, 1, 8900);