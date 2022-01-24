-- Up
CREATE TABLE posts_m (
  id int unsigned NOT NULL AUTO_INCREMENT,
  title varchar(50) DEFAULT NULL,
  body text,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE articles_m (
  id int unsigned NOT NULL AUTO_INCREMENT,
  title varchar(50) DEFAULT NULL,
  body text,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- Down
DROP TABLE IF EXISTS posts_m;
DROP TABLE IF EXISTS articles_m;