-- Up
CREATE INDEX idx_title ON posts_m (title);
-- Down
ALTER TABLE posts_m DROP INDEX idx_title;