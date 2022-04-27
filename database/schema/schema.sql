CREATE TABLE `posts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `body` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `articles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `body` text,
  `author_id` integer NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `authors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `identities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(72) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `profiles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `user_id` integer NOT NULL,   
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
   `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `posts_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY  (`post_id`,`tag_id`)
);

CREATE TABLE migrations (
  id MEDIUMINT NOT NULL AUTO_INCREMENT,
  version BIGINT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE queue (
  id MEDIUMINT NOT NULL AUTO_INCREMENT,
  body TEXT,
  queue VARCHAR(100) NOT NULL,
  scheduled DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

/* Then insert some posts for testing: */
INSERT INTO posts (title, body, created_at, updated_at)
    VALUES ('Post #1', 'This is post #1.',NOW(), NOW());
INSERT INTO posts (title, body, created_at, updated_at)
    VALUES ('Post #2', 'This is post #2.',NOW(), NOW());
INSERT INTO posts (title, body, created_at, updated_at)
    VALUES ('Post #3', 'This is post #3.',NOW(), NOW());

/* Then insert some posts for testing: */
INSERT INTO articles (title, body, author_id, created_at, updated_at)
    VALUES ('Article #1', 'This is article #1',1000,NOW(), NOW());
INSERT INTO articles (title, body, author_id, created_at, updated_at)
    VALUES ('Article #2', 'This is article #2.',1000, NOW(), NOW());
INSERT INTO articles (title, body, author_id, created_at, updated_at)
    VALUES ('Article #3', 'This is article #3.',1001, NOW(), NOW());

INSERT INTO authors (name, created_at, updated_at)
    VALUES ('claire',NOW(), NOW());
INSERT INTO authors (name, created_at, updated_at)
    VALUES ('tony',NOW(), NOW());
INSERT INTO authors (name, created_at, updated_at)
    VALUES ('jim',NOW(), NOW());

INSERT INTO tags (name, created_at, updated_at) VALUES ('new',NOW(), NOW());
INSERT INTO tags (name, created_at, updated_at) VALUES ('interesting',NOW(), NOW());
INSERT INTO tags (name, created_at, updated_at) VALUES ('favourite',NOW(), NOW());

INSERT INTO posts_tags (post_id,tag_id) VALUES (1000,1001);
INSERT INTO posts_tags (post_id,tag_id) VALUES (1001,1001);
INSERT INTO posts_tags (post_id,tag_id) VALUES (1001,1000);

INSERT INTO profiles (name, user_id,created_at, updated_at) VALUES ('admin',1000,NOW(), NOW());
INSERT INTO profiles (name,  user_id,created_at, updated_at) VALUES ('standard-user',1001,NOW(), NOW());
INSERT INTO profiles (name, user_id, created_at, updated_at) VALUES ('guest',1001,NOW(), NOW());

INSERT INTO users (name, created_at, updated_at) VALUES ('jon', NOW(), NOW());
INSERT INTO users (name, created_at, updated_at) VALUES ('fred', NOW(), NOW());
INSERT INTO users (name, created_at, updated_at) VALUES ('tom', NOW(), NOW());
INSERT INTO users (name, created_at, updated_at) VALUES ('tim', NOW(), NOW());