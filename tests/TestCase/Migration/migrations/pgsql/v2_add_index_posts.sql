-- Up
CREATE INDEX idx_title ON posts_m (title);
-- Down
DROP INDEX idx_title;