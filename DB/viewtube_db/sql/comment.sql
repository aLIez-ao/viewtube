ALTER TABLE comments
ADD INDEX (parent_id),
ADD FOREIGN KEY (parent_id) REFERENCES comments (id);
ALTER TABLE users ADD COLUMN history_paused BOOLEAN DEFAULT FALSE;