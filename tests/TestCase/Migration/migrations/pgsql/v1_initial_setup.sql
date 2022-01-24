-- Up
CREATE TABLE posts_m (
  id SERIAL PRIMARY KEY,
  title varchar(50) DEFAULT NULL,
  body text,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

CREATE TABLE articles_m (
  id SERIAL PRIMARY KEY,
  title varchar(50) DEFAULT NULL,
  body text,
  created_at timestamp(0) NOT NULL,
  updated_at timestamp(0) NOT NULL
);

ALTER SEQUENCE posts_id_seq RESTART WITH 1000;
ALTER SEQUENCE articles_id_seq RESTART WITH 1000;
-- Down
DROP TABLE IF EXISTS posts_m;
DROP TABLE IF EXISTS articles_m;