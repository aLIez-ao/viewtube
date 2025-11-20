INSERT INTO users (username, email, password, avatar) 
VALUES ('Admin', 'admin@viewtube.com', '123456', 'default.png');

INSERT INTO videos (user_id, title, description, youtube_id, views) VALUES 
(1, 'Paisajes relajantes 4K', 'Video de prueba de paisajes.', 'ysz5S6PUM-U', 1500),
(1, 'Curso de PHP desde Cero', 'Aprende a programar.', 'n3tWv1f4hPc', 5300),
(1, 'Música Lo-Fi para estudiar', 'Beats relajantes.', 'jfKfPfyJRdk', 8900),
(1, 'Trailer de Película Épica', 'Acción y aventura.', 'KvkTP734H6w', 120);