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


-- Asegúrate de que el ID de TU usuario actual sea 1. Si es otro, cambia el '1' en la sección de historial.

-- 1. CREAR USUARIOS (CREADORES)
INSERT INTO users (username, email, password, avatar) VALUES 
('TechMaster', 'tech@demo.com', '$2y$10$eImiTXuWVxfM37uY4JANjQ==', 'default.png'), -- Pass: 123456 (hash genérico)
('CocinaFacil', 'cocina@demo.com', '$2y$10$eImiTXuWVxfM37uY4JANjQ==', 'default.png'),
('TravelVlog', 'travel@demo.com', '$2y$10$eImiTXuWVxfM37uY4JANjQ==', 'default.png'),
('MusicMix', 'music@demo.com', '$2y$10$eImiTXuWVxfM37uY4JANjQ==', 'default.png'),
('GamingPro', 'gaming@demo.com', '$2y$10$eImiTXuWVxfM37uY4JANjQ==', 'default.png');

-- Obtener los IDs generados (Asumiendo que son 2, 3, 4, 5, 6 si ya tenías el 1)
-- Ajusta estos IDs si tu BD ya tenía más usuarios.

-- 2. CREAR CANALES
INSERT INTO channels (user_id, name, description, subscribers_count) VALUES 
(2, 'Tech Master Reviews', 'El mejor canal de tecnología y gadgets.', 15400),
(3, 'Cocina con Sabor', 'Recetas fáciles y rápidas para todos.', 8200),
(4, 'Mundo Viajero', 'Recorriendo el mundo mochila al hombro.', 45000),
(5, 'Lofi & Chill', 'Música para estudiar y relajarse.', 120000),
(6, 'Pro Gamer TV', 'Gameplays, trucos y guías.', 300);

-- 3. CREAR VIDEOS (Usando IDs de Youtube reales para las miniaturas)
INSERT INTO videos (channel_id, title, description, youtube_id, thumbnail_url, duration, views, created_at) VALUES 
-- Tech Master (ID Canal: 2)
(2, 'iPhone 15 Pro Max - Reseña Honesta', 'Vale la pena el nuevo iPhone? Lo analizamos a fondo.', 'xXT8r8s6i3A', 'https://img.youtube.com/vi/xXT8r8s6i3A/mqdefault.jpg', 945, 54000, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 'La MEJOR Laptop para Programar en 2024', 'Comparativa de Macbook vs Dell vs Thinkpad.', 'bJzb-RuDcMU', 'https://img.youtube.com/vi/bJzb-RuDcMU/mqdefault.jpg', 1230, 12000, DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Cocina con Sabor (ID Canal: 3)
(3, 'Cómo hacer PIZZA CASERA perfecta', 'Masa fácil y crujiente.', 'sv3TXMSv6Lw', 'https://img.youtube.com/vi/sv3TXMSv6Lw/mqdefault.jpg', 600, 8900, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(3, 'Tacos al Pastor en casa', 'La receta secreta de la abuela.', 'C2fLg4t-eow', 'https://img.youtube.com/vi/C2fLg4t-eow/mqdefault.jpg', 480, 1500, DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- Mundo Viajero (ID Canal: 4)
(4, '10 Cosas que hacer en JAPÓN', 'Guía definitiva de Tokio y Kioto.', 'Npt1mqMAWWg', 'https://img.youtube.com/vi/Npt1mqMAWWg/mqdefault.jpg', 1800, 230000, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(4, 'Me perdí en la selva del Amazonas', 'Una experiencia aterradora pero increíble.', 'vPhgm3q-3sM', 'https://img.youtube.com/vi/vPhgm3q-3sM/mqdefault.jpg', 2400, 50000, DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- Lofi & Chill (ID Canal: 5)
(5, 'Música para Estudiar / Trabajar [Lo-Fi Hip Hop]', 'Concentración máxima.', 'jfKfPfyJRdk', 'https://img.youtube.com/vi/jfKfPfyJRdk/mqdefault.jpg', 3600, 1500000, DATE_SUB(NOW(), INTERVAL 1 MONTH)),
(5, 'Jazz Relajante para Dormir', 'Dulces sueños.', 'neV3EPgvZ3g', 'https://img.youtube.com/vi/neV3EPgvZ3g/mqdefault.jpg', 4000, 800000, DATE_SUB(NOW(), INTERVAL 15 DAY)),

-- Gaming Pro (ID Canal: 6)
(6, 'Elden Ring: Guía para principiantes', 'No mueras en el intento.', 'E3Huy2cdih0', 'https://img.youtube.com/vi/E3Huy2cdih0/mqdefault.jpg', 1500, 200, DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- 4. CREAR HISTORIAL PARA EL USUARIO ID 1 (TÚ)
-- Asumiendo que tu usuario tiene ID = 1. 
-- Si tu ID es otro, cambia el '1' por tu ID real.

-- Limpiar historial previo para evitar duplicados raros en la prueba
DELETE FROM history WHERE user_id = 1;

INSERT INTO history (user_id, video_id, last_watched_at) VALUES 
(1, 1, NOW()), -- Visto hace instantes
(1, 3, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 5, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(1, 7, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 2, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 4, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 6, DATE_SUB(NOW(), INTERVAL 1 WEEK));

-- 5. SUSCRIBIRTE A ALGUNOS CANALES (Para probar subscriptions.php)
DELETE FROM subscriptions WHERE user_id = 1;

INSERT INTO subscriptions (user_id, channel_id) VALUES 
(1, 2), -- Tech Master
(1, 4), -- Mundo Viajero
(1, 5); -- Lofi